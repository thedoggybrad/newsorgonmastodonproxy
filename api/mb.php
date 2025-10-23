<?php

$rss_url = 'https://mb.com.ph/rss/articles';

// Create a stream context with HTTP headers, including user agent and follow redirects
$options = [
    'http' => [
        'method' => 'GET',
        'header' => [
            'User-Agent: facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
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
