<!DOCTYPE html>
<html>
<head>
    <title>Affichage de données avec PHP</title>
</head>
<body>

<h1>Donnees de la base de donnees</h1>

<?php
// Paramètres de connexion à la base de données
$db_host = "bdd_distribag";
$db_user = "root";
$db_password = "root";
$db_name = "bdd_distribag";


// Connexion à la base de données
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Requête SQL pour récupérer des données
$sql = "SELECT * FROM stock";
$result = $conn->query($sql);
$resultCheck = mysqli_num_rows($result);

if ($result->num_rows > 0) {
    // Affichage des données dans un tableau
    echo "<table border='1'>";
    echo "<tr><th>Nom</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["quantité"] . "</td><td>";
    }
    echo "</table>";
} else {
    echo "Aucune donnée trouvée dans la base de données.";
}

// Fermeture de la connexion à la base de données
$conn->close();
?>

</body>
</html>
