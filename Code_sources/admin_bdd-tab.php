<?php
session_start();

// Vérifier si l'utilisateur est authentifié en tant qu'administrateur
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    // Rediriger l'utilisateur vers une autre page s'il n'est pas un administrateur
    header("Location: login.html");
    exit;
}
?>


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

// Requête SQL pour récupérer tous les distributeurs
$sql = "SELECT id, latitude, longitude, lieu, etat FROM distributeur";
$result = $conn->query($sql);

// Initialiser un tableau pour stocker tous les distributeurs
$distributors = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $distributors[] = $row;
    }
} else {
    echo "Aucun distributeur trouvé.";
}

// Fermeture de la connexion à la base de données
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Liste des distributeurs</title>
    <link rel="stylesheet" href="CSS/admin_styles.css">
    <link rel="stylesheet" href="CSS/admin_tab.css">
</head>
<body>
    <header>
        <div class="container">
            <a href="admin_index.php">
                <img src="Image/admin_DistriBagWebLogo.png" alt="Logo Distri Baguette Admin" class="logo">
            </a>
        </div>
    </header>
    <h1 class="center-text">Liste des distributeurs</h1>
    <?php if (count($distributors) > 0): ?>
        <table id="bdd-table">
            <tr>
                <th>ID</th>
                <th>Lieu</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>État</th>
            </tr>
            <?php foreach ($distributors as $distributor): ?>
                <tr>
                    <td><?php echo htmlspecialchars($distributor['id']); ?></td>
                    <td><?php echo htmlspecialchars($distributor['lieu']); ?></td>
                    <td><?php echo htmlspecialchars($distributor['latitude']); ?></td>
                    <td><?php echo htmlspecialchars($distributor['longitude']); ?></td>
                    <td><?php echo htmlspecialchars($distributor['etat']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p class="no-data-message">Aucun distributeur trouvé.</p>
    <?php endif; ?>
</body>
</html>