<?php
// Increase execution time and memory limit for this long-running script
set_time_limit(1800); // 30 minutes
ini_set('memory_limit', '512M');

require_once 'config.php';

echo "<pre>"; // Use preformatted text for better log readability in the browser

// --- Helper Functions ---

function get_or_create_subject_id($name, $conn) {
    $name = trim($name);
    if (empty($name)) {
        // Fallback for empty subject names
        $name = 'Uncategorized';
    }

    // Check if subject exists
    $stmt = $conn->prepare("SELECT id FROM subjects WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row['id'];
    }
    $stmt->close();

    // If not, create it
    echo "Creating new subject: " . htmlspecialchars($name) . "\n";
    $stmt = $conn->prepare("INSERT INTO subjects (name, code, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
    $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 4)); // Generate a simple code
    $stmt->bind_param("ss", $name, $code);
    $stmt->execute();
    $new_id = $stmt->insert_id;
    $stmt->close();

    return $new_id;
}

function get_class_level_id($name, $conn) {
    $name = trim($name);
    if (empty($name) || $name === 'Uncategorized') {
        return null; // Return null if class level is not specified
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

    // Unlike subjects, we probably shouldn't create new class levels on the fly.
    // We'll return null and handle it in the main logic.
    echo "Warning: Class Level '" . htmlspecialchars($name) . "' not found in database. Setting to NULL.\n";
    return null;
}


// --- Main Processing Logic ---

echo "Starting PDF processing and database population...\n\n";

$json_file = 'ncdc_pdfs.json';
if (!file_exists($json_file)) {
    die("ERROR: ncdc_pdfs.json not found. Please run the scraper first.\n");
}

$pdf_list = json_decode(file_get_contents($json_file), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("ERROR: Invalid JSON in ncdc_pdfs.json.\n");
}

echo "Found " . count($pdf_list) . " PDFs to process.\n\n";

// For demonstration, let's process only a small subset first.
// Remove or comment out this line to process all files.
$pdf_list = array_slice($pdf_list, 12, 5);
echo "NOTE: Processing a small subset of 5 PDFs for testing purposes.\n\n";


foreach ($pdf_list as $index => $pdf_info) {
    // User requested to only process secondary school curriculum
    if (str_starts_with($pdf_info['inferred_class_level'], 'P')) {
        echo "Skipping Primary level document: " . htmlspecialchars($pdf_info['original_text']) . "\n";
        continue;
    }

    echo "--------------------------------------------------\n";
    echo "Processing PDF " . ($index + 1) . " of " . count($pdf_list) . ": " . htmlspecialchars($pdf_info['inferred_subject']) . "\n";
    echo "URL: " . htmlspecialchars($pdf_info['url']) . "\n";

    $temp_pdf_file = 'temp_download.pdf';
    $temp_txt_file = 'temp_download.txt';

    // 1. Download PDF
    echo "Downloading...";
    if (copy($pdf_info['url'], $temp_pdf_file)) {
        echo " OK\n";
    } else {
        echo " FAILED. Skipping.\n";
        continue;
    }

    // 2. Convert to Text
    echo "Converting to text...";
    // Use -layout to preserve some of the original document structure
    $command = "pdftotext -layout " . escapeshellarg($temp_pdf_file) . " " . escapeshellarg($temp_txt_file);
    shell_exec($command);

    if (!file_exists($temp_txt_file) || filesize($temp_txt_file) === 0) {
        echo " FAILED. PDF might be an image or unreadable. Skipping.\n";
        @unlink($temp_pdf_file);
        continue;
    }
    echo " OK\n";

    // 3. Parse Text and Insert into DB
    $text_content = file_get_contents($temp_txt_file);

    // This is a very simplified parser. A real-world one would be much more complex.
    // It looks for "CHAPTER X" as a topic separator.
    $chapters = preg_split('/(CHAPTER\s+\d+(\.\d)?)/', $text_content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

    if (count($chapters) <= 1) {
        echo "Could not find any chapters in the document. Skipping.\n";
        @unlink($temp_pdf_file);
        @unlink($temp_txt_file);
        continue;
    }

    $subject_id = get_or_create_subject_id($pdf_info['inferred_subject'], $conn);
    $class_level_id = get_class_level_id($pdf_info['inferred_class_level'], $conn);

    // If class_level_id is null, we can't proceed as it's a required field.
    if ($class_level_id === null) {
        echo "Cannot process this document because its class level ('" . htmlspecialchars($pdf_info['inferred_class_level']) . "') is not in the database. Skipping.\n";
        @unlink($temp_pdf_file);
        @unlink($temp_txt_file);
        continue;
    }

    // Loop through the captured chapters
    for ($c = 0; $c < count($chapters); $c += 2) {
        $chapter_title_marker = $chapters[$c];
        $chapter_content = $chapters[$c + 1] ?? '';

        // Extract the actual title from the content block
        $lines = explode("\n", $chapter_content);
        $topic_title = trim($lines[0]); // Assume the first line after "CHAPTER X" is the title
        $topic_theme = '';

        // Try to find a theme
        if (preg_match('/THEME:\s*(.*)/i', $chapter_content, $theme_match)) {
            $topic_theme = trim($theme_match[1]);
        }

        echo "  - Parsing Topic: " . htmlspecialchars($topic_title) . "\n";

        // Insert into database
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO curriculum_topics (subject_id, class_level_id, title, theme) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $subject_id, $class_level_id, $topic_title, $topic_theme);
            $stmt->execute();
            $topic_id = $stmt->insert_id;
            $stmt->close();

            // This is where more detailed parsing for activities, outcomes etc. would go.
            // For now, we have created the topic.

            $conn->commit();
            echo "    -> Successfully inserted topic with ID: $topic_id\n";

        } catch (Exception $e) {
            $conn->rollback();
            echo "    -> FAILED to insert topic. Error: " . $e->getMessage() . "\n";
        }
    }


    // 4. Cleanup
    unlink($temp_pdf_file);
    unlink($temp_txt_file);

    // Be a good bot
    sleep(2);
}

echo "\n\nProcessing finished.\n";
echo "</pre>";

$conn->close();
?>
