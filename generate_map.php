<?php
echo "<pre>";

$json_file = 'ncdc_pdfs.json';
$csv_file = 'curriculum_map.csv';

if (!file_exists($json_file)) {
    die("ERROR: ncdc_pdfs.json not found.");
}

$pdf_list = json_decode(file_get_contents($json_file), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("ERROR: Invalid JSON in ncdc_pdfs.json. Error: " . json_last_error_msg());
}

// Get unique URLs to avoid duplicate work for the user
$unique_urls = [];
foreach ($pdf_list as $pdf) {
    $unique_urls[$pdf['url']] = 1;
}
$urls = array_keys($unique_urls);
sort($urls);

echo "Found " . count($urls) . " unique PDF URLs.\n";

// Create CSV content
$csv_content = "url,subject_name,class_level_name\n";
foreach ($urls as $url) {
    // We escape the URL in case it contains commas
    $csv_content .= '"' . $url . '",,';
    $csv_content .= "\n";
}

if (file_put_contents($csv_file, $csv_content)) {
    echo "Successfully created mapping file: " . $csv_file . "\n";
} else {
    echo "ERROR: Could not write to " . $csv_file . "\n";
}

echo "</pre>";
?>
