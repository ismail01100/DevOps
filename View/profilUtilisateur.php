<?php
include_once 'Database.php';
include_once 'Utilisateur.php';
include_once 'UtilisateurController.php';

$database = new Database();
$db = $database->getConnection();
$utilisateurController = new UtilisateurController($db);

$userDetails = $utilisateurController->getUserProfile();

if ($userDetails) {
    echo "<h2>User Profile</h2>";
    echo "Name: " . $userDetails['Nom'] . "<br>";
    echo "Email: " . $userDetails['Email'] . "<br>";
    echo "Salary: " . $userDetails['Salaire'] . " DH<br>";
} else {
    echo "No user profile found.";
}
?>
