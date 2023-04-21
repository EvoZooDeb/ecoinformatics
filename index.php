<?php
#$protocol = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ? 'https' : 'http';
#require_once(getenv('PROJECT_DIR').'local_vars.php.inc');

$protocol = 'http';
define('PROJECTTABLE','Beporzó monitoring');
define('LOCATION','Mintavételi hely');
define('URL','openbiomaps.org/projects/pollimon');

# wms cluster layer name
# layer_data_MY-CLUSTER-LAYER for cname created automatically
#
$wms_cluster = 'my_cluster';
$min_zoom_for_filter = 12;
$distanceInput = 30;
$minDistanceInput = 1;

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- CODELAB: Add link rel manifest -->
  <link rel="manifest" href="manifest.json?v1">
<!-- CODELAB: Add iOS meta tags and icons -->
<!--   <meta name="apple-mobile-web-app-capable" content="yes"> -->
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <meta name="apple-mobile-web-app-title" content="OpenBioMaps">
  <link rel="apple-touch-icon" href="images/icons/Android/Icon-144.png">
  <link rel="icon" href="https://openbiomaps.org/img/favicon.ico" type="image/x-icon" />
  <!-- description -->
  <meta name="description" content="OpenBioMaps Map Query App">
  <!-- meta theme-color -->
  <meta name="theme-color" content="#aad2dd" />
  <link rel="stylesheet" type="text/css" href="styles/form-styles.css?v1">
  <link rel="stylesheet" href="https://openlayers.org/en/v5.3.0/css/ol.css" type="text/css">
  <link rel="stylesheet" href="https://unpkg.com/purecss@2.1.0/build/pure-min.css" integrity="sha384-yHIFVG6ClnONEA5yB5DJXfW2/KC173DIQrYoZMEtBvGzmf0PKiGyNEqe9N6BNDBH" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="styles/fontawesome-free-6.1.1-web/css/fontawesome.min.css">
  <link rel="stylesheet" type="text/css" href="styles/fontawesome-free-6.1.1-web/css/solid.min.css">
  <link rel="stylesheet" type="text/css" href="styles/inline.css?2">
  <!-- The line below is only needed for old environments like Internet Explorer and Android 4.x -->
  <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList,URL"></script>
  <!-- Local stroge -->
  <script src="scripts/localforage.js"></script>
  <!-- Keep screen on -->
  <script src="scripts/NoSleep.min.js"></script>

  <script src="https://cdn.rawgit.com/openlayers/openlayers.github.io/master/en/v5.3.0/build/ol.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <title><?php echo PROJECTTABLE ?></title>
</head>
<body>
<header class="header">
    <h1><form id='sample_site' class=""> 
        <select id='site_name' class="" name="site_name" selected="<?php echo ucfirst(LOCATION) ?>"><option><?php echo ucfirst(LOCATION) ?></option></select>
        <input id='observer' name="observer" placeholder="megfigyelő">
        </form></h1>
    <button id="butInstall" aria-label="Install" hidden></button>
    <i class='fa-solid fa-eye' id="togglekeep" title="Wake Lock is enabled" style='color:lightskyblue'></i>
</header>
<script src="scripts/install.js"></script>
<div style='min-height:56px'></div>
<div id="map" class="map"></div>
<div id='info' style='padding:6px;border-radius:5px;position:fixed;bottom:10px;right:10px;max-height:220px;width:auto;background-color:white;opacity:0.85;z-index:1;overflow-y: auto;'></div>
<div id='geoinfo' style='padding:6px;border-radius:5px;position:fixed;top:60px;right:5px;background-color:white;opacity:0.85;z-index:1001;overflow-y: auto;'><span id='accuracy'></span> <span id='speed'></span></div>

<!--Ide jön a form -->
<button type="button" id="open-form-button" class="pure-button button-ol button-secondary open-form-button" onclick="showSpeciesForm()"><i class="material-icons" style="font-size:24px;">keyboard_double_arrow_right</i></button>
<div class="form-container">
    <form action="/action_page.php" class="pure-form species-counter-form">
        <div style='width:100%'>
            <select class="pure-button dropdown-menu-js" id="fajok" name="fajok" selected="Válasszon egy fajt..." onchange="addOption()"></select>
        </div>
    </form>
    <button type="submit" class="pure-button button-success species-form-submit" style="display:none" onclick="submitData()"> Adatok beküldése </button>
    <div class="species-form-close" onclick="hideSpeciesForm()"><i class="material-icons" style="font-size:20px">cancel</i></div>
</div>
<script src="./scripts/species-counting-form.js?v2"></script>
<script src="./scripts/species-form-dropdown-content.js?v4"></script>

<div id="myAuthModal" class="modal">
<div class="modal-content">
    <span class="close" onclick="Close('myAuthModal')">&times;</span>
    <form method='post' id='authbox' name='authbox'>
    <label for='username'>Username: </label><br><input name='username' id='username' class='' autocomplete="username">
    <br>
    <br>
    <label for='password'>Password: </label><br><input type='password' name='password' id='password' class='' autocomplete="current-password">
    <br><br>
    <button id='sendLogIn' class='pure-button button-xlarge button-success'>Log in</button>
    </form>
</div>
</div>

    <!--
    <div>
      position accuracy : <code id="accuracy"></code>&nbsp;&nbsp;
      altitude : <code id="altitude"></code>&nbsp;&nbsp;
      altitude accuracy : <code id="altitudeAccuracy"></code>&nbsp;&nbsp;
      heading : <code id="heading"></code>&nbsp;&nbsp;
      speed : <code id="speed"></code>
    </div>-->

<script src="scripts/functions.js"></script>
<script src="scripts/main.js"></script>
<script type="text/javascript">
    const URL = '<?php echo URL ?>';

    /* Cluster layer */
    const distanceInput = <?php echo $distanceInput ?>;
    const minDistanceInput = <?php echo $minDistanceInput ?>;

    const min_zoom_for_filter = <?php echo $min_zoom_for_filter ?>;

    const target_table = '<?php echo isset($_GET['table']) ? $_GET['table'] : PROJECTTABLE ?>';

    const wms_cluster = '<?php echo $wms_cluster ?>';
    
    const jat = decodeURIComponent(getCookie('access_token'));
    const jrt = decodeURIComponent(getCookie('refresh_token'));

</script>
<script src="scripts/auth.js"></script>
<script src="scripts/map_init.js"></script>
<script src="scripts/map_wms_fetch.js"></script>
<script src="scripts/map_load.js"></script>

<script>
    // <!-- Register service worker. --->
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('service-worker.js')
            .then((reg) => {
              console.log('Service worker registered.', reg);
            });
      });
    }
</script>    
</body>
</html>
