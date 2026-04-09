<?php

// Start output buffering
ob_start();

$rss_url = 'https://manilastandard.net/ms-rss?category=news';

// Create a stream context with HTTP headers, including user agent and follow redirects
$options = [
    'http' => [
        'method' => 'GET',
        'header' => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36',
            'Accept: */*',
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
    // Ensure no output has been sent before setting the content type
    header('Content-Type: application/rss+xml; charset=utf-8');
    echo $rss_content;
}

// End output buffering and send all output
ob_end_flush();

?>
