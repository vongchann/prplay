<?php
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

 //    $jsonFilePath = "https://dplayer.katme.link/channels/live_playlist.json";
 //    $jsonContent = file_get_contents($jsonFilePath);
 //    $datajs = json_decode($jsonContent, true);

	// $urlParam = $datajs[2]['video_url'];	

	$urlParam = "https://dplayer.katme.link/hls_proxy.php?url=https://cf.khfullhdcdn03.lol/hls/2Ufa9256e3EO73A/playlist.m3u8&data=UmVmZXJlcj0iaHR0cHM6Ly9raGZ1bGxoZC5uZXQvIg==";

	if (stripos($urlParam, 'DaddyLive|') !== false) {
		$parts = explode('|', $urlParam);
		
		if (count($parts) >= 3) {
			$data = [
				'url' => $parts[1],
				'ref' => $parts[2]
			];

			if ($data) {
				$base = locateBaseURL();        
				$urlparts = 'https://dplayer.katme.link/hls_proxy.php?url=' . urlencode($data['url']) . '&data=' . base64_encode($data['ref']);
				header('Location: ' . $urlparts, true, 302);
			} else {
				header("HTTP/1.0 404 Not Found");
			}
		} else {
			header("HTTP/1.0 404 Not Found");
		}
		exit;
	}
    header('Location: ' . $urlParam, true, 302);
	exit;
	


?>
