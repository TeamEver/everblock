
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <script>
            var mapContainer = document.getElementById("everblock-storelocator");

            // Extraire les coordonnées du premier marqueur
            var firstMarker = {"lat":"25.73629600","lng":"-80.24479700","title":"Coconut Grove"};
            var initialLat = firstMarker.lat;
            var initialLng = firstMarker.lng;

            var map = L.map(mapContainer).setView([initialLat, initialLng], 13);
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(map);
            
            var markers = [{"lat":"25.73629600","lng":"-80.24479700","title":"Coconut Grove"},{"lat":"25.76500500","lng":"-80.24379700","title":"Dade County"},{"lat":"26.13793600","lng":"-80.13943500","title":"E Fort Lauderdale"},{"lat":"25.88674000","lng":"-80.16329200","title":"N Miami\/Biscayne"},{"lat":"26.00998700","lng":"-80.29447200","title":"Pembroke Pines"}];
            
            markers.forEach(function(marker) {
                L.marker([marker.lat, marker.lng]).addTo(map)
                    .bindPopup(marker.title);
            });
            
            // Ajustez la hauteur du conteneur de la carte ici
            mapContainer.style.height = "500px"; // Par exemple, réglez la hauteur à 500 pixels
        </script>