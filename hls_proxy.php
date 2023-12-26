<?php
error_reporting(0);
set_time_limit(0);

function locateBaseURL() {
    global $userSetHost;

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    $domain = isset($userSetHost) && !empty($userSetHost) ? $protocol . $userSetHost : $protocol . $_SERVER['HTTP_HOST'];

    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $scriptDir = ($scriptDir === '/' || $scriptDir === '\\') ? '' : trim($scriptDir, '/\\');

    $baseUrl = rtrim($domain, '/') . '/' . $scriptDir;
    $baseUrl = rtrim($baseUrl, '/') . '/'; // Ensure only one trailing slash

    return $baseUrl;
}
$proxyUrl = locateBaseURL() . "hls_proxy.php";

// Function to fetch content from a URL with optional additional headers
function fetchContent($url, $additionalHeaders = []) {
    $decodedData = base64_decode($_GET['data']);
    $parts = explode('|', $decodedData);
    $maxRedirects = 5;

    $httpOptions = [
        'http' => [
            'method' => 'GET',
            'follow_location' => false, // Disable automatic redirect following
            'max_redirects' => $maxRedirects,
            'header' => []
        ]
    ];

    foreach ($parts as $headerData) {
        $equalPos = strpos($headerData, '=');
        if ($equalPos !== false) {
            $header = substr($headerData, 0, $equalPos);
            $value = trim(substr($headerData, $equalPos + 1), "'\"");
            $httpOptions['http']['header'][] = "$header: $value";
        }
    }

    if (isset($_SERVER['HTTP_RANGE'])) {
        $httpOptions['http']['header'][] = "Range: " . $_SERVER['HTTP_RANGE'];
    }

    $context = stream_context_create($httpOptions);
    $redirectCount = 0;
    $finalUrl = $url;

    do {
        $response = file_get_contents($finalUrl, false, $context);
        $responseHeaders = $http_response_header; // Automatically populated by file_get_contents

        $isRedirect = false;
        foreach ($responseHeaders as $header) {
            if (preg_match('/^Location:\s*(.*)$/i', $header, $matches)) {
                $finalUrl = trim($matches[1]);
                $isRedirect = true;
                break;
            }
        }

        if ($isRedirect && $redirectCount < $maxRedirects) {
            $redirectCount++;
        } else {
            // Either not a redirect, or max redirects reached
            break;
        }
    } while ($isRedirect);

    return ['content' => $response, 'finalUrl' => $finalUrl];
}

// Function to check if the request is for a master playlist
function isMasterRequest($queryParams) {
    return isset($queryParams['url']) && !isset($queryParams['url2']);
}

// Function to rewrite URLs within HLS playlist content
function rewriteUrls($content, $baseUrl, $proxyUrl, $data, $domain) {
    $lines = explode("\n", $content);
    $rewrittenLines = [];
    $isNextLineUri = false;

    foreach ($lines as $line) {
        if (empty(trim($line)) || $line[0] === '#') {
            if (preg_match('/URI="([^"]+)"/i', $line, $matches)) {
                $uri = $matches[1];
                if (strpos($uri, 'hls_proxy.php') === false) {
                    $rewrittenUri = $proxyUrl . '?url=' . urlencode($uri) . '&data=' . urlencode($data);
                    $line = str_replace($uri, $rewrittenUri, $line);
                }
            }
            $rewrittenLines[] = $line;

            if (strpos($line, '#EXT-X-STREAM-INF') !== false) {
                $isNextLineUri = true;
            }
            continue;
        }

        $urlParam = $isNextLineUri ? 'url' : 'url2';

        if (!filter_var($line, FILTER_VALIDATE_URL)) {
            $line = rtrim($baseUrl, '/') . '/' . ltrim($line, '/');
        }

        if (strpos($line, 'hls_proxy.php') === false) {
            $rewrittenLines[] = $proxyUrl . "?$urlParam=" . urlencode($line) . '&data=' . urlencode($data);
        } else {
            $rewrittenLines[] = $line;
        }

        $isNextLineUri = false;
    }

    return implode("\n", $rewrittenLines);
}

// Main processing logic
$isMaster = isMasterRequest($_GET);
$data = $_GET['data'] ?? '';
$requestUrl = $isMaster ? ($_GET['url'] ?? '') : ($_GET['url2'] ?? '');
$result = fetchContent($requestUrl, $data);
$content = $result['content'];
$finalUrl = $result['finalUrl'];
$baseUrl = dirname($finalUrl);

if ($isMaster) {
    $content = rewriteUrls($content, $baseUrl, $proxyUrl, $data, $domain);
}

echo $content;



?>

