<?php
session_start();

// Détruire la session ou mettre à jour la variable de session pour l'administrateur
unset($_SESSION['admin']);
// Vous pouvez aussi détruire toute la session si vous le souhaitez
// session_destroy();

// Rediriger vers la page d'accueil ou une autre page pour les utilisateurs
header("Location: index.html");
exit;
?>
