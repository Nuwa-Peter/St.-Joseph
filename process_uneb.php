<?php
// Increase execution time and memory limit for this long-running script
set_time_limit(3600); // 60 minutes
ini_set('memory_limit', '512M');

// Include Composer's autoloader
require_once 'vendor/autoload.php';
require_once 'config.php';

echo "<pre>";

// --- Helper Functions (copied from previous script) ---
function curl_download($url, $output_file) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_ENCODING, "");
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");
    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code == 200 && $data) {
        file_put_contents($output_file, $data);
        return true;
    }
    return false;
}

function find_subject_id_by_keyword($filename, $existing_subjects) {
    $filename_upper = strtoupper($filename);
    foreach ($existing_subjects as $subject) {
        $subject_name_upper = strtoupper($subject['name']);
        if (strpos($filename_upper, $subject_name_upper) !== false) {
            return $subject['id'];
        }
        // Also check for common abbreviations
        if (array_key_exists($subject_name_upper, ['MTC' => 'MATHEMATICS', 'PHY' => 'PHYSICS', 'CHEM' => 'CHEMISTRY', 'BIO' => 'BIOLOGY', 'AGR' => 'AGRICULTURE'])) {
             if (strpos($filename_upper, $keyword) !== false) {
                 return $subject['id'];
             }
        }
    }
    return null;
}


// --- Main Processing Logic ---

echo "Starting UNEB Assessment data processing...\n\n";

// Fetch existing subjects for mapping
$existing_subjects = $conn->query("SELECT id, name FROM subjects")->fetch_all(MYSQLI_ASSOC);

// Hardcoded list of RAR files from UNEB sample papers page
$rar_files = [
    'AGRICULTURE' => 'https://ereg.uneb.ac.ug/files/agric.rar',
    'ART AND DESIGN' => 'https://ereg.uneb.ac.ug/files/art_design.rar',
    'BIOLOGY' => 'https://ereg.uneb.ac.ug/files/bio.rar',
    'CHEMISTRY' => 'https://ereg.uneb.ac.ug/files/chem.rar',
    'GENERAL SCIENCE' => 'https://ereg.uneb.ac.ug/files/General_science.rar',
    'ICT' => 'https://ereg.uneb.ac.ug/files/ict.rar',
    'MATHEMATICS' => 'https://ereg.uneb.ac.ug/files/MTC.rar',
    'NUTRITION AND FOOD TECHNOLOGY' => 'https://ereg.uneb.ac.ug/files/nutrition.rar',
    'PERFORMING ARTS' => 'https://ereg.uneb.ac.ug/files/performing_arts.rar',
    'PHYSICAL EDUCATION' => 'https://ereg.uneb.ac.ug/files/physical_educ.rar',
    'PHYSICS' => 'https://ereg.uneb.ac.ug/files/Phy.rar',
    'TECHNOLOGY AND DESIGN' => 'https://ereg.uneb.ac.ug/files/techdesign.rar',
    'HUMANITIES' => 'https://ereg.uneb.ac.ug/files/Humanities.rar', // Contains Hist, CRE, IRE, Geo
];

$parser = new \Smalot\PdfParser\Parser();

foreach ($rar_files as $subject_keyword => $url) {
    echo "--------------------------------------------------\n";
    echo "Processing Archive for: " . htmlspecialchars($subject_keyword) . "\n";

    $temp_rar_file = 'temp_archive.rar';
    $extract_dir = 'extracted_files';

    if (!is_dir($extract_dir)) {
        mkdir($extract_dir, 0777, true);
    }

    echo "Downloading " . htmlspecialchars($url) . "...";
    if (!curl_download($url, $temp_rar_file)) {
        echo " FAILED. Skipping.\n";
        continue;
    }
    echo " OK\n";

    echo "Extracting archive...";
    // Use `unrar x` to extract with full paths if any, -o+ to overwrite all
    $command = "unrar x -o+ " . escapeshellarg($temp_rar_file) . " " . escapeshellarg($extract_dir . DIRECTORY_SEPARATOR);
    shell_exec($command . " 2>&1");
    echo " OK\n";

    $extracted_files = glob($extract_dir . '/*.pdf');
    echo "Found " . count($extracted_files) . " PDF(s) in archive.\n";

    foreach ($extracted_files as $pdf_file) {
        echo "  - Processing PDF: " . htmlspecialchars(basename($pdf_file)) . "\n";

        $subject_id = find_subject_id_by_keyword($subject_keyword, $existing_subjects);
        // For now, assume these are all for S1/S2 of the new curriculum
        $class_level_id = 7; // S1 as a default, this needs refinement

        if ($subject_id === null) {
            echo "    -> Could not map to a subject. Skipping.\n";
            continue;
        }

        try {
            $pdf = $parser->parseFile($pdf_file);
            $content = $pdf->getText();

            // Simplified parsing: treat the whole text as a "sample_question"
            $assessment_type = 'sample_question';

            $stmt = $conn->prepare("INSERT INTO uneb_assessments (subject_id, class_level_id, assessment_type, content, source_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $subject_id, $class_level_id, $assessment_type, $content, $url);
            $stmt->execute();
            $stmt->close();
            echo "    -> Successfully inserted content into database.\n";

        } catch (Exception $e) {
            echo "    -> FAILED to parse or insert. Error: " . $e->getMessage() . "\n";
        }
    }

    // Cleanup for this archive
    foreach ($extracted_files as $file) {
        unlink($file);
    }
    rmdir($extract_dir);
    unlink($temp_rar_file);

    sleep(1);
}

echo "\n\nUNEB Processing finished.\n";
echo "</pre>";

$conn->close();
?>
