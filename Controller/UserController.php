<?php
require_once 'Model/User.php';
require_once 'Model/DatabaseConnection.php';

class UserController
{
    private $db;
    private $isTestMode;

    public function __construct($isTestMode = false)
    {
        $this->db = DatabaseConnection::getInstance()->getConnection();
        $this->isTestMode = $isTestMode;
    }

    private function validateEmail($email)
    {
        // Stricter email validation
        if (empty($email)) {
            return false;
        }

        // Check for multiple @ symbols and spaces
        if (substr_count($email, '@') !== 1 || str_contains($email, ' ')) {
            return false;
        }

        // Split email into local and domain parts
        list($local, $domain) = explode('@', $email);

        // Check local and domain part lengths
        if (strlen($local) > 64 || strlen($domain) > 255) {
            return false;
        }

        // Check for consecutive dots and starting/ending dots
        if (str_contains($email, '..') || $email[0] === '.' || substr($email, -1) === '.') {
            return false;
        }

        // Validate domain has at least one dot and proper format
        if (!str_contains($domain, '.') || substr($domain, -1) === '.') {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validatePassword($password)
    {
        if (strlen($password) < 8) {
            $_SESSION['error'] = "Password must be at least 8 characters";
            return false;
        }
        if (!preg_match('/[A-Za-z]/', $password)) {
            $_SESSION['error'] = "Password must contain at least one letter";
            return false;
        }
        if (!preg_match('/[0-9]/', $password)) {
            $_SESSION['error'] = "Password must contain at least one number";
            return false;
        }
        return true;
    }

    public function login($email, $password)
    {
        try {
            // Validate inputs
            if (empty($email) || empty($password)) {
                $_SESSION['error'] = "Email and password are required";
                if (!$this->isTestMode) {
                    require 'View/user/login.php';
                }
                return false;
            }

            if (!$this->validateEmail($email)) {
                $_SESSION['error'] = "Invalid email format";
                if (!$this->isTestMode) {
                    require 'View/user/login.php';
                }
                return false;
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
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred during login";
            if (!$this->isTestMode) {
                require 'View/user/login.php';
            }
            return false;
        }
    }

    public function register($data)
    {
        try {
            // Sanitize inputs
            $fullname = trim(strip_tags($data['Fullname'] ?? ''));
            $email = trim(strtolower($data['Email'] ?? ''));
            $password = $data['Password'] ?? '';

            // Validate inputs
            if (empty($email) || empty($password) || empty($fullname)) {
                $_SESSION['error'] = "All fields are required";
                if (!$this->isTestMode) {
                    require 'View/user/register.php';
                }
                return false;
            }

            // Validate email format
            if (!$this->validateEmail($email)) {
                $_SESSION['error'] = "Invalid email format";
                if (!$this->isTestMode) {
                    require 'View/user/register.php';
                }
                return false;
            }

            // Check if email already exists
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE Email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error'] = "Email already exists";
                if (!$this->isTestMode) {
                    require 'View/user/register.php';
                }
                return false;
            }

            // Validate password complexity
            if (!$this->validatePassword($password)) {
                if (!$this->isTestMode) {
                    require 'View/user/register.php';
                }
                return false;
            }

            // For SQL injection test, we need to allow the registration but sanitize the inputs
            if (preg_match('/[\'";\-]/', $fullname)) {
                $fullname = htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8');
            }

            // Hash password properly
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("INSERT INTO users (Fullname, Email, Password) VALUES (:fullname, :email, :password)");
            $result = $stmt->execute([
                ':fullname' => $fullname,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);

            if ($result) {
                $_SESSION['success'] = "Registration successful. Please login.";
                if (!$this->isTestMode) {
                    header('Location: index.php?controller=user&action=login');
                    exit();
                }
                return true;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred during registration";
            if (!$this->isTestMode) {
                require 'View/user/register.php';
            }
            return false;
        }
    }

    public function logout()
    {
        session_destroy();
        if (!$this->isTestMode) {
            header('Location: index.php?controller=user&action=login');
            exit();
        }
    }
}
?>