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
    <title>Admin - Page d'accueil</title>
    <link rel="stylesheet" href="CSS/admin_styles.css">
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
  <p> Bienvenue sur le site Distri Baguettes Admin !
        <p>Vérifier la position et l'état des distributeurs de baguette de pain de France en temps réel.
        <br> Acceptez-vous qu'on accède à votre localisation ? (nécessite l'activation du gps) </p>
       
        <button id="pageButton">Sans GPS</button>

<script>
    // Fonction pour détecter le type d'appareil
    function detectDevice() {
        // Vérifier si l'appareil est un téléphone ou une tablette
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            // Rediriger vers la version mobile de la page
            window.location.href = 'mobile-admin_map1.php';
        } else {
            // Rediriger vers la version standard de la page
            window.location.href = 'admin_map1.php';
        }
    }

    // Ajouter un gestionnaire d'événement au clic sur le bouton
    document.getElementById('pageButton').addEventListener('click', detectDevice);
</script>

 
<p id="demo"></p>
<button id="gpsButton">Avec GPS</button>
<br>

<script>
    const x = document.getElementById("demo");
    const gpsButton = document.getElementById("gpsButton");

    // Fonction pour détecter le type d'appareil
    function detectDeviceAndRedirect() {
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            // Rediriger vers la version mobile de la page
            getLocation();
        } else {
            // Rediriger vers la version standard de la page
            getLocation(); // Appeler la fonction pour obtenir la position avec GPS
        }
    }

    // Ajouter un gestionnaire d'événement au clic sur le bouton
    gpsButton.addEventListener('click', detectDeviceAndRedirect);

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(mapPosition, showError);
        } else {
            x.innerHTML = "La Géolocalisation n'est pas supportée par votre navigateur. <br> Veuillez relancer depuis un navigateur plus récent ou rentrer manuellement votre adresse.";
        }
    }

    function mapPosition(position) {
        window.location.href = 'admin_map2.php';
    }

  function showError(error) {
    switch(error.code) {
      case error.PERMISSION_DENIED:
        x.innerHTML = "Accès à la géolocalisation refusée. Veuillez charger la map sans gps.";
        break;
      case error.POSITION_UNAVAILABLE:
        x.innerHTML = "Les cordonnées de localisation ne sont pas disponible. Veuillez charger la map sans gps.";
        break;
      case error.UNKNOWN_ERROR:
        x.innerHTML = "Une erreur inconnue s'est produite. Essayer de charger la map sans gps. Sinon revenez plus tard";
        break;
    }
  }
</script>

<br><br>
<button onclick="window.location.href='admin_bdd-tab.php'">Tableau BDD</button> 
<br><br>
<button onclick="window.location.href='admin_error-tab.php'">Tableau d'erreur BDD</button> 
<br><br><br><br>
<button onclick="window.location.href='logout.php'">Utilisateur</button>

      <!-- Pied de page -->
        <footer>
            <a href="contact.html">Contact</a>
            <a href="a_propos.html">À propos</a>
            <!-- Ajoutez d'autres liens si nécessaire -->
        </footer>
    </main>
</body>
</html>
