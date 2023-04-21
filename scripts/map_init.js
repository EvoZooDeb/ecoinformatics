const bbox = [-180.0000, -85.0000, 180.0000, 85.0000];
let extent = ol.proj.transformExtent(bbox, 'EPSG:4326', 'EPSG:3857');
const projection = new ol.proj.Projection({
    code: 'EPSG:900913',
    extent: extent,
});

/* Cluster layer */
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
    function addClusterFeatures(myFeatures) {
        wmsLayer.setVisible(false);
        clusterSource.getSource().addFeatures(myFeatures);
        drawSource.clear(true);
    }

