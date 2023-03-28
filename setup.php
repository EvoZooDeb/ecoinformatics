<!doctype html>
<html lang="en">
<head>
<style>
body {
    font-size:16px;
}
</style>
</head>
<body>
<?php

$protocol = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ? 'https' : 'http';
require_once(getenv('PROJECT_DIR').'local_vars.php.inc');

$version = (isset($_GET['version'])) ? 'v'.$_GET['version'] : 'v1.0.1';

$manifest = '
{
  "name": "",
  "short_name": "",
  "icons": [{
    "src": "images/icons/iOS/Icon-128.png",
      "sizes": "128x128",
      "type": "image/png"
    }, {
      "src": "images/icons/Android/Icon-144.png",
      "sizes": "144x144",
      "type": "image/png"
    }, {
      "src": "images/icons/iOS/Icon-152.png",
      "sizes": "152x152",
      "type": "image/png"
    }, {
      "src": "images/icons/Android/Icon-192.png",
      "sizes": "192x192",
      "type": "image/png"
    }, {
      "src": "images/icons/iOS/Icon-256.png",
      "sizes": "256x256",
      "type": "image/png"
    }, {
      "src": "images/icons/Android/Icon-512.png",
      "sizes": "512x512",
      "type": "image/png"
    }],
  "start_url": "index.php",
  "display": "standalone",
  "background_color": "#ececec",
  "theme_color": "#0a7a99"
}';

$manifest_j = json_decode($manifest,true);

$manifest_j['name'] = PROJECTTABLE . ' Map Query App' . ' ' . $version;
$manifest_j['short_name'] = PROJECTTABLE;


echo "<h2>Installing pwa</h2>";


if (file_put_contents('manifest.json', json_encode($manifest_j))) {
    echo "<br>Done.<br>";
    echo "<a href='$protocol://".URL."/pwa/index.php'>Visit PWA</a>";
} else {
    echo "<br>Installation Failed.";
}

?>
</body>
</html>
