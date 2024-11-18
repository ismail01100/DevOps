<?php
require_once 'Model/User.php';
require_once 'Model/DatabaseConnection.php';
class UserController {
    private $db;
    private $isTestMode;
    
    public function __construct($isTestMode = false) {
        $this->db = DatabaseConnection::getInstance()->getConnection();
        $this->isTestMode = $isTestMode;
    }

    public function login($email, $password) {
        try {
            // Validate inputs
            if (empty($email) || empty($password)) {
                $_SESSION['error'] = "Email and password are required";
                if (!$this->isTestMode) {
                    require 'View/user/login.php';
                }
                return;
            }

            $stmt = $this->db->prepare("SELECT * FROM users WHERE Email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['Password'])) {
                $_SESSION['user'] = $user;
                if (!$this->isTestMode) {
                    header('Location: index.php?controller=portefeuille&action=index');
                    exit();
                }
                return true;
            } else {
                $_SESSION['error'] = "Invalid email or password";
                if (!$this->isTestMode) {
                    require 'View/user/login.php';
                }
                return false;
            }
        } catch(PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred during login";
            if (!$this->isTestMode) {
                require 'View/user/login.php';
            }
            return false;
        }
    }

    public function register($data) {
        try {
            // Validate inputs
            if (empty($data['Email']) || empty($data['Password']) || empty($data['Fullname'])) {
                $_SESSION['error'] = "All fields are required";
                if (!$this->isTestMode) {
                    require 'View/user/register.php';
                }
                return false;
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
            if (!$this->isTestMode) {
                header('Location: index.php?controller=user&action=login');
                exit();
            }
            return true;
        } catch(PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred during registration";
            if (!$this->isTestMode) {
                require 'View/user/register.php';
            }
            return false;
        }
    }

    public function logout() {
        unset($_SESSION['user']);
        header('Location: index.php?controller=user&action=login');
        exit();
    }
}
?>