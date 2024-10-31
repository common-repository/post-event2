google.load("maps", "2",{"other_params":"sensor=true"});
		 var map = null;
		 var geocoder = null;

			function	reload_map()
			{
				var reload_value = jQuery('#EventPlace').val();
				if (reload_value != '')
				{
					document.getElementById('entry').style.display = 'block';
					showAddress(reload_value);
				}
				else
				{
					document.getElementById('entry').style.display = 'none';
				}
			}
		 function initialize() {
	            map = new google.maps.Map2(document.getElementById("map"));
	             map.setCenter(new google.maps.LatLng(48.857775, 2.211407), 13);
	             geocoder = new GClientGeocoder();
	             map.addControl(new GSmallMapControl());
	             var place = jQuery('#EventPlace').val();
	             if (place != '')
	             	showAddress(place);
	             }
	     		function showAddress(address) {
	    			if (geocoder) {
	    				geocoder.getLatLng(
	    					address,
	    					function(point) {
	    						if (!point) {
	    							alert(address + " not found");
	    						}
	    						else {
	    							map.setCenter(point, 13);
	    							var marker = new GMarker(point);
	    							map.addOverlay(marker);
	    							marker.openInfoWindowHtml('<div style="width:20px;height:5px;"><strong>Map</strong><br /><a href="http://maps.google.fr/maps?f=q&hl=fr&q='+address+'" target="_blank" style="color:blue;">'+jQuery('#EventPlace').val()+'</div>');
	    						}
	    					}
	    				);
	    			}
	    		}
	     		var value = jQuery('#EventPlace').val();
	    		if (value != '')
		    		document.getElementById('entry').style.display = 'block';
	    		google.setOnLoadCallback(initialize);