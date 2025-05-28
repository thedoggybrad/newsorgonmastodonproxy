<?php

$rss_url = 'https://www.abs-cbn.com/feed';

// Create a stream context with HTTP headers, including user agent and follow redirects
$options = [
    'http' => [
        'method' => 'GET',
        'header' => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36',
            'Accept: application/rss+xml, application/xml, text/xml',
        ],
        'follow_location' => 1,
        'timeout' => 10
    ]
];

$context = stream_context_create($options);

$rss_content = @file_get_contents($rss_url, false, $context);

if ($rss_content === false) {
    echo "Failed to fetch RSS feed using file_get_contents.";
    // Optionally, you can inspect $http_response_header here for debugging
} else {
    // Just display the RSS feed content
    header('Content-Type: application/rss+xml; charset=utf-8');
    echo $rss_content;
}

?>
