mapboxgl.accessToken = 'pk.eyJ1IjoibWdvbWV6YnVjZXRhNzkiLCJhIjoiY2praTNrbWlvMHkzcDNxcGJwbW51NTJhdCJ9.Zg6JVTwxyOz6O3SZVtMzww';
var map = new mapboxgl.Map({
	container: 'map',
	style: 'mapbox://styles/mapbox/light-v10',
	center: [-8.676936205721324,42.85206928425398], // starting position
	zoom: 13 // starting zoom
});

// create the popup
var popup = new mapboxgl.Popup({ offset: 40 }).setText(
	'Brión, A Coruña, Galicia'
);

// create DOM element for the marker
var el = document.createElement('div');
el.id = 'marker';

// create the marker
new mapboxgl.Marker(el)
	.setLngLat([-8.676936205721324,42.85206928425398])
	.setPopup(popup) // sets a popup on this marker
	.addTo(map);

// Add zoom and rotation controls to the map.
map.addControl(new mapboxgl.NavigationControl(), 'bottom-right');