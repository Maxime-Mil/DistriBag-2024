<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Map avec GPS</title>
	</head>
	<body>
		<header>
			<h1>Distri Baguette</h1>
		</header>
		<main>
			<p>Carte France (avec GPS)</p>
			<html>

<head>
          <!-- Nous chargeons les fichiers CDN de Leaflet. Le CSS AVANT le JS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <style type="text/css">
            #map{ /* la carte DOIT avoir une hauteur sinon elle n'apparaît pas */
                height:400px;
            }
        </style>
    <style>
        #map {
            height: 400px;
        }
    </style>
</head>
<body>
    <!-- Div pour afficher la carte -->
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        var map;

        // Fonction pour initialiser la carte avec des coordonnées par défaut (Paris)
        function initMap() {
            map = L.map('map').setView([48.8566, 2.3522], 13); // Coordonnées de Paris par défaut
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
        }

    </script>
		
    </main>
</body>
</html>