<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map avec GPS</title>
    
       <!-- Chargement des fichiers CDN de Leaflet -->
    <link rel="stylesheet" type="text/css" href="CSS/styles.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" type="text/css" href="CSS/map.css">
    <link rel="stylesheet" type="text/css" href="CSS/mobile.css">

</head>
<body>
    <header>
        <div class="container">
	<a href="index.html">
	   <img src="Image/DistriBagWebLogo.png" alt="Logo Distri Baguette" class="logo">
	    </a>
	   </div>
    </header>
    <main>
        <p>Carte France</p>
        <!-- Div pour afficher la carte -->
        <div id="map"></div>
      <div>
        <label for="zoom">Zoom :</label>
        <input type="range" id="zoom" min="0" max="18" value="5" oninput="updateZoomValue(this.value)">
        <span id="zoom-value">5</span>
    </div>
        <!-- Div pour afficher les informations sur le marqueur -->
       <!-- Le pop-up modal -->
    <div id="modal" class="modal">  
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Informations du distributeur</h2>
            <table id="modal-table">
            <!-- Les informations sur le marqueur seront affichées ici -->
            <!-- Boutons pour naviguer entre les points -->
            <!-- <button id="prevBtn" onclick="prevMarker()">Précédent</button>
            <button id="nextBtn" onclick="nextMarker()">Suivant</button> -->
            </table>
        </div>
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

var currentMarkerIndex = 0;
var markers = [];

// Ajout des marqueurs à partir du code PHP
<?php
    // Connexion à la base de données
    $conn = new mysqli("172.28.0.2", "root", "root", "distribaguette");

    // Vérifier la connexion
    if ($conn->connect_error) {
        die("Erreur de connexion à la base de données : " . $conn->connect_error);
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
            afficherInfosMarqueur(" . $row['distributeur_id'] . ", " . $row['latitude'] . ", " . $row['longitude'] . ", '" . htmlspecialchars($row['lieu'], ENT_QUOTES, 'UTF-8') . "', " . $row['nb_baguette'] . ", '" . $etat_fr . "');
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

addCurrentLocationMarker();
                         
}


// Fonction pour ajouter un marqueur de la position actuelle
function addCurrentLocationMarker() {
    navigator.geolocation.getCurrentPosition(function(position) {
        var latitude = position.coords.latitude;
        var longitude = position.coords.longitude;
        marker = L.marker([latitude, longitude]).addTo(map).bindPopup("Vous êtes ici").openPopup();
        map.setView([latitude, longitude], 13); // Définition du zoom à 13 après avoir ajouté le marqueur
        document.getElementById('zoom').value = 13; // Mettre à jour la valeur de l'élément de contrôle du zoom
        document.getElementById('zoom-value').innerText = 13; // Mettre à jour l'affichage de la valeur de zoom
    });
}

 // Fonction pour mettre à jour la position du marqueur en temps réel
 function updateMarkerPosition(position) {
              var latitude = position.coords.latitude;
              var longitude = position.coords.longitude;
              marker.setLatLng([latitude, longitude]).update();
              marker._icon.classList.add("huechange-20");
              if (latitude = position.coords.latitude) 
              {
                  zoom = 13;
                  map.setView([latitude, longitude], 13); // Mise à jour du zoom de la carte
              }

          }

          

// Ouvrir le pop-up modal
function openModal() {
  var modal = document.getElementById("modal");
  modal.style.display = "block";
}

// Fermer le pop-up modal
function closeModal() {
  var modal = document.getElementById("modal");
  modal.style.display = "none";
}

window.addEventListener('click', function(event) {
    var modal = document.getElementById("modal");
    if (event.target == modal) {
        closeModal();
    }
});

function displayMarkerInfo() {
    var markerInfo = markers[currentMarkerIndex];
    // Affichez les informations du marqueur sélectionné
    afficherInfosMarqueur(markerInfo.lieu, markerInfo.etat, markerInfo.nb_baguette);
}

            
// Fonction pour afficher les informations sur le marqueur cliqué dans le pop-up modal
function afficherInfosMarqueur(id, latitude, longitude, lieu, nb_baguette, etat) {
    // Remplir le contenu du tableau dans le pop-up modal
    var table = document.getElementById("modal-table");
    table.innerHTML = ""; // Réinitialiser le contenu du tableau
    var tr = "<tr><td>État</td><td>" + etat + "</td></tr>";
    tr += "<tr><td>Lieu</td><td>" + lieu + "</td></tr>";
    tr += "<tr><td>Nombre de baguettes</td><td>" + nb_baguette + "</td></tr>";
    table.innerHTML = tr;

    // Ouvrir le pop-up modal
    openModal();
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

            // Appeler la fonction pour obtenir la position de l'utilisateur lorsque la page est chargée
            document.addEventListener('DOMContentLoaded', function() {
                initMap(); // Initialiser la carte
                watchLocation(); // Mettre à jour la position du marqueur en temps réel
            });
        </script>

    </main>
</body>
</html>
