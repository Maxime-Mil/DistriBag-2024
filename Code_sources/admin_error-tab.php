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

// Fonction pour valider la latitude
function isValidLatitude($latitude) {
    return is_numeric($latitude) && $latitude >= -90 && $latitude <= 90;
}

// Fonction pour valider la longitude
function isValidLongitude($longitude) {
    return is_numeric($longitude) && $longitude >= -180 && $longitude <= 180;
}

// Fonction pour vérifier si une coordonnée est dans l'eau
function isInWater($latitude, $longitude) {
    $url = "https://revgeocode.search.hereapi.com/v1/revgeocode?at=$latitude,$longitude&lang=fr-FR&apiKey=jLFltSkRMnn1lQ1NQ0lIFEys5oK5GPyX0LTkOaS7c5A";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    // Vérifier si la réponse contient des éléments
    return empty($data['items']);
}

// Fonction pour vérifier si le lieu est incorrect
function isIncorrectLieu($latitude, $longitude, $lieu) {
    $url = "https://revgeocode.search.hereapi.com/v1/revgeocode?at=$latitude,$longitude&lang=fr-FR&apiKey=jLFltSkRMnn1lQ1NQ0lIFEys5oK5GPyX0LTkOaS7c5A";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (!empty($data['items'])) {
        $retrievedLieu = $data['items'][0]['address']['label'];
        // Comparer le lieu récupéré avec le lieu stocké dans la base de données
        return stripos($retrievedLieu, $lieu) === false;
    }

    // Si aucune information n'est récupérée, retourner vrai (le lieu est incorrect)
    return true;
}

// Requête SQL pour récupérer les distributeurs
$sql = "SELECT id, latitude, longitude, lieu, etat FROM distributeur";
$result = $conn->query($sql);

// Initialiser un tableau pour stocker les distributeurs avec des coordonnées invalides ou dans l'eau
$invalid_distributors = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Exclure le distributeur avec l'ID 0
        if ($row['id'] == 0) {
            continue;
        }
        
        $isInvalidCoordinate = !isValidLatitude($row['latitude']) || !isValidLongitude($row['longitude']);
        $isWater = isInWater($row['latitude'], $row['longitude']);
        $isIncorrectPlace = !$isInvalidCoordinate && !$isWater && isIncorrectLieu($row['latitude'], $row['longitude'], $row['lieu']);

        if ($isInvalidCoordinate || $isWater || $isIncorrectPlace) {
            $row['errorType'] = [
                'coordinate' => $isInvalidCoordinate || $isWater,
                'place' => $isIncorrectPlace
            ];
            $invalid_distributors[] = $row;
        }
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
    <title>Admin - Error tab</title>
    <link rel="stylesheet" href="CSS/admin_styles.css">
    <link rel="stylesheet" href="CSS/admin_tab.css">
    <style>
        .error-coordinate {
            color: red;
        }
        .error-place {
            color: orange;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <a href="admin_index.php">
                <img src="Image/admin_DistriBagWebLogo.png" alt="Logo Distri Baguette Admin" class="logo">
            </a>
        </div>
    </header>
    <h1 style="text-align: center;">Tableau des erreurs base de données</h1>
    <?php if (count($invalid_distributors) > 0): ?>
        <table id="bdd-table">
            <tr>
                <th>ID</th>
                <th>Lieu</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Etat</th>
            </tr>
            <?php foreach ($invalid_distributors as $distributor): ?>
                <tr>
                    <td><?php echo htmlspecialchars($distributor['id']); ?></td>
                    <td class="<?php echo !$distributor['errorType']['coordinate'] && $distributor['errorType']['place'] ? 'error-place' : ''; ?>">
                        <?php echo htmlspecialchars($distributor['lieu']); ?>
                    </td>
                    <td class="<?php echo $distributor['errorType']['coordinate'] ? 'error-coordinate' : ''; ?>">
                        <?php echo htmlspecialchars($distributor['latitude']); ?>
                    </td>
                    <td class="<?php echo $distributor['errorType']['coordinate'] ? 'error-coordinate' : ''; ?>">
                        <?php echo htmlspecialchars($distributor['longitude']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($distributor['etat']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p style="font-size: 1.2em;">Pas d'erreur trouvé. Tout est opérationnel.</p>
    <?php endif; ?>
</body>
</html>
