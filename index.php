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
  <link rel="stylesheet" type="text/css" href="./form-styles.css?v1">
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
<script src="./scripts/species-counting-form.js?v1"></script>
<script src="./scripts/species-form-dropdown-content.js?v1"></script>

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

<script type="text/javascript">
    $(document).ready(function() {
        filter();
        $("#open-form-button").hide();
    });
    function Close(e) {
        let el = document.getElementById(e);
        el.style.display = "none";
    }
    const jat = decodeURIComponent(getCookie('access_token'));
    const jrt = decodeURIComponent(getCookie('refresh_token'));

    let at_expiry = false;
    let rt_expiry = false;
    const d = new Date();

    if (jat !== 'undefined') {
        const p1 = JSON.parse(jat);
        const p2 = JSON.parse(jrt);
        at_expiry = eval(p1.expiry - (d.getTime()/1000));
        rt_expiry = eval(p2.expiry - (d.getTime()/1000));
    }

    let access_token;
    let refresh_token;
    /*const alertinfo = document.getElementById('info');
    alertinfo.innerHTML = jat + '<br>';
    alertinfo.innerHTML += jrt + '<br>';
    alertinfo.innerHTML += rt_expiry;*/
   
    // Get the modal
    var modal = document.getElementById("myAuthModal");

    if (jrt === 'undefined') {
        // No refresh token

        //modal.style.display = "block";

    } else if (jrt !== 'undefined' && !at_expiry && rt_expiry) {
        // Has refresh token, and not expired but access_token expired
        const jrt_p = JSON.parse(jrt);
        refresh_token = jrt_p.data.refresh_token;

        $.ajax({
            type: "POST",
            url: 'https://<?php echo URL ?>/oauth/token.php',
            headers: {'Content-type': 'application/x-www-form-urlencoded','Authorization':"Basic " + btoa('web' + ":" + 'web')},
            data: {
                refresh_token: refresh_token,
                client_id: 'web',
                client_secret: 'web',
                grant_type:'refresh_token'
            },
            async: false,
            dataType: 'json',
            success: function (response) {
                access_token = response['access_token'];
                refresh_token = response['refresh_token'];
                setCookie('access_token',access_token,1);
                setCookie('refresh_token',refresh_token,336);
            }, 
            error: function() {
                alert('Log in first!');
            }
        });
    } else if (jrt !== 'undefined'){
        // Has an access token which not expired
        console.log('Refreshing tokens with valid access token');
        const jrt_p = JSON.parse(jrt);
        refresh_token = jrt_p.data.refresh_token;
        $.ajax({
            type: "POST",
            url: 'https://<?php echo URL ?>/oauth/token.php',
            headers: {'Content-type': 'application/x-www-form-urlencoded','Authorization':"Basic " + btoa('web' + ":" + 'web')},
            data: {
                refresh_token: refresh_token,
                client_id: 'web',
                client_secret: 'web',
                grant_type:'refresh_token'
            },
            async: false,
            dataType: 'json',
            success: function (response) {
                access_token = response['access_token'];
                refresh_token = response['refresh_token'];
                setCookie('access_token',access_token,1);
                setCookie('refresh_token',refresh_token,336);
            }, 
            error: function() {
                console.log('Invalid refresh tokens....');
                // Invalid refresh token has been used. It has been kicked out by a concurrent request
                //alert('Token refreshing failed, log in to access resources!');
                if (jat !== 'undefined') {
                    const jat_p = JSON.parse(jat);
                    access_token = jat_p.data.access_token;
                    $.ajax({
                        type: "POST",
                        url: 'https://<?php echo URL ?>/oauth/token.php',
                        headers: {'Content-type': 'application/x-www-form-urlencoded','Authorization':"Basic " + btoa('web' + ":" + 'web')},
                        data: {
                            access_token: access_token,
                            client_id: 'web',
                            client_secret: 'web',
                            grant_type:'refresh_token'
                        },
                        async: false,
                        dataType: 'json',
                        success: function (response) {
                            access_token = response['access_token'];
                            refresh_token = response['refresh_token'];
                            setCookie('access_token',access_token,1);
                            setCookie('refresh_token',refresh_token,336);
                        }, 
                        error: function() {
                            console.log('Invalid access tokens....');
                            modal.style.display = "block";
                            // Invalid refresh token has been used. It has been kicked out by a concurrent request
                            //alert('Token refreshing failed, log in to access resources!');
                        }
                    })
                }
            }
        });
    }
    let tables;
    $.ajax({
        type: "POST",
        url: 'https://<?php echo URL ?>/v2.4/pds.php',
        data: {
            access_token: access_token,
            scope: 'get_tables',
            table: '<?php echo isset($_GET['table']) ? $_GET['table'] : PROJECTTABLE ?>' 
        },
        dataType: 'json',
        success: function (response) {
            tables = response['data'];
            tables.sort();
            let x = tables.map(function(v) {
                return $('<option/>', {
                  value: v,
                  text: v
                })
            });
            $('#table_list').append(x);
            $('#table_list').val('<?php echo isset($_GET['table']) ? $_GET['table'] : PROJECTTABLE ?>');
                
         },
        error: function () {
            //alert("MAP connection error!");
        }
    });

    /*let body = {
        access_token: access_token,
        scope: 'get_tables'
    }

    fetch("https://openbiomaps.org/projects/<?php echo PROJECTTABLE ?>/v2.4/pds.php", {
        method: 'post',
        body: JSON.stringify(body),
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    }).then((response) => {
        return response.json()
    }).then((res) => {
        if (res.status === 201) {
            console.log("Post successfully created!")
        }
    }).catch((error) => {
        console.log(error)
    })*/

    // Screen lock control
    var noSleep = new NoSleep();
    noSleep.enable(); // keep the screen on!
    var wakeLockEnabled = true;
    var toggleEl = document.querySelector("#togglekeep");
    toggleEl.addEventListener('click', function() {
        if (!wakeLockEnabled) {
          noSleep.enable(); // keep the screen on!
          wakeLockEnabled = true;
          toggleEl.title = "Wake Lock is enabled";
          toggleEl.style.color = "lightskyblue";
        } else {
          noSleep.disable(); // let the screen turn off.
          wakeLockEnabled = false;
          toggleEl.title = "Wake Lock is disabled";
          toggleEl.style.color = "darkslategray";
        }
    }, false);

    var myAuth = document.getElementById('sendLogIn');
    myAuth.addEventListener('click', auth, false);

    function auth(e) {
        e.preventDefault();
        let u = document.getElementById("username").value;
        let p = document.getElementById("password").value;
        $.ajax({
            type: "POST",
            headers: {
                "Authorization": "Basic " + btoa('web' + ":" + 'web')
            },
            url: 'https://<?php echo URL ?>/oauth/token.php',
            data: {
                grant_type: 'password',
                username:u,
                password:p,
                scope:'get_data get_tables webprofile'
            },
            async: false,
            dataType: 'json',
            success: function (response) {
                access_token = response['access_token'];
                refresh_token = response['refresh_token'];
                setCookie('access_token',access_token,1);
                setCookie('refresh_token',refresh_token,336);
                location.reload();
            }, 
            error: function() {
                alert('Log in failed!');
            }
        });
    }

    const bbox = [-180.0000, -85.0000, 180.0000, 85.0000];
    let extent = ol.proj.transformExtent(bbox, 'EPSG:4326', 'EPSG:3857');
    const projection = new ol.proj.Projection({
        code: 'EPSG:900913',
        extent: extent,
    });

    /* Cluster layer */
    const distanceInput = <?php echo $distanceInput ?>;
    const minDistanceInput = <?php echo $minDistanceInput ?>;
    var features;
    var styleCache = {};
    var styleFunctionIsChangingFeature = false;
    var styleFunction = function (evt) {
        if (styleFunctionIsChangingFeature) {
            return;
        }
        var feature = evt.feature;
        var size = feature.get('features').length;
        var style = styleCache[size];
        if (!style) {
            style = new ol.style.Style({
                image: new ol.style.Circle({
                    radius: 16,
                    stroke: new ol.style.Stroke({
                        color: '#fffb2a'
                    }),
                    fill: new ol.style.Fill({
                        color: '#3399CC'
                    })
                }),
                text: new ol.style.Text({
                    text: size.toString(),
                    font: 'bold 11px sans-serif',
                    fill: new ol.style.Fill({
                        color: '#fffb2a'
                    })
                })
            });
            styleCache[size] = style;
        }
        styleFunctionIsChangingFeature = true;
        feature.setStyle(style);
        styleFunctionIsChangingFeature = false;
    };
    const clusterSource = new ol.source.Cluster({
      distance: parseInt(distanceInput, 10),
      minDistance: parseInt(minDistanceInput, 10),
      source: new ol.source.Vector(),
    });
    clusterSource.on('addfeature', styleFunction);
    clusterSource.on('changefeature', styleFunction);
    
    var clusters = new ol.layer.Vector({
        source: clusterSource
    });

    // Vector draw source and layer 
    const drawSource = new ol.source.Vector();
    const drawLayer = new ol.layer.Vector({
        source: drawSource,
        style: new ol.style.Style({
            fill: new ol.style.Fill({
              color: 'rgba(255, 255, 255, 0.2)',
            }),
            stroke: new ol.style.Stroke({
              color: '#ff3833',
              width: 2,
            }),
            image: new ol.style.Circle({
              radius: 7,
              fill: new ol.style.Fill({
                color: '#ff3833',
              }),
            }),
        }),
    });
    

    let wmsSource = new ol.source.ImageWMS();

    // access_token hack !!!!
    access_token = '';

    if (access_token != '') {

        // Force refreshing SESSION for access_token
        $.ajax({
            type: "POST",
            url: 'https://<?php echo URL ?>/v2.4/pds.php',
            data: {
                access_token: access_token,
                scope: 'request_time',
                value: Math.round(d.getTime()/1000),
                table: '<?php echo isset($_GET['table']) ? $_GET['table'] : PROJECTTABLE ?>' 
            },
            async: false,
            dataType: 'json',
            success: function (response) {
                //alert('Fetching WMS')
                wmsSource = new ol.source.ImageWMS({
                    url: 'https://<?php echo URL ?>/private/proxy.php',
                        params: {map:'PMAP',LAYERS:'<?php echo $wms_cluster ?>', isBaseLayer:'false', visibility:'true', opacity:'1.0', format:'image/png', transparent:'true', numZoomLevels:'20',CNAME:'layer_data_<?php echo $wms_cluster ?>'}, 
                    ratio:1, 
                    serverType: 'mapserver'
                });
            },
            error: function () {
                alert("MAP connection error!");
            }
        });
    } else {
        //console.log("Can't connect to server!");
    }

    var wmsLayer = new ol.layer.Image({
        //extent: [-13884991, 2870341, -7455066, 6338219],
        source: wmsSource
    });

    const map = new ol.Map({
        target: 'map',
        layers: [
          new ol.layer.Tile({
            source: new ol.source.OSM()
          }),
          wmsLayer,
          clusters,
          drawLayer
        ],
        view: new ol.View({
          center: ol.proj.fromLonLat([18.854118,47.458825]),
          projection: projection,
          zoom: 8
        })
    });

    /* GeoLocation info:
        - Speed info
        - Accuracy info
        - Position symbol
        - Accuracy symbol
     */
    const geolocation = new ol.Geolocation({
      // enableHighAccuracy must be set to true to have the heading value.
      trackingOptions: {
        enableHighAccuracy: true,
      },
      projection: map.getView().getProjection(),
    });
    geolocation.setTracking(true);
    function el(id) {
      return document.getElementById(id);
    }
    /*el('track').addEventListener('change', function () {
      geolocation.setTracking(this.checked);
    });*/
    geolocation.on('change', function () {
      el('accuracy').innerText = Math.round(geolocation.getAccuracy() * 10) / 10 + ' [m]';
    //  el('altitude').innerText = geolocation.getAltitude() + ' [m]';
    //  el('altitudeAccuracy').innerText = geolocation.getAltitudeAccuracy() + ' [m]';
    //  el('heading').innerText = geolocation.getHeading() + ' [rad]';
      el('speed').innerText = Math.round(geolocation.getSpeed() * 36) / 10 + ' [km/h]';
    });
    // handle geolocation error.
    geolocation.on('error', function (error) {
      const info = document.getElementById('geoinfo');
      info.innerHTML = error.message;
      info.style.display = '';
    });
    const accuracyFeature = new ol.Feature();
    accuracyFeature.setStyle(
        new ol.style.Style({
            fill: new ol.style.Fill({
              color: 'rgba(255, 255, 255, 0.2)',
            }),
            stroke: new ol.style.Stroke({
              color: '#a3a3a3',
              width: 2,
            })
        })
    );
    geolocation.on('change:accuracyGeometry', function () {
      accuracyFeature.setGeometry(geolocation.getAccuracyGeometry());
    });
    const positionFeature = new ol.Feature();
    positionFeature.setStyle(
      new ol.style.Style({
        image: new ol.style.Circle({
          radius: 4,
          fill: new ol.style.Fill({
            color: '#ffe81f',
          }),
          stroke: new ol.style.Stroke({
            color: '#3a3a3a',
            width: 2,
          }),
        }),
      })
    );
    geolocation.on('change:position', function () {
        const coordinates = geolocation.getPosition();
        positionFeature.setGeometry(coordinates ? new ol.geom.Point(coordinates) : null);
    });
    var geolocationLayer = new ol.layer.Vector({
      map: map,
      source: new ol.source.Vector({
        features: [accuracyFeature, positionFeature],
      }),
    });


    const modify = new ol.interaction.Modify({source: drawSource});
    map.addInteraction(modify);
         
    /*const snap = new ol.interaction.Snap({
      source: drawLayer.getSource(),
    });
    map.addInteraction(snap);
    */


    let properties;

    map.on('click', (e) => {
        const clickedFeatures = map.forEachFeatureAtPixel(e.pixel, function (feature, layer) {
            if (layer !== null) {
                return feature;
            } else
                return false;
        });
        const info = document.getElementById('info');
        info.innerHTML = '<span class="close" @click="Close(\'info\')" style="margin:0">&times;</span>';
        info.style.display = 'block';
        if (clickedFeatures) {
            const cfeatures = clickedFeatures.get('features');
            if (cfeatures.length) {
                let text;
                let n = 0;
                cfeatures.forEach((e, index, arr) => {
                    n++;
                    properties = e.getProperties();
                    Object.keys(properties).forEach(key => {
                        if (key == 'geometry') { return; }
                        info.innerHTML += '<b>' + key + '</b>: ' + properties[key] + '<br>';
                    });
                    info.innerHTML += '<hr>';
                    if (n > 5) {
                        info.innerHTML += '....';
                        arr.length = index + 1;
                    }
                });
            }
        } else {
            //Zoom to click
            let v = e.coordinate;
            
            /*let extent = new ol.extent.boundingExtent([[eval(v[0]-500),eval(v[1]-500)],[eval(v[0]+500),eval(v[1]+500)]]);
            map.getView().fit(extent, {duration: 1000, padding: [50, 50, 50, 50]});*/
            var actualZoom = map.getView().getZoom();
            map.getView().setCenter([v[0],v[1]]);
            map.getView().animate({center: [v[0],v[1]]}, {zoom: eval(actualZoom + 2)});
        }
    });
    map.on('moveend', function(e) {
        filter();
    });

      
    /* Tracklogging
        - Trackline draw
        - Following geolocation position changes on map
     */
    const tracklineSource = new ol.source.Vector();
    const tracklineLayer = new ol.layer.Vector({
        source: tracklineSource,
        style: new ol.style.Style({
          stroke: new ol.style.Stroke({
              color: [0,0,0,0.6],
              width: 2,
              lineDash: [4,8],
              lineDashOffset: 6
          }),
        }),
    });
    map.addLayer(tracklineLayer);

    const track = document.createElement('div');
    track.className = 'ol-control ol-unselectable track';
    track.innerHTML = '<button id="trackloc" title="Turn off tracking"><i class="fa-solid fa-route" style=""></i></button>';
    map.addControl(new ol.control.Control({
        element: track
    }));

    var watchID;
    var trackWatch = function(e) {
        if (e=='off') {
            navigator.geolocation.clearWatch(watchID);
            document.getElementById("trackloc").innerHTML = '<i class="fa-solid fa-route" style="color:darkslategray"></i>';
            document.getElementById("trackloc").title = "Turn on location tracking";
        } else {
            document.getElementById("trackloc").innerHTML = '<i class="fa-solid fa-route" style="color:white"></i>';
            document.getElementById("trackloc").title = "Turn off location tracking";
            let trackline_start = 0;
                watchID = navigator.geolocation.watchPosition(function(pos) {
                const coords =  new ol.proj.fromLonLat([pos.coords.longitude, pos.coords.latitude]);

                var start_point = coords;
                var end_point = coords;

                if (!trackline_start) {
                    tracklineSource.addFeatures([
                        new ol.Feature(new ol.geom.LineString([start_point, end_point]))
                    ]);
                    trackline_start = 1;
                } else {
                    let line = tracklineSource.getFeatures()[0].getGeometry();
                    line.appendCoordinate(coords);
                    map.getView().setCenter(coords);
                }

            }, function(error) {
                //alert(`ERROR: ${error.message}`);
                console.log(error.message);
            }, {
                enableHighAccuracy: true
            });
        }
    }
    trackWatch('on');

    var trackLocEnabled = true;
    var toggleLoc = document.querySelector("#trackloc");
    toggleLoc.addEventListener('click', function() {
        if (!trackLocEnabled) {
          trackWatch('on'); // Turn on location.Watch!
          trackLocEnabled = true;
          toggleLoc.title = "Location tracking is enabled";
          toggleLoc.style.color = "lightskyblue";
        } else {
          trackWatch('off'); // Turn off.
          trackLocEnabled = false;
          toggleLoc.title = "Location tracking is disabled";
          toggleLoc.style.color = "darkslategray";
        }
    }, false);

    /* User location */
    const locate = document.createElement('div');
    locate.className = 'ol-control ol-unselectable locate';
    locate.innerHTML = '<button title="Locate me"><i class="fa-solid fa-location-crosshairs"></button>';
    locate.addEventListener('click', function() {
        //if (!accuracySource.isEmpty()) {
        if (!geolocationLayer.getSource().isEmpty()) {
            //map.getView().fit(accuracySource.getExtent(), {
            map.getView().fit(geolocationLayer.getSource().getExtent(), {
                maxZoom: 18,
                duration: 500
            });
        }
    });
    map.addControl(new ol.control.Control({
        element: locate
    }));
    var currZoom = map.getView().getZoom();
    
    var clearF = function(e) {
        console.log('clear');
        wmsLayer.setVisible(true);
        clusters.setVisible(false);
        clusterSource.getSource().clear(true);
        drawSource.clear(true);
    }
    var filter = function(e) {

        var actualZoom = map.getView().getZoom();

        // Prevent fetching too much data..
        if (actualZoom<<?php echo $min_zoom_for_filter ?>) {
            return;
        }

        let polygonFeature;
        let extent = map.getView().calculateExtent(map.getSize());
        let polygon = new ol.geom.Polygon.fromExtent(extent);
        polygon.scale(0.85, 0.85)
        features = [new ol.Feature(polygon)];
        drawSource.addFeatures(features);
        polygonFeature = new ol.Feature(new ol.geom.Polygon(features[0].getGeometry().getCoordinates()));

        let format = new ol.format.WKT();
        let src = 'EPSG:3857';
        let dest = 'EPSG:4326';
        let wktRepresenation = format.writeGeometry(polygonFeature.getGeometry().clone().transform(src,dest));

        let myFeatures;

        // example answer for developing - avoiding cors error in localhost
        //
        //[{"obm_id":"1","uploader_name":"B\u00e1n Mikl\u00f3s","uploading_date":"2023-04-02 16:36:22.897174","uploading_id":"38134","hist_time":"","name":"botkert 1","obm_files_id":"","obm_geometry":"POINT(21.6214855 47.5581552)","observer":"banm@vocs.unideb.hu","q":"1"},{"obm_id":"2","uploader_name":"B\u00e1n Mikl\u00f3s","uploading_date":"2023-04-02 16:36:22.897174","uploading_id":"38134","hist_time":"","name":"botkert 2","obm_files_id":"","obm_geometry":"POINT(21.6221461 47.5587459)","observer":"banm@vocs.unideb.hu","q":"1"}]

        $.ajax({
            type: "GET",
            url: 'https://<?php echo URL ?>/index.php?query&qtable=pollimon_sample_plots&geom_selection=wktquery&geometry=' + wktRepresenation + '&output=json&filename=',
            dataType: 'json',
            //async: false,
            success: function (response) {
                let features = new Array;
                let plotnames = {};
                let usernames = {};
                for (let k=0;k<response.length;k++) {
                    let feature = new ol.format.WKT().readFeatures(response[k].obm_geometry,{
                        'dataProjection': "EPSG:4326",
                        'featureProjection': "EPSG:3857"});
                    feature[0].setProperties(response[k]);
                    plotnames[response[k].obm_id] = response[k].name;
                    usernames[response[k].obm_id] = response[k].observer;
                    features.push(feature[0]);
                }
                $('#site_name').find('option').remove();
                $.each(plotnames, function(key, value) {   
                     $('#site_name')
                         .append($("<option></option>")
                                    .attr("value", key)
                                    .text(value)); 
                });
                if (Object.keys(usernames).length==1) {
                    $('#observer').val(Object.values(usernames)[0]);
                }
                clusterSource.getSource().clear(true);
                clusters.setVisible(true);
                addClusterFeatures(features);
            }, 
            error: function () {
                // For localhost developers:
                let response = [{"obm_id":"1","uploader_name":"B\u00e1n Mikl\u00f3s","uploading_date":"2023-04-02 16:36:22.897174","name":"botkert 1","obm_files_id":"","obm_geometry":"POINT(21.6214855 47.5581552)","observer":"valalki"},{"obm_id":"2","uploader_name":"B\u00e1n Mikl\u00f3s","uploading_date":"2023-04-02 16:36:22.897174","uploading_id":"38134","name":"botkert 2","obm_files_id":"","obm_geometry":"POINT(21.6221461 47.5587459)","observer":"valalki"}];
                let features = new Array;
                let plotnames = {};
                let usernames = {};
                for (let k=0;k<response.length;k++) {
                    let feature = new ol.format.WKT().readFeatures(response[k].obm_geometry,{
                        'dataProjection': "EPSG:4326",
                        'featureProjection': "EPSG:3857"});
                    feature[0].setProperties(response[k]);
                    plotnames[response[k].obm_id] = response[k].name;
                    usernames[response[k].obm_id] = response[k].observer;
                    features.push(feature[0]);
                }
                $('#site_name').find('option').remove();
                $.each(plotnames, function(key, value) {   
                     $('#site_name')
                         .append($("<option></option>")
                                    .attr("value", key)
                                    .text(value)); 
                });  
                if (Object.keys(usernames).length==2) {
                    $('#observer').val(Object.values(usernames)[0]);
                }
                clusterSource.getSource().clear(true);
                clusters.setVisible(true);
                addClusterFeatures(features);
                // Delete it at end of early development
            }
        });

    };
    function getCookie(cookieName) {
      let cookie = {};
      document.cookie.split(';').forEach(function(el) {
        let [key,value] = el.split('=');
        cookie[key.trim()] = value;
      })
      return cookie[cookieName];
    }
    function setCookie(cname, cvalue, exhours) {
        const d = new Date();
        d.setTime(d.getTime() + ((exhours+3)*60*60*1000));
        let expires = "expires="+ d.toUTCString();
        let cookie = {
            "expiry":d.setTime(d.getTime() + (exhours*60*60*1000)),
            "data":{}
        }
        cookie["data"][cname] = cvalue;
        document.cookie = cname + "=" + JSON.stringify(cookie) + ";" + expires + ";path=/";
    }
    function addClusterFeatures(myFeatures) {
        wmsLayer.setVisible(false);
        clusterSource.getSource().addFeatures(myFeatures);
        drawSource.clear(true);
    }
</script>
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
