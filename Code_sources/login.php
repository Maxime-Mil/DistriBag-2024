<?php
session_start();

// Données d'authentification stockées sur le site
$users = array(
    'root' => 'root',
    'admin' => 'admin',
    // Ajoutez autant d'utilisateurs que nécessaire
);

// Vérification de l'envoi du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérification des champs nom d'utilisateur et mot de passe
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Vérification des informations d'identification
        if (array_key_exists($username, $users) && $users[$username] == $password) {
            // Authentification réussie, rediriger l'utilisateur vers la page souhaitée
            $_SESSION['admin'] = true;
            header("Location: admin_index.php");
            exit;
        } else {
            // Identifiants incorrects, rediriger vers la page d'erreur de connexion
            header("Location: erreur_connexion.html");
            exit;
        }
    } else {
        // Les champs n'ont pas été envoyés, rediriger vers la page d'erreur de connexion
        header("Location: erreur_connexion.html");
        exit;
    }
}
?>
