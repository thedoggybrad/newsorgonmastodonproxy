<?php

// Function to fetch HTML content using cURL
function getHTMLContent($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    
    $html = curl_exec($ch);
    curl_close($ch);
    
    return $html;
}

// Fetch the HTML content
$url = 'https://www.pna.gov.ph/articles/list';
$html = getHTMLContent($url);

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
    $date = $dateSpan ? trim($dateSpan->nodeValue) : 'Unknown Date';  // Get the date or set 'Unknown Date'

    // If there's an "Updated on" part in the same span, we remove it
    $updatedTextStart = strpos($date, 'Updated on');
    if ($updatedTextStart !== false) {
        $date = trim(substr($date, 0, $updatedTextStart));  // Keep only the main date
    }

    // Check if date is valid and convert it
    $timestamp = strtotime($date);  // Try converting the date
    if (!$timestamp) {
        // If strtotime() fails, set it to a default date or skip
        $date = 'Unknown Date';
        $timestamp = time(); // Set current timestamp for fallback
    }
    
    // Format the date for RSS pubDate
    $rssDate = date(DATE_RSS, $timestamp);  // Format the date for RSS

    $imageUrl = $img ? $img->getAttribute('src') : '';
    $articleLink = $link ? $link->getAttribute('href') : '';

    $items[] = [
        'image' => $imageUrl,
        'link' => $articleLink,
        'title' => $title,
        'date' => $rssDate // Add the formatted date for RSS
    ];
}

// Prepare the RSS feed content
$rssContent = '<?xml version="1.0" encoding="UTF-8"?>';
$rssContent .= '<rss version="2.0">';
$rssContent .= '<channel>';
$rssContent .= '<title>Philippine News Agency - Latest Articles</title>';
$rssContent .= '<link>https://www.pna.gov.ph/articles/list</link>';
$rssContent .= '<description>Latest news articles from the Philippine News Agency</description>';

// Add each article to the RSS feed
foreach ($items as $item) {
    $rssContent .= '<item>';
    $rssContent .= '<title>' . htmlspecialchars($item['title']) . '</title>';
    $rssContent .= '<link>' . htmlspecialchars($item['link']) . '</link>';
    $rssContent .= '<description>' . htmlspecialchars($item['title']) . '</description>';
    $rssContent .= '<pubDate>' . $item['date'] . '</pubDate>'; // Use the formatted date here
    $rssContent .= '<enclosure url="' . htmlspecialchars($item['image']) . '" type="image/jpeg" />';
    $rssContent .= '</item>';
}

$rssContent .= '</channel>';
$rssContent .= '</rss>';

// Save the RSS feed to a file
$filePath = 'rss_feed.xml'; // Specify the path where you want to save the file
file_put_contents($filePath, $rssContent);

// Output a confirmation message
echo "RSS feed has been saved to: " . realpath($filePath);

?>
