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
        if (actualZoom < min_zoom_for_filter) {
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
            url: 'https://' + URL + '/index.php?query&qtable=pollimon_sample_plots&geom_selection=wktquery&geometry=' + wktRepresenation + '&output=json&filename=',
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
                let response = [
                    {"obm_id":"1","uploader_name":"B\u00e1n Mikl\u00f3s","uploading_date":"2023-04-02 16:36:22.897174","name":"botkert 1","obm_files_id":"","obm_geometry":"POINT(21.6214855 47.5581552)","observer":"valaki"},
                    {"obm_id":"2","uploader_name":"B\u00e1n Mikl\u00f3s","uploading_date":"2023-04-02 16:36:22.897174","uploading_id":"38134","name":"botkert 2","obm_files_id":"","obm_geometry":"POINT(21.6221461 47.5587459)","observer":"valaki"}];
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


