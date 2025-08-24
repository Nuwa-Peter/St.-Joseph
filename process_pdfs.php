<?php
// Increase execution time and memory limit for this long-running script
set_time_limit(1800); // 30 minutes
ini_set('memory_limit', '512M');

// Include Composer's autoloader
require_once 'vendor/autoload.php';
require_once 'config.php';

echo "<pre>"; // Use preformatted text for better log readability in the browser

// --- Helper Functions ---

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

function find_subject_id($inferred_name, $existing_subjects) {
    $cleaned_name = strtoupper(trim(preg_replace('/(&#8217;)|(&#038;)/', "'", $inferred_name)));

    // Direct match first
    foreach ($existing_subjects as $subject) {
        if (strtoupper($subject['name']) === $cleaned_name) {
            return $subject['id'];
        }
    }

    // Keyword matching
    $keywords = [
        'HISTORY' => 'HISTORY',
        'POLITICAL EDUCATION' => 'HISTORY',
        'GEOGRAPHY' => 'GEOGRAPHY',
        'ECONOMICS' => 'ECONOMICS',
        'ENTREPRENEURSHIP' => 'ENTREPRENEURSHIP',
        'BIOLOGY' => 'BIOLOGY',
        'CHEMISTRY' => 'CHEMISTRY',
        'PHYSICS' => 'PHYSICS',
        'MATHEMATICS' => 'MATHEMATICS',
        'MTC' => 'MATHEMATICS',
        'ENGLISH' => 'ENGLISH',
        'LITERATURE' => 'LITERATURE',
        'KISWAHILI' => 'KISWAHILI',
        'AGRICULTURE' => 'AGRICULTURE',
        'ISLAMIC' => 'IRE',
        'IRE' => 'IRE',
        'CHRISTIAN' => 'CRE',
        'CRE' => 'CRE',
        'ART' => 'ART AND DESIGN',
        'ICT' => 'ICT',
        'PHYSICAL EDUCATION' => 'PHYSICAL EDUCATION',
        'GENERAL SCIENCE' => 'GENERAL SCIENCE'
    ];

    foreach ($keywords as $keyword => $subject_name) {
        if (strpos($cleaned_name, $keyword) !== false) {
            foreach ($existing_subjects as $subject) {
                if (strtoupper($subject['name']) === $subject_name) {
                    return $subject['id'];
                }
            }
        }
    }

    return null; // No match found
}


function get_class_level_id($name, $conn) {
    $name = trim($name);
    if (empty($name) || $name === 'Uncategorized') {
        return null;
    }
    $stmt = $conn->prepare("SELECT id FROM class_levels WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row['id'];
    }
    $stmt->close();
    return null;
}

// --- Main Processing Logic ---

echo "Starting PDF processing and database population...\n\n";

// 1. Fetch existing subjects and class levels to use for mapping
$existing_subjects = $conn->query("SELECT id, name FROM subjects")->fetch_all(MYSQLI_ASSOC);
$existing_class_levels = $conn->query("SELECT id, name FROM class_levels")->fetch_all(MYSQLI_ASSOC);
$class_level_map = array_column($existing_class_levels, 'id', 'name');


$json_file = 'ncdc_pdfs.json';
if (!file_exists($json_file)) die("ERROR: ncdc_pdfs.json not found.");
$pdf_list = json_decode(file_get_contents($json_file), true);
if (json_last_error() !== JSON_ERROR_NONE) die("ERROR: Invalid JSON. Error: " . json_last_error_msg());

echo "Found " . count($pdf_list) . " PDFs to process.\n\n";

$parser = new \Smalot\PdfParser\Parser();

foreach ($pdf_list as $index => $pdf_info) {
    if (str_starts_with($pdf_info['inferred_class_level'], 'P')) {
        continue;
    }

    $subject_id = find_subject_id($pdf_info['inferred_subject'], $existing_subjects);
    $class_level_id = $class_level_map[$pdf_info['inferred_class_level']] ?? null;

    if ($subject_id === null || $class_level_id === null) {
        echo "Skipping document: " . htmlspecialchars($pdf_info['original_text']) . " (Could not map Subject or Class Level)\n";
        continue;
    }

    echo "--------------------------------------------------\n";
    echo "Processing: " . htmlspecialchars($pdf_info['original_text']) . " [Subject ID: $subject_id, Class ID: $class_level_id]\n";

    $temp_pdf_file = 'temp_download.pdf';

    echo "Downloading...";
    if (!curl_download($pdf_info['url'], $temp_pdf_file)) {
        echo " FAILED. Skipping.\n";
        continue;
    }
    echo " OK\n";

    echo "Parsing PDF...";
    try {
        $pdf = $parser->parseFile($temp_pdf_file);
        $text_content = $pdf->getText();
        if (empty(trim($text_content))) throw new Exception("Extracted text is empty.");
        echo " OK\n";
    } catch (Exception $e) {
        echo " FAILED. Library could not parse PDF. Error: " . $e->getMessage() . ". Skipping.\n";
        @unlink($temp_pdf_file);
        continue;
    }

    // New parsing logic
    $lines = explode("\n", $text_content);
    $topics = [];
    $keywords_to_find = ['CHAPTER', 'THEME', 'SUB-CHAPTER', 'INTRODUCTION', 'PREFACE', 'ACKNOWLEDGEMENTS', 'GLOSSARY', 'REFERENCES'];

    foreach ($lines as $line) {
        $trimmed_line = trim($line);
        foreach ($keywords_to_find as $keyword) {
            if (str_starts_with(strtoupper($trimmed_line), $keyword)) {
                 $title = trim(preg_replace('/\s*\.{3,}\s*\d*$/', '', $trimmed_line));
                 if (strlen($title) > 4 && strlen($title) < 100) {
                     $topics[] = $title;
                 }
                 break;
            }
        }
    }
    $topics = array_unique($topics);

    if (empty($topics)) {
        echo "Could not parse any topics. Skipping document.\n";
        @unlink($temp_pdf_file);
        continue;
    }

    echo "  - Found " . count($topics) . " potential topics. Inserting into database...\n";

    $inserted_count = 0;
    foreach($topics as $topic_title) {
        try {
            $stmt = $conn->prepare("INSERT INTO curriculum_topics (subject_id, class_level_id, title) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $subject_id, $class_level_id, $topic_title);
            $stmt->execute();
            $stmt->close();
            $inserted_count++;
        } catch (Exception $e) {
            // Ignore duplicate topic errors for now
        }
    }
    echo "    -> Successfully inserted $inserted_count new topics.\n";

    unlink($temp_pdf_file);
    sleep(1);
}

echo "\n\nProcessing finished.\n";
echo "</pre>";

$conn->close();
?>
