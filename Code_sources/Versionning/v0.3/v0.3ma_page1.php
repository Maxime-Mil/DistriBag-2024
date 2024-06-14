<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map sans GPS</title>
    
     <!-- Chargement des fichiers CDN de Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" type="text/css" href="map.css">
</head>
<body>
    <header>
        <h1>Distri Baguette</h1>
    </header>
    
    <main>
        <p>Carte France (sans GPS) </p>
  <!-- Div pour afficher la carte -->
        <div id="map"></div>
        <!-- Div pour afficher les informations sur le marqueur -->
        <div id="infos">
            <h2>Informations sur le marqueur</h2>
            <table id="marqueur-info">
                <!-- Les informations sur le marqueur seront affichées ici -->
            </table>
        </div>
        <button onclick="window.location.href='index.html'">Accueil</button>

    <!-- Fichiers Javascript -->
     <!-- Script pour Leaflet -->
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script type="text/javascript">
        var map;
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
    if (popupContent !== "") {
        marker.bindPopup(popupContent).openPopup();
    } else {
       /* marker.unbindPopup(); */
    } 
}

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

            }
                 function afficherInfosMarqueur(id, latitude, longitude, lieu, nb_baguette, etat) {
    var table = document.getElementById("marqueur-info");
    table.innerHTML = ""; // Réinitialiser le contenu du tableau
    var tr = "<tr><td>État</td><td>" + etat + "</td></tr>";
    tr += "<tr><td>Lieu</td><td>" + lieu + "</td></tr>";
    tr += "<tr><td>Nombre de baguettes</td><td>" + nb_baguette + "</td></tr>";
    // Ajouter d'autres propriétés du marqueur si nécessaire
    table.innerHTML = tr;
    }
   
    initMap(); 
    
    </script>
      </main>
</body>
</html>
