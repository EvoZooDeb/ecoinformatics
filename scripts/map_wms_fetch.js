// Authenticated wms layer

// access_token hack !!!!
access_token = '';

if (access_token != '') {
    // Force refreshing SESSION for access_token
    $.ajax({
        type: "POST",
        url: 'https://' + URL + '/v2.4/pds.php',
        data: {
            access_token: access_token,
            scope: 'request_time',
            value: Math.round(d.getTime()/1000),
            table: target_table 
        },
        async: false,
        dataType: 'json',
        success: function (response) {
            //alert('Fetching WMS')
            wmsSource = new ol.source.ImageWMS({
                url: 'https://' + URL + '/private/proxy.php',
                    params: {
                        map:'PMAP',
                        LAYERS:wms_cluster, 
                        isBaseLayer:'false', 
                        visibility:'true', 
                        opacity:'1.0', 
                        format:'image/png', 
                        transparent:'true', 
                        numZoomLevels:'20',
                        CNAME:'layer_data_'+wms_cluster}, 
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

