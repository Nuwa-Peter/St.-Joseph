<?php
// This script is designed to be run from the command line if possible,
// but since it's not, it will save its output to a file.
// It might take a long time to run.

set_time_limit(600); // 10 minutes max execution time

echo "Starting NCDC PDF scraper...\n";

function fetch_website_content($url) {
    // A simple wrapper for file_get_contents with a user agent
    $options = [
        'http' => [
            'method' => "GET",
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36\r\n"
        ]
    ];
    $context = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

$base_url = 'https://ncdc.go.ug/resource/?_page=';
$total_pages = 27; // Based on previous observation
$all_pdfs = [];

for ($i = 1; $i <= $total_pages; $i++) {
    $url = $base_url . $i;
    echo "Fetching page " . $i . " of " . $total_pages . ": " . $url . "\n";

    $html = fetch_website_content($url);
    if ($html === false) {
        echo "Failed to fetch page " . $i . ". Skipping.\n";
        continue;
    }

    // Find all links that end with .pdf
    preg_match_all('/<a\s+[^>]*href="([^"]+\.pdf)"[^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $pdf_url = $match[1];
        // The link text might be inside other tags, so we need to clean it up
        $link_text = trim(strip_tags($match[2]));

        // Skip if the link text is something generic like "Free Download"
        if (empty($link_text) || strtolower($link_text) === 'free download') {
            // Let's try to find the title from the context
            // A common pattern is the link is inside a <div class="book-title"> or similar
            // This is complex, for now we will use the URL's filename as a fallback title
            $path_parts = pathinfo($pdf_url);
            $link_text = $path_parts['filename'];
            $link_text = str_replace(['-', '_'], ' ', $link_text); // Clean up filename
        }

        $inferred_subject = 'Uncategorized';
        $inferred_class_level = 'Uncategorized';

        // Infer class level (e.g., S.4, P.7)
        if (preg_match('/(S\.?\s?[1-6])|(P\.?\s?[1-7])/i', $link_text, $class_match)) {
            $inferred_class_level = strtoupper(str_replace(['.', ' '], '', $class_match[0]));
            // Remove the class level from the title to get the subject
            $inferred_subject = trim(str_ireplace($class_match[0], '', $link_text));
        } else {
            $inferred_subject = $link_text;
        }

        // Further clean up subject
        $inferred_subject = preg_replace('/\s*(Learners Book|Teacher’s Guide|Syllabus|Training Manual|Guide|Book|FINAL.*|Web file.*)\s*/i', '', $inferred_subject);
        $inferred_subject = trim($inferred_subject);

        $all_pdfs[] = [
            'url' => $pdf_url,
            'original_text' => $link_text,
            'inferred_subject' => $inferred_subject,
            'inferred_class_level' => $inferred_class_level
        ];

        echo "  - Found: " . $pdf_url . " (" . $inferred_subject . " / " . $inferred_class_level . ")\n";
    }

    // Be a good bot and wait a second between requests
    sleep(1);
}

$output_file = 'ncdc_pdfs.json';
$json_data = json_encode($all_pdfs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if (file_put_contents($output_file, $json_data)) {
    echo "\nScraping complete. Found " . count($all_pdfs) . " PDF links.\n";
    echo "Data saved to " . $output_file . "\n";
} else {
    echo "\nError: Could not write data to " . $output_file . "\n";
}

?>
