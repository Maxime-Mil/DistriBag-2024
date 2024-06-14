<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map avec GPS</title>
    <!-- Chargement des fichiers CDN de Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" type="text/css" href="map.css">
    <link rel="stylesheet" type="text/css" href="mobile.css">

</head>
<body>
    <header>
        <h1>Distri Baguette</h1>
    </header>
    <main>
        <p>Carte France</p>
        <!-- Div pour afficher la carte -->
        <div id="map"></div>
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

        <button onclick="window.location.href='index.html'">Accueil</button>
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
$conn = new mysqli("localhost", "root", "root", "distribaguette");

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Requête SQL pour récupérer les marqueurs
$sql = "SELECT d.id AS distributeur_id, d.latitude, d.longitude, d.lieu, s.nb_baguette, d.etat 
        FROM distributeur d 
        INNER JOIN stock s ON d.id = s.id_distributeur";
$result = $conn->query($sql);

// Récupération des résultats de la requête
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Convertir les valeurs d'état en français
        switch ($row['etat']) {
            case "ON":
                $etat_fr = "Ouvert";
                $color_class = "huechange-290";
                break;
            case "OFF":
                $etat_fr = "Fermé";
                $color_class = "huechange-150";
                break;
            case "HS":
                $etat_fr = "Hors Service";
                $color_class = "huechange-200";
                break;
            default:
                $etat_fr = "Inconnu";
                $color_class = "huechange-350";
        }
        // Ajouter les informations du marqueur à la liste des marqueurs en JavaScript
        echo "var marker = L.marker([" . $row['latitude'] . ", " . $row['longitude'] . "]).addTo(map).on('click', function() {
            afficherInfosMarqueur(" . $row['distributeur_id'] . ", " . $row['latitude'] . ", " . $row['longitude'] . ", '" . $row['lieu'] . "', " . $row['nb_baguette'] . ", '" . $etat_fr . "');
        });\n";
        // Ajouter la classe de couleur au marqueur
        echo "marker._icon.classList.add('" . $color_class . "');\n";
    }
} else {
    echo "Erreur";
}

// Fermeture de la connexion à la base de données
$conn->close();
?>
               
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

            // Appeler la fonction pour obtenir la position de l'utilisateur lorsque la page est chargée
            document.addEventListener('DOMContentLoaded', function() {
                initMap(); // Initialiser la carte
            });
        </script>

    </main>
</body>
</html>
