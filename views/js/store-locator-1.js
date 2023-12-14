
            (function() {
                var map;
                var markers = [{"lat":50.349434,"lng":3.113153,"title":"Extreme Tennis"},{"lat":50.348092,"lng":3.529918,"title":"Extreme Tennis Valenciennes"},{"lat":50.5048982,"lng":2.689302,"title":"Extreme Tennis Sailly Labourse"}]; // Initialisez la variable markers avec vos données JSON

                // Fonction pour trouver le marqueur le plus proche
                function findClosestMarker(userLocation) {
                    var closestMarker = null;
                    var closestDistance = Number.MAX_VALUE;

                    markers.forEach(function(marker) {
                        var markerLocation = new google.maps.LatLng(marker.lat, marker.lng);
                        var distance = google.maps.geometry.spherical.computeDistanceBetween(userLocation, markerLocation);

                        if (distance < closestDistance) {
                            closestDistance = distance;
                            closestMarker = marker;
                        }
                    });

                    return closestMarker;
                }

                function initMap() {
                    map = new google.maps.Map(document.getElementById("everblock-storelocator"), {
                        center: { lat: 50.349434, lng: 3.113153 },
                        zoom: 13
                    });

                    markers.forEach(function(marker) {
                        new google.maps.Marker({
                            position: { lat: marker.lat, lng: marker.lng },
                            map: map,
                            title: marker.title
                        });
                    });

                    document.getElementById("everblock-storelocator").style.height = "500px";
                }

                function initAutocomplete() {
                    var autocomplete = new google.maps.places.Autocomplete(document.getElementById("store_search"));

                    autocomplete.addListener("place_changed", function() {
                        var place = autocomplete.getPlace();
                        // Vous pouvez accéder aux informations sur le lieu sélectionné ici
                        console.log(place);

                        if (place.geometry && place.geometry.location) {
                            var userLocation = place.geometry.location;

                            // Maintenant, recherchez le marqueur le plus proche
                            var closestMarker = findClosestMarker(userLocation);

                            if (closestMarker) {
                                // Définir la vue de la carte pour zoomer sur le marqueur le plus proche
                                map.panTo({ lat: closestMarker.lat, lng: closestMarker.lng });
                                map.setZoom(15); // Réglez le niveau de zoom souhaité
                            }
                        }
                    });

                }

                google.maps.event.addDomListener(window, "load", initAutocomplete);
                google.maps.event.addDomListener(window, "load", initMap);
            })();
        