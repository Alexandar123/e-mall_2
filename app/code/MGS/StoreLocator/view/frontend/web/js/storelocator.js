/**
 * Locate store on gogle map
 * 
 * @category    design
 * @package     base_default
 * @author      MGS Magento Team
 */

/**
 * Return parameters for back url
 *
 * @param  storeLat latitude
 * @param  storeLong logitude
 * @param  storeRadius store radius
 * @param  storeZoomLevel google map zoom level
 * @param  storeInfoText display infobox on google map
 * @return null
 */
function initializeMap(storeLat, storeLong, storeRadius, storeInfoText, googleMapDivId)
{
	storeInfoText = storeInfoText.replace(/-quotation-/g, '"');
    var myCenter = new google.maps.LatLng(storeLat, storeLong);
    //convert distance from miles to meters
    var storeRadius = storeRadius * 1609.34;
    var mapProp = {
      center : myCenter,
      zoom : 14,
      mapTypeId : google.maps.MapTypeId.ROADMAP  
      };

    var map = new google.maps.Map(document.getElementById(googleMapDivId), mapProp);

    //Draw marker
    var marker = new google.maps.Marker({
      position : myCenter,
      });
    marker.setMap(map);

    //Draw circle radius is in meter
    if(storeRadius) {
        var myCity = new google.maps.Circle({
            center : myCenter,
            radius : storeRadius,
            strokeOpacity : 0.8,
            strokeWeight : 1,
            fillOpacity : 0
        });
        myCity.setMap(map);
    }
    
    // Open information window
    if(storeInfoText) {
        var infowindow = new google.maps.InfoWindow({
            content:storeInfoText
        });
    infowindow.open(map, marker);
    }
}

/**
 * add multiple stores on google map
 *
 * @param array markers markers
 * @param string googleMapDivId div id
 */
function drawMap(markers, googleMapDivId)
{
    var map;
    var bounds = new google.maps.LatLngBounds();
    var mapOptions = {
        mapTypeId: 'roadmap'
    };
                    
    // Display a map on the page
    map = new google.maps.Map(document.getElementById(''+googleMapDivId), mapOptions);
    map.setTilt(45);
                        
    // Display multiple markers on a map
    var infoWindow = new google.maps.InfoWindow(), marker, i;
    
    // Loop through our array of markers & place each one on the map  
    for( i = 0; i < markers.length; i++ ) {
        var position = new google.maps.LatLng(markers[i][1], markers[i][2]);
        bounds.extend(position);
        marker = new google.maps.Marker({
            position: position,
            map: map,
            title: markers[i][0]
        });
        
        // Allow each marker to have an info window    
        google.maps.event.addListener(marker, 'click', (function(marker, i) {
            return function() {
                infoWindow.setContent(markers[i][0]);
                infoWindow.open(map, marker);
            }
        })(marker, i));

        // Automatically center the map fitting all markers on the screen
        map.fitBounds(bounds);
    }
	
}



function drawMapWithCircle(markers, googleMapDivId, lat, longa, radius, image)
{
	var bounds = new google.maps.LatLngBounds();
	var mapOptions = {
		center: new google.maps.LatLng(lat, longa)
	  };

	var map = new google.maps.Map(document.getElementById(googleMapDivId), mapOptions);
	var infoWindow = new google.maps.InfoWindow(), marker, i;
	
	for( i = 0; i < markers.length; i++ ) {
        var position = new google.maps.LatLng(markers[i][1], markers[i][2]);
        bounds.extend(position);
        marker = new google.maps.Marker({
            position: position,
            map: map,
            title: markers[i][0]
        });
        
        // Allow each marker to have an info window    
        google.maps.event.addListener(marker, 'click', (function(marker, i) {
            return function() {
                infoWindow.setContent(markers[i][0]);
                infoWindow.open(map, marker);
            }
        })(marker, i));

        // Automatically center the map fitting all markers on the screen
    }
	
	var marker = new google.maps.Marker({
	  position: new google.maps.LatLng(lat, longa),
	  map: map,
	  icon: image
	});
	
	var circ = new google.maps.Circle();
    circ.setRadius(radius * 1609.0);
    circ.setCenter(new google.maps.LatLng(lat, longa));
    map.setCenter(new google.maps.LatLng(lat, longa));
	
    map.fitBounds(circ.getBounds());

    // updates markers
    google.maps.event.trigger(map,'dragend');
	
	var citymap = {};
	citymap['chicago'] = {
	  center: new google.maps.LatLng(lat, longa)
	};

	var cityCircle;
	
	for (var city in citymap) {
		var populationOptions = {
			strokeColor: '#FF0000',
			strokeOpacity: 0.8,
			strokeWeight: 1,
			fillColor: '#FF0000',
			fillOpacity: 0.1,
			map: map,
			center: citymap[city].center,
			radius: radius * 1609.0
		};
		// Add the circle for this city to the map.
		cityCircle = new google.maps.Circle(populationOptions);
	}

}