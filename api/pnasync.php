<?php

// Function to fetch HTML content using file_get_contents
function getHTMLContent($url) {
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)"
        ]
    ];
    $context = stream_context_create($opts);
    $html = file_get_contents($url, false, $context);
    return $html;
}

// Fetch the HTML content
$url = 'https://pna.gov.ph/';
$html = getHTMLContent($url);

if ($html === false) {
    die("Failed to retrieve content.");
}

// Parse the HTML using DOMDocument and DOMXPath
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// Extract article items
$articles = $xpath->query('//div[contains(@class, "article-item")]');

// Extract article details
$items = [];
foreach ($articles as $article) {
    $img = $xpath->query('.//img', $article)->item(0);
    $link = $xpath->query('.//a', $article)->item(0);
    $title = $link ? $link->nodeValue : '';

    // Extract the main date (ignore the "Updated on" part)
    $dateSpan = $xpath->query('.//span[contains(@class, "ms-1.5")]', $article)->item(0);
    $date = $dateSpan ? trim($dateSpan->nodeValue) : 'Unknown Date';

    // Remove "Updated on" if present
    $updatedTextStart = strpos($date, 'Updated on');
    if ($updatedTextStart !== false) {
        $date = trim(substr($date, 0, $updatedTextStart));
    }

    // Check if date is valid and convert it
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

// Prepare the RSS feed content
header('Content-Type: application/rss+xml; charset=UTF-8');

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
    if ($item['image']) {
        echo '<enclosure url="' . htmlspecialchars($item['image']) . '" type="image/jpeg" />';
    }
    echo '</item>';
}

echo '</channel>';
echo '</rss>';
