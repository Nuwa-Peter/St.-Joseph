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
    // Clean the name more aggressively
    $cleaned_name = trim(preg_replace('/(Teacher(s)?(\s*Guide)?|Learner(s)?(\s*Book)?|Syllabus|Prototype|Textbook)/i', '', $name));
    $cleaned_name = trim(preg_replace('/(&#8217;)|(&#038;)/', "'", $cleaned_name));
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

    // Handle potential duplicate codes
    $original_code = $code;
    $counter = 1;
    while (true) {
        $stmt = $conn->prepare("SELECT id FROM subjects WHERE code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $stmt->close();
            break;
        }
        $stmt->close();
        $counter++;
        $code = $original_code . $counter;
    }

    $stmt = $conn->prepare("INSERT INTO subjects (name, code, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
    $stmt->bind_param("ss", $cleaned_name, $code);
    $stmt->execute();
    $new_id = $stmt->insert_id;
    $stmt->close();

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

    echo "Warning: Class Level '" . htmlspecialchars($name) . "' not found in database. Setting to NULL.\n";
    return null;
}

// --- Main Processing Logic ---

echo "Starting PDF processing and database population...\n\n";

$json_file = 'ncdc_pdfs.json';
if (!file_exists($json_file)) die("ERROR: ncdc_pdfs.json not found.");
$pdf_list = json_decode(file_get_contents($json_file), true);
if (json_last_error() !== JSON_ERROR_NONE) die("ERROR: Invalid JSON in ncdc_pdfs.json. Error: " . json_last_error_msg());

echo "Found " . count($pdf_list) . " PDFs to process.\n\n";

// For demonstration, let's process only a small subset first.
$pdf_list = array_slice($pdf_list, 12, 5);
echo "NOTE: Processing a small subset of 5 PDFs for testing purposes.\n\n";

$parser = new \Smalot\PdfParser\Parser();

foreach ($pdf_list as $index => $pdf_info) {
    if (str_starts_with($pdf_info['inferred_class_level'], 'P')) {
        echo "Skipping Primary level document: " . htmlspecialchars($pdf_info['original_text']) . "\n";
        continue;
    }

    echo "--------------------------------------------------\n";
    echo "Processing PDF " . ($index + 1) . " of " . count($pdf_list) . ": " . htmlspecialchars($pdf_info['inferred_subject']) . "\n";
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

    // --- New, Smarter Parsing Logic ---
    $subject_id = get_or_create_subject_id($pdf_info['inferred_subject'], $conn);
    $class_level_id = get_class_level_id($pdf_info['inferred_class_level'], $conn);

    if ($class_level_id === null) {
        echo "Cannot process this document because its class level ('" . htmlspecialchars($pdf_info['inferred_class_level']) . "') is not in the database. Skipping.\n";
        @unlink($temp_pdf_file);
        continue;
    }

    // 1. Find the Table of Contents
    $toc = [];
    $lines = explode("\n", $text_content);
    $in_toc = false;
    foreach ($lines as $line) {
        if (strtoupper(trim($line)) === 'CONTENTS') {
            $in_toc = true;
            continue;
        }
        if ($in_toc && preg_match('/^(CHAPTER|SUB-CHAPTER)\s+\d/i', trim($line))) {
            // Stop when we hit the first chapter after the contents page
            break;
        }
        if ($in_toc) {
            // Match lines that look like "CHAPTER 1 ................... 1"
            if (preg_match('/^(CHAPTER\s+\d+(\.\d)?)\s*(.*?)\s*\.{3,}\s*(\d+)/i', $line, $matches)) {
                $title = trim($matches[3]);
                if (!empty($title)) $toc[] = $title;
            }
        }
    }

    if (empty($toc)) {
        echo "Could not parse a table of contents. Skipping document.\n";
        @unlink($temp_pdf_file);
        continue;
    }

    echo "  - Found " . count($toc) . " topics in Table of Contents.\n";

    // 2. Extract content for each topic found in the TOC
    for ($i = 0; $i < count($toc); $i++) {
        $current_title = $toc[$i];
        $next_title = isset($toc[$i + 1]) ? $toc[$i + 1] : null;

        // Find the start position of the current topic's content
        $start_pos = strpos($text_content, $current_title);
        if ($start_pos === false) continue;
        $start_pos += strlen($current_title);

        // Find the end position
        $end_pos = false;
        if ($next_title) {
            $end_pos = strpos($text_content, $next_title, $start_pos);
        }

        $topic_content = ($end_pos !== false)
            ? substr($text_content, $start_pos, $end_pos - $start_pos)
            : substr($text_content, $start_pos);

        echo "  - Parsing Topic: " . htmlspecialchars($current_title) . "\n";

        // Database Insertion
        $conn->begin_transaction();
        try {
            // For now, we are just inserting the topic title. The content extraction
            // for activities etc. would go here.
            $stmt = $conn->prepare("INSERT INTO curriculum_topics (subject_id, class_level_id, title) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $subject_id, $class_level_id, $current_title);
            $stmt->execute();
            $topic_id = $stmt->insert_id;
            $stmt->close();

            $conn->commit();
            echo "    -> Successfully inserted topic with ID: $topic_id\n";
        } catch (Exception $e) {
            $conn->rollback();
            echo "    -> FAILED to insert topic. Error: " . $e->getMessage() . "\n";
        }
    }

    // 4. Cleanup
    unlink($temp_pdf_file);

    sleep(1);
}

echo "\n\nProcessing finished.\n";
echo "</pre>";

$conn->close();
?>
