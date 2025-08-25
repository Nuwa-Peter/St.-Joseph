<?php
// Increase execution time and memory limit for this long-running script
set_time_limit(3600); // 60 minutes
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
    $cleaned_name = trim($name);
    if (empty($cleaned_name)) return null;

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

function get_class_level_ids($name, $class_level_map) {
    $name = trim($name);
    $ids = [];

    if (strtoupper($name) === 'A LEVEL') {
        if (isset($class_level_map['S5'])) $ids[] = $class_level_map['S5'];
        if (isset($class_level_map['S6'])) $ids[] = $class_level_map['S6'];
    } elseif (strtoupper($name) === 'S1-S4') {
        if (isset($class_level_map['S1'])) $ids[] = $class_level_map['S1'];
        if (isset($class_level_map['S2'])) $ids[] = $class_level_map['S2'];
        if (isset($class_level_map['S3'])) $ids[] = $class_level_map['S3'];
        if (isset($class_level_map['S4'])) $ids[] = $class_level_map['S4'];
    } else {
        if (isset($class_level_map[$name])) $ids[] = $class_level_map[$name];
    }
    return $ids;
}

// --- Main Processing Logic ---

echo "Starting FINAL PDF processing using human-verified map...\n\n";

// 1. Fetch existing class levels to use for mapping
$existing_class_levels = $conn->query("SELECT id, name FROM class_levels")->fetch_all(MYSQLI_ASSOC);
$class_level_map = array_column($existing_class_levels, 'id', 'name');

$map_file = 'curriculum_map.csv';
if (!file_exists($map_file)) die("ERROR: curriculum_map.csv not found.");

$parser = new \Smalot\PdfParser\Parser();
$file_handle = fopen($map_file, "r");

// Read header row
fgetcsv($file_handle);

while (($data = fgetcsv($file_handle, 1000, ",")) !== FALSE) {
    if (count($data) < 3) continue;

    $url = trim($data[0]);
    $subject_name = trim($data[1]);
    $class_level_str = trim($data[2]);

    if (empty($subject_name) || empty($class_level_str)) {
        continue; // Skip rows that were not mapped by the user
    }

    echo "--------------------------------------------------\n";
    echo "Processing: " . htmlspecialchars($url) . "\n";
    echo "Mapping to Subject: '" . htmlspecialchars($subject_name) . "', Class(es): '" . htmlspecialchars($class_level_str) . "'\n";

    $subject_id = get_or_create_subject_id($subject_name, $conn);
    $class_level_ids = get_class_level_ids($class_level_str, $class_level_map);

    if (empty($subject_id) || empty($class_level_ids)) {
        echo "Could not map Subject or Class Level from map file. Skipping.\n";
        continue;
    }

    $temp_pdf_file = 'temp_download.pdf';

    echo "Downloading...";
    if (!curl_download($url, $temp_pdf_file)) {
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

    echo "  - Found " . count($topics) . " topics. Inserting for " . count($class_level_ids) . " class level(s)...\n";

    $inserted_count = 0;
    foreach ($class_level_ids as $class_level_id) {
        foreach($topics as $topic_title) {
            try {
                $stmt = $conn->prepare("INSERT INTO curriculum_topics (subject_id, class_level_id, title) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE title=title;");
                $stmt->bind_param("iis", $subject_id, $class_level_id, $topic_title);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $inserted_count++;
                }
                $stmt->close();
            } catch (Exception $e) {
                // Error inserting, maybe log it but continue
            }
        }
    }
    echo "    -> Successfully inserted $inserted_count new topics.\n";

    unlink($temp_pdf_file);
    sleep(1);
}

fclose($file_handle);

echo "\n\nProcessing finished.\n";
echo "</pre>";

$conn->close();
?>
