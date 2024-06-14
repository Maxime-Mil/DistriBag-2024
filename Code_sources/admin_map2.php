<?php
session_start();

// Vérifier si l'utilisateur est authentifié en tant qu'administrateur
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    // Rediriger l'utilisateur vers une autre page s'il n'est pas un administrateur
    header("Location: login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map avec GPS</title>
        <!-- Chargement des fichiers CDN de Leaflet -->
	 <link rel="stylesheet" href="CSS/admin_styles.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" type="text/css" href="CSS/map.css">
   <link rel="stylesheet" type="text/css" href="CSS/tab.css">
</head>
<body>
    <header>
        <div class="container">
	<a href="admin_index.php">
	   <img src="Image/admin_DistriBagWebLogo.png" alt="Logo Distri Baguette Admin" class="logo">
	    </a>
	   </div>
    </header>
     <main>
        <p>Carte France</p>
        <!-- Div pour afficher la carte -->
        <div id="map"></div>
        <div>
    <label for="latitude">Latitude :</label>
    <input type="text" id="latitude" placeholder="Entrez la latitude">
    <label for="longitude">Longitude :</label>
    <input type="text" id="longitude" placeholder="Entrez la longitude">
<label for="zoom">Zoom :</label>
            <input type="range" id="zoom" min="0" max="18" value="5" oninput="updateZoomValue(this.value)">
            <span id="zoom-value">5</span>
  <br>
   <button onclick="updateMapView()">Valider</button>
</div>
        <!-- Div pour afficher les informations sur le marqueur -->
        <div id="infos">
            <h2>Informations sur le marqueur</h2>
            <table id="marqueur-info">
                <!-- Les informations sur le marqueur seront affichées ici -->
            </table>
        </div>
        

        <!-- Script pour Leaflet -->
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <script>
            var map;
            var marker; // Variable pour stocker le marqueur de la position actuelle
            var zoom = 5;
            var defaultCoordinates = [46.626623, 1.762310];
            // Fonction pour initialiser la carte avec des coordonnées par défaut et ajouter les marqueurs
            function initMap() {
    
                 map = L.map('map').setView(defaultCoordinates, zoom); // Coordonnées de Paris par défaut
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
  // Ecouteur d'événement zoom
map.on('zoomend', function() {
    var zoomLevel = map.getZoom();
    document.getElementById('zoom').value = zoomLevel;
    document.getElementById('zoom-value').innerText = zoomLevel;
});

              
// Fonction pour définir le contenu du popup en fonction de l'état de la machine
function setPopupContent(marker, etat) {
    var popupContent = "";
    switch (etat) {
        case "ON":
            popupContent = "Ouvert";
            break;
        case "OFF":
            popupContent = "Fermé";
            break;
        case "HS":
            popupContent = "Hors Service";
            break;
        default:
            popupContent = "État inconnu";
    }
    if (popupContent !== "Ouvert") {
        marker.bindPopup(popupContent).openPopup();
    } else {
       /* marker.unbindPopup(); */
    } 
}

// Ajout des marqueurs à partir du code PHP

<?php
    // Connexion à la base de données
    $conn = new mysqli("172.28.0.2", "root", "root", "distribaguette");

    // Vérifier la connexion
    if ($conn->connect_error) {
        die("Erreur de connexion �� la base de données : " . $conn->connect_error);
    }

// Définir l'encodage UTF-8 pour la connexion
if (!$conn->set_charset("utf8mb4")) {
    printf("Erreur lors du chargement du jeu de caractères utf8mb4 : %s\n", $conn->error);
    exit();
}

    // Requête SQL pour récupérer les marqueurs
    $sql = "SELECT d.id AS distributeur_id, d.latitude, d.longitude, d.lieu, s.nb_baguette, d.etat 
            FROM distributeur d 
            INNER JOIN stock s ON d.id = s.distributeur_id";
    $result = $conn->query($sql);

   // Récupération des résultats de la requête
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Convertir les valeurs d'état en français
        $etat_fr = "";
        switch ($row['etat']) {
            case "ON":
                $etat_fr = "Ouvert";
                break;
            case "OFF":
                $etat_fr = "Fermé";
                break;
            case "HS":
                $etat_fr = "Hors Service";
                break;
            default:
                $etat_fr = "Inconnu";
        }
        
        echo "var marker2 = L.marker([" . $row['latitude'] . ", " . $row['longitude'] . "]).addTo(map).on('click', function() {
            afficherInfosMarqueur(" . $row['distributeur_id'] . ", " . $row['latitude'] . ", " . $row['longitude'] . ", '" . $row['lieu'] . "', " . $row['nb_baguette'] . ", '" . $etat_fr . "');
        });\n";
            // Changer la couleur du marqueur en fonction de l'état de la machine
            $color_class = "";
            switch ($row['etat']) {
                case "ON":
                    $color_class = "huechange-290";
                    break;
                case "OFF":
                    $color_class = "huechange-150";
                    break;
                case "HS":
                    $color_class = "huechange-200";
                    break;
                default:
                    $color_class = "huechange-350";
            }
            echo "marker2._icon.classList.add('" . $color_class . "');\n";
            // Ajouter un popup au marqueur avec le contenu approprié
            echo "setPopupContent(marker2, '" . $row['etat'] . "');\n";
        }    
    } else {
        echo "Erreur";
    }

    // Fermeture de la connexion à la base de données
    $conn->close();
