<?php

$rss_url = 'https://manilastandard.net/ms-rss?category=news';

// Create a stream context with HTTP headers, including user agent and follow redirects
$options = [
    'http' => [
        'method' => 'GET',
        'header' => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36',
            'Accept: */*',
            // 'Accept: application/rss+xml, application/xml, text/xml',
        ],
        'follow_location' => 1,
        'timeout' => 30
    ]
];

$context = stream_context_create($options);

$rss_content = @file_get_contents($rss_url, false, $context);

if ($rss_content === false) {
    echo "Failed to fetch RSS feed using file_get_contents.";
} else {
    // Check if the content is valid XML
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadXML($rss_content);

    if ($dom->validate()) {
        // If it's a valid RSS XML, output it with proper content-type
        header('Content-Type: application/rss+xml; charset=utf-8');
        echo $rss_content;
    } else {
        // If the RSS is invalid, display errors
        echo "Invalid RSS feed format.";
        foreach(libxml_get_errors() as $error) {
            echo "<br>" . $error->message;
        }
    }
}

?>
