var marker, map;

function initMap(lat_cord=0,lng_cord=0) {
	
  
  var map = new google.maps.Map(document.getElementById('map'), {
    zoom: 13,
    center: {lat: lat_cord, lng: lng_cord}
  });
  marker = new google.maps.Marker({
    map: map,
    draggable: true,
    animation: google.maps.Animation.DROP,
  });	  
	marker.setPosition({lat : lat_cord, lng : lng_cord});
	google.maps.event.addListener(marker, 'dragend', function(e){
		$("#map_cord").val(JSON.stringify(marker.getPosition()));
	});
	
        // Try HTML5 geolocation.
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
        var pos = {
            lat: position.coords.latitude,
            lng: position.coords.longitude
        };
		$("#map_cord").val(JSON.stringify(pos));
        map.setCenter(pos);
		marker.setPosition(pos);
        }, function() {
            handleLocationError(true, map.getCenter());
          });
        } else {
          // Browser doesn't support Geolocation
          handleLocationError(false, map.getCenter());
        }
}

function handleLocationError(browserHasGeolocation, pos) {
   alertify.message("لم يتم العثور على إحداثياتك. المرجو إعادة المحاولة");
}