?>

                // Ajout du marqueur pour la position actuelle
                addCurrentLocationMarker();
               
}
          
            // Fonction pour ajouter un marqueur de la position actuelle
function addCurrentLocationMarker() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var latitude = position.coords.latitude;
            var longitude = position.coords.longitude;
            marker = L.marker([latitude, longitude]).addTo(map).bindPopup("Vous êtes ici").openPopup();
            map.setView([latitude, longitude], 13); // Définition du zoom à 13 après avoir ajouté le marqueur
            document.getElementById('zoom').value = 13; // Mettre à jour la valeur de l'élément de contrôle du zoom
            document.getElementById('zoom-value').innerText = 13; // Mettre à jour l'affichage de la valeur de zoom
        }, function(error) {
            // Gestion des erreurs de géolocalisation
            console.log("Erreur de géolocalisation:", error.message);
            // Utiliser la position géographique par défaut en cas d'erreur
            map.setView(defaultCoordinates, 5); // Utilisez le zoom par défaut
            document.getElementById('zoom').value = 5; // Mettre à jour la valeur de l'élément de contrôle du zoom
            document.getElementById('zoom-value').innerText = 5; // Mettre à jour l'affichage de la valeur de zoom
        });
    } else {
        // La géolocalisation n'est pas prise en charge par le navigateur
        console.log("La géolocalisation n'est pas prise en charge par votre navigateur.");
        // Utiliser la position géographique par défaut
        map.setView(defaultCoordinates, 5); // Utilisez le zoom par défaut
        document.getElementById('zoom').value = 5; // Mettre à jour la valeur de l'élément de contrôle du zoom
        document.getElementById('zoom-value').innerText = 5; // Mettre à jour l'affichage de la valeur de zoom
    }
}


          var i=0;
            // Fonction pour mettre à jour la position du marqueur en temps réel
        var updateCount = 0; // Variable pour suivre le nombre de mises à jour

function updateMarkerPosition(position) {
    var latitude = position.coords.latitude;
    var longitude = position.coords.longitude;
    marker.setLatLng([latitude, longitude]).update();
    marker._icon.classList.add("huechange-20");

    if (latitude === position.coords.latitude) {
        zoom = 13;
        map.setView([latitude, longitude], zoom); // Mise à jour du zoom de la carte
    }

    updateCount++; // Incrémenter le compteur
    if (updateCount >= 2) {
        navigator.geolocation.clearWatch(watchID); // Arrêter de surveiller la position après deux mises à jour
    }
}

// Fonction pour récupérer la position de l'utilisateur et mettre à jour le marqueur en temps réel
var watchID; // Variable pour stocker l'ID de watchPosition

function watchLocation() {
    watchID = navigator.geolocation.watchPosition(function(position) {
        updateMarkerPosition(position);
    });
}


        // Fonction pour afficher les informations sur le marqueur cliqué
function afficherInfosMarqueur(id, latitude, longitude, lieu, nb_baguette, etat) {
    var table = document.getElementById("marqueur-info");
    table.innerHTML = ""; // Réinitialiser le contenu du tableau
    var tr = "<tr><td>État</td><td>" + etat + "</td></tr>";
    tr += "<tr><td>Lieu</td><td>" + lieu + "</td></tr>";
    tr += "<tr><td>Nombre de baguettes</td><td>" + nb_baguette + "</td></tr>";
    // Ajouter d'autres propriétés du marqueur si nécessaire
    table.innerHTML = tr;
}

           // Fonction pour mettre à jour la vue de la carte avec les nouvelles coordonnées
            function updateMapView() {
                var lat = document.getElementById('latitude').value;
                var lng = document.getElementById('longitude').value;
                if (lat && lng) {
                    map.setView([lat, lng], map.getZoom());
                } else {
                    alert('Veuillez entrer des coordonnées valides.');
                }
            }
      

          function updateZoomValue(value) {
            document.getElementById('zoom-value').innerText = value;
            if (marker) {
                map.setView(marker.getLatLng(), parseInt(value)); // Mettre à jour le zoom en fonction de la valeur du range
            }else{
              document.getElementById('zoom-value').innerText = value;
                map.setZoom(parseInt(value));
            }
        }

            document.addEventListener('DOMContentLoaded', function() {
                initMap(); // Initialiser la carte
            });
        </script>

    </main>
</body>
</html>