<?php
	require_once('config.inc.php');
	global $config;

	$json = isset($_GET['json']) ? $_GET['json'] : '';

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>DracoBlue's SAMP DMap 0.4</title>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?=$config['api key']?>" type="text/javascript"></script>
    <script type="text/javascript">

      // EuclideanProjection taken from: http://econym.googlepages.com/example_custommapflat.htm

      // ====== Create the Euclidean Projection for the flat map ======
      // == Constructor ==
      function EuclideanProjection(a){
        this.pixelsPerLonDegree=[];
        this.pixelsPerLonRadian=[];
        this.pixelOrigo=[];
        this.tileBounds=[];
        var b=256;
        var c=1;
        for(var d=0;d<a;d++){
          var e=b/2;
          this.pixelsPerLonDegree.push(b/360);
          this.pixelsPerLonRadian.push(b/(2*Math.PI));
          this.pixelOrigo.push(new GPoint(e,e));
          this.tileBounds.push(c);
          b*=2;
          c*=2
        }
      }
 
      // == Attach it to the GProjection() class ==
      EuclideanProjection.prototype=new GProjection();
 
 
      // == A method for converting latitudes and longitudes to pixel coordinates == 
      EuclideanProjection.prototype.fromLatLngToPixel=function(a,b){
        var c=Math.round(this.pixelOrigo[b].x+a.lng()*this.pixelsPerLonDegree[b]);
        var d=Math.round(this.pixelOrigo[b].y+(-2*a.lat())*this.pixelsPerLonDegree[b]);
        return new GPoint(c,d)
      };

      // == a method for converting pixel coordinates to latitudes and longitudes ==
      EuclideanProjection.prototype.fromPixelToLatLng=function(a,b,c){
        var d=(a.x-this.pixelOrigo[b].x)/this.pixelsPerLonDegree[b];
        var e=-0.5*(a.y-this.pixelOrigo[b].y)/this.pixelsPerLonDegree[b];
        return new GLatLng(e,d,c)
      };

      // == a method that checks if the y value is in range, and wraps the x value ==
      EuclideanProjection.prototype.tileCheckRange=function(a,b,c){
        var d=this.tileBounds[b];
        if (a.y<0 || a.y>=d || a.x<0 || a.x>=d) { // By DracoBlue: added this, to avoid repeatition 
          return false;
        }
        return true
      }

      // == a method that returns the width of the tilespace ==      
      EuclideanProjection.prototype.getWrapWidth=function(zoom) {
        return this.tileBounds[zoom]*256;
      }
	  
	  
	// Here comes the dmap specific stuff:

	var gtasaIcons = {};
	var markers = [];
	var markersText = [];
	var map = null;
	var update_c = 1;
	
	function fetchData() {
		GDownloadUrl("fetch_data.php?uc="+update_c+"&json=", function(data) {
			update_c++;
			data = eval("("+data+")");
			if (typeof data.items !== "undefined") {
				for (id in data.items) {
					var item = data.items[id];
					var point = new GLatLng(((parseFloat(item.pos.y)*90)/3000),
								((parseFloat(item.pos.x)*90)/1500));
					if (typeof markers[item.id] === "undefined" || markers[item.id] === null) {
						markers[item.id] = {}
						var marker = createMapMarker(point, item.id, item.text, parseInt(item.icon));
						markers[item.id].marker = marker;
						markers[item.id].id = item.id;
						markersText[item.id] = "<b>" + item.name + "</b> <br/>" + item.text;
						map.addOverlay(markers[item.id].marker);
					} else {
						// already exists: update!
						markersText[item.id] = "<b>" + item.name + "</b> <br/>" + item.text;
						markers[item.id].marker.setLatLng(point);
					}
					markers[item.id].update_c = update_c;
				}
			}
			// remove the old ones
			for (i in markers) {
				if (markers[i].update_c != update_c) {
					map.removeOverlay(markers[i].marker);
					markers[i] = null;
					markersText[id] = null;
				}
			}
		});
	}

    function load() {
		document.getElementById('map').style.width="512px";
		document.getElementById('map').style.height="512px";
		

		if (GBrowserIsCompatible()) {
			map = new GMap2(document.getElementById("map"));

			var copyright = new GCopyright(1, new GLatLngBounds(new GLatLng(-180, -180), new GLatLng(180, 180)), 0, '<a style="color:#ffffff;text-decoration:none" href="http://dracoblue.net">&copy; 2008-2009 by DracoBlue</a>');
			var copyrights = new GCopyrightCollection('');
			copyrights.addCopyright(copyright);
			var tilelayer = new GTileLayer(copyrights, 1, 4);
			tilelayer.getTileUrl = function(tile, zoom) { 
			return 'map/'+tile.x+'x'+tile.y+'-'+(6-zoom)+".jpg"; };
			var CUSTOM_MAP = new GMapType( [tilelayer], new EuclideanProjection(20), "DracoBlue" );
			map.addMapType(CUSTOM_MAP);
			map.setMapType(CUSTOM_MAP);
			map.addControl(new GSmallMapControl());
			map.enableScrollWheelZoom();
			map.setCenter(new GLatLng(0, 0), 1);
			fetchData();
			setInterval("fetchData();",5000);
		}
    }

    function createMapMarker(point, id, text, type) {
	
	if (typeof gtasaIcons[type] === "undefined") {
		var icon = new GIcon(); 
		icon.image = 'icons/Icon_'+type+'.gif';
		icon.iconSize = new GSize(20, 20);
		icon.iconAnchor = new GPoint(10, 10);
		// If I would HAVE this shado icons, I would add them :-P
		// icon.shadowSize = new GSize(22, 20);
		// icon.shadow = 'icons/Icon_'+type+'.gif';
		icon.infoWindowAnchor = new GPoint(1, 1);
		gtasaIcons[type] = icon;
	}
	
      var marker = new GMarker(point, gtasaIcons[type]);
      GEvent.addListener(marker, 'click', function() {
        marker.openInfoWindowHtml(markersText[id]);
      });
      return marker;
    }

    //]]>
    </script>
  </head>

  <body onload="load()" onunload="GUnload()">
    <div id="map" style="width: 512px; height: 512px"></div>
  </body>
</html>