<?php

function getHTMLContent($url) {
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) " .
                        "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\r\n",
            "timeout" => 30
        ],
        "ssl" => [
            // Force TLS 1.2
            "crypto_method" => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
            "verify_peer" => true,
            "verify_peer_name" => true,
        ]
    ];

    $context = stream_context_create($opts);
    $html = @file_get_contents($url, false, $context);
    return $html;
}

$url = 'https://www.pna.gov.ph/articles/list';
$html = getHTMLContent($url);

if ($html === false) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Failed to fetch HTML content.";
    exit;
}

libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

$articles = $xpath->query('//div[contains(@class, "article-item")]');

$items = [];
foreach ($articles as $article) {
    $img = $xpath->query('.//img', $article)->item(0);
    $link = $xpath->query('.//a', $article)->item(0);
    $title = $link ? trim($link->nodeValue) : '';

    $dateSpan = $xpath->query('.//span[contains(@class, "ms-1.5")]', $article)->item(0);
    $date = $dateSpan ? trim($dateSpan->nodeValue) : 'Unknown Date';

    $updatedTextStart = strpos($date, 'Updated on');
    if ($updatedTextStart !== false) {
        $date = trim(substr($date, 0, $updatedTextStart));
    }

    $timestamp = strtotime($date);
    if (!$timestamp) {
        $date = 'Unknown Date';
        $timestamp = time();
    }

    $rssDate = date(DATE_RSS, $timestamp);

    $imageUrl = $img ? $img->getAttribute('src') : '';
    $articleLink = $link ? $link->getAttribute('href') : '';

    $items[] = [
        'image' => $imageUrl,
        'link' => $articleLink,
        'title' => $title,
        'date' => $rssDate
    ];
}

header('Content-Type: application/rss+xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<rss version="2.0">';
echo '<channel>';
echo '<title>Philippine News Agency - Latest Articles</title>';
echo '<link>https://www.pna.gov.ph/articles/list</link>';
echo '<description>Latest news articles from the Philippine News Agency</description>';

foreach ($items as $item) {
    echo '<item>';
    echo '<title>' . htmlspecialchars($item['title']) . '</title>';
    echo '<link>' . htmlspecialchars($item['link']) . '</link>';
    echo '<description>' . htmlspecialchars($item['title']) . '</description>';
    echo '<pubDate>' . $item['date'] . '</pubDate>';
    echo '<enclosure url="' . htmlspecialchars($item['image']) . '" type="image/jpeg" />';
    echo '</item>';
}

echo '</channel>';
echo '</rss>';
