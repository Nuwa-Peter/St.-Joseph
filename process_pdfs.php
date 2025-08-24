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

function get_or_create_subject_id($name, $conn) {
    $cleaned_name = trim(preg_replace('/(Teacher(s)?(\s*Guide)?|Learner(s)?(\s*Book)?|Syllabus|Prototype|Textbook|\d{4}(\.\d{1,2}\.\d{1,2})?)/i', '', $name));
    $cleaned_name = trim(preg_replace('/(&#8217;)|(&#038;)/', "'", $cleaned_name));
    $cleaned_name = trim(preg_replace('/[0-9-.]+$/', '', $cleaned_name));
    $cleaned_name = trim($cleaned_name);

    if (empty($cleaned_name)) {
        $cleaned_name = 'Uncategorized';
    }

    $stmt = $conn->prepare("SELECT id FROM subjects WHERE name = ?");
    $stmt->bind_param("s", $cleaned_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row['id'];
    }
    $stmt->close();

    echo "Creating new subject: " . htmlspecialchars($cleaned_name) . "\n";
    $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $cleaned_name), 0, 4));

    $original_code = $code;
    $counter = 1;
    while (true) {
        $stmt_check = $conn->prepare("SELECT id FROM subjects WHERE code = ?");
        $stmt_check->bind_param("s", $code);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows == 0) {
            $stmt_check->close();
            break;
        }
        $stmt_check->close();
        $counter++;
        $code = substr($original_code, 0, 3) . $counter;
    }

    $stmt_insert = $conn->prepare("INSERT INTO subjects (name, code, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
    $stmt_insert->bind_param("ss", $cleaned_name, $code);
    $stmt_insert->execute();
    $new_id = $stmt_insert->insert_id;
    $stmt_insert->close();

    return $new_id;
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
    echo "Warning: Class Level '" . htmlspecialchars($name) . "' not found. Setting to NULL.\n";
    return null;
}

// --- Main Processing Logic ---

echo "Starting PDF processing and database population...\n\n";

$json_file = 'ncdc_pdfs.json';
if (!file_exists($json_file)) die("ERROR: ncdc_pdfs.json not found.");
$pdf_list = json_decode(file_get_contents($json_file), true);
if (json_last_error() !== JSON_ERROR_NONE) die("ERROR: Invalid JSON. Error: " . json_last_error_msg());

echo "Found " . count($pdf_list) . " PDFs to process.\n\n";

// For demonstration, process a small subset.
// $pdf_list = array_slice($pdf_list, 12, 5);
// echo "NOTE: Processing a small subset of 5 PDFs for testing purposes.\n\n";

$parser = new \Smalot\PdfParser\Parser();

foreach ($pdf_list as $index => $pdf_info) {
    if (str_starts_with($pdf_info['inferred_class_level'], 'P')) {
        continue;
    }

    echo "--------------------------------------------------\n";
    echo "Processing PDF " . ($index + 1) . ": " . htmlspecialchars($pdf_info['original_text']) . "\n";
    echo "URL: " . htmlspecialchars($pdf_info['url']) . "\n";

    $temp_pdf_file = 'temp_download.pdf';

    echo "Downloading...";
    if (!curl_download($pdf_info['url'], $temp_pdf_file)) {
        echo " FAILED. Skipping.\n";
        continue;
    }
    echo " OK\n";

    echo "Parsing PDF with PHP library...";
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

    $subject_id = get_or_create_subject_id($pdf_info['inferred_subject'], $conn);
    $class_level_id = get_class_level_id($pdf_info['inferred_class_level'], $conn);

    if ($class_level_id === null) {
        echo "Cannot process this document because its class level ('" . htmlspecialchars($pdf_info['inferred_class_level']) . "') is not in the database. Skipping.\n";
        @unlink($temp_pdf_file);
        continue;
    }

    // New parsing logic
    $lines = explode("\n", $text_content);
    $topics = [];
    foreach ($lines as $line) {
        $trimmed_line = trim($line);
        if (preg_match('/^(CHAPTER\s+\d+|THEME:|SUB-CHAPTER\s+\d\.\d|INTRODUCTION|PREFACE|ACKNOWLEDGEMENTS|GLOSSARY|REFERENCES)/i', $trimmed_line)) {
            // Further clean the title
            $title = trim(preg_replace('/\s*\.{3,}\s*\d*$/', '', $trimmed_line));
            if (strlen($title) > 5 && strlen($title) < 100) { // Basic sanity check
                 $topics[] = $title;
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

    foreach($topics as $topic_title) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO curriculum_topics (subject_id, class_level_id, title) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $subject_id, $class_level_id, $topic_title);
            $stmt->execute();
            $topic_id = $stmt->insert_id;
            $stmt->close();
            $conn->commit();
            echo "    -> Inserted topic: " . htmlspecialchars($topic_title) . "\n";
        } catch (Exception $e) {
            $conn->rollback();
            echo "    -> FAILED to insert topic '" . htmlspecialchars($topic_title) . "'. Error: " . $e->getMessage() . "\n";
        }
    }

    unlink($temp_pdf_file);
    sleep(1);
}

echo "\n\nProcessing finished.\n";
echo "</pre>";

$conn->close();
?>
