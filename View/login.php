<?php
include_once 'Database.php';
include_once 'Utilisateur.php';

$database = new Database();
$db = $database->getConnection();

if ($_POST) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $utilisateur = new Utilisateur($db);
    if ($utilisateur->login($email, $password)) {
        echo "Login successful. Welcome " . $utilisateur->nom;
        // Redirect to profile or portfolio page
    } else {
        echo "Login failed. Please check your email or password.";
    }
}
?>

<form method="post">
    Email: <input type="text" name="email"><br>
    Password: <input type="password" name="password"><br>
    <input type="submit" value="Login">
</form>
