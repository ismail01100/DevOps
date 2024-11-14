<?php
include_once 'Utilisateur.php';

class UtilisateurController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUserProfile() {
        // Assuming the user is already logged in and user ID is set in the session
        session_start();
        if (isset($_SESSION['user_id'])) {
            $user = new Utilisateur($this->conn);
            $user->id = $_SESSION['user_id'];
            return $user->getProfile();
        }
        return null;
    }
}
?>
