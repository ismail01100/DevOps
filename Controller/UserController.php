<?php
require_once 'Model/User.php';
require_once 'Model/DatabaseConnection.php';
class UserController {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    public function login($email, $password) {
        try {
            // Validate inputs
            if (empty($email) || empty($password)) {
                $_SESSION['error'] = "Email and password are required";
                require 'View/user/login.php';
                return;
            }

            $stmt = $this->db->prepare("SELECT * FROM users WHERE Email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Use password_verify instead of md5
            if ($user && password_verify($password, $user['Password'])) {
                $_SESSION['user'] = $user;
                header('Location: index.php?controller=portefeuille&action=index');
                exit(); // Add exit after redirect
            } else {
                $_SESSION['error'] = "Invalid email or password";
                require 'View/user/login.php';
            }
        } catch(PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred during login";
            require 'View/user/login.php';
        }
    }

    public function register($data) {
        try {
            // Validate inputs
            if (empty($data['Email']) || empty($data['Password']) || empty($data['Fullname'])) {
                $_SESSION['error'] = "All fields are required";
                require 'View/user/register.php';
                return;
            }

            // Hash password properly
            $hashedPassword = password_hash($data['Password'], PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("INSERT INTO users (Fullname, Email, Password) VALUES (:fullname, :email, :password)");
            $stmt->execute([
                ':fullname' => $data['Fullname'],
                ':email' => $data['Email'],
                ':password' => $hashedPassword
            ]);
            
            $_SESSION['success'] = "Registration successful. Please login.";
            header('Location: index.php?controller=user&action=login');
            exit(); // Add exit after redirect
        } catch(PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred during registration";
            require 'View/user/register.php';
        }
    }

    public function logout() {
        unset($_SESSION['user']);
        header('Location: index.php?controller=user&action=login');
        exit();
    }
}
?>