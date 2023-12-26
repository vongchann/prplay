<html>
<head>
<title>MHDTVWORLD.ME | Watch Your Favourite Indian TV Channels Anytime Anywher</title>
<!-- <link rel="stylesheet" type="text/css" href="/clap.css"> -->


<div id="player" style="height: 100%; width: 100%;"></div>
</head>  
<body> 
<script type="text/javascript" src="https://cdn.jsdelivr.net/clappr/latest/clappr.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/clappr.level-selector/latest/level-selector.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/@clappr/hlsjs-playback@1.0.1/dist/hlsjs-playback.min.js"></script>



<?php 

    // $uriSegments = explode("/", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    
// https://dplayer.katme.link/hls_proxy.php?url=https://cf.khfullhdcdn03.lol/hls/2Ufa9256e3EO73A/playlist.m3u8&data=UmVmZXJlcj0iaHR0cHM6Ly9raGZ1bGxoZC5uZXQvIg==


    // $_GET['subject']
    $source = 'https://dplayer.katme.link/live_play.php'; 
    $baseurl = 'https://dplayer.katme.link/hls_proxy.php?url=';
    // $mediaurl = 'https://cf.khfullhdcdn03.lol/hls/2Ufa9256e3EO73A/playlist.m3u8';
    $mediaurl = $_GET['url'];
    // $data = '&data=UmVmZXJlcj0iaHR0cHM6Ly9raGZ1bGxoZC5uZXQvIg==';
    $data = $_GET['data'];

    // source: '<?php echo $baseurl.$mediaurl.'&data='.$data
    
    echo $baseurl.$mediaurl.'&data='.$data;  
?>
<script>
        var player = new Clappr.Player({
            source: '<?php echo $baseurl.$mediaurl.'&data='.$data; ?>',
            width: '100%',
            height: '100%',
            autoPlay: true,
            plugins: [HlsjsPlayback, LevelSelector],
            mimeType: "application/x-mpegURL",
            mediacontrol: { seekbar: "#ff0000", buttons: "#eee" },
            parentId: "#player",
        });
    </script>
    </body>
</html>