<?php

namespace Tests\Controller;

use PHPUnit\Framework\TestCase;
use UserController;
use PDO;
use DatabaseConnection;

class UserControllerTest extends TestCase
{
    private $userController;
    private $db;
    
    protected function setUp(): void
    {
        // Get test database connection
        $this->db = DatabaseConnection::getInstance()->getConnection();
        $this->userController = new UserController(true);
        
        // Disable foreign key checks, truncate tables, then re-enable checks
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
        $this->db->exec('TRUNCATE TABLE portefeuille');
        $this->db->exec('TRUNCATE TABLE charges');
        $this->db->exec('TRUNCATE TABLE users');
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function testSuccessfulRegistration()
    {
        $testData = [
            'Fullname' => 'Test User',
            'Email' => 'test@example.com',
            'Password' => 'password123'
        ];

        $result = $this->userController->register($testData);
        $this->assertTrue($result);

        // Verify user was created in database
        $stmt = $this->db->prepare("SELECT * FROM users WHERE Email = :email");
        $stmt->execute([':email' => $testData['Email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($user);
        $this->assertEquals($testData['Fullname'], $user['Fullname']);
        $this->assertEquals($testData['Email'], $user['Email']);
        $this->assertTrue(password_verify($testData['Password'], $user['Password']));
    }

    public function testRegistrationWithMissingData()
    {
        $testData = [
            'Email' => 'test@example.com',
            'Password' => 'password123'
            // Missing Fullname
        ];

        // Start output buffering to capture any output
        ob_start();
        $this->userController->register($testData);
        ob_end_clean();

        // Verify no user was created
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users");
        $stmt->execute();
        $count = $stmt->fetchColumn();

        $this->assertEquals(0, $count);
        $this->assertEquals("All fields are required", $_SESSION['error']);
    }

    public function testSuccessfulLogin()
    {
        // First create a user
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("INSERT INTO users (Fullname, Email, Password) VALUES (:fullname, :email, :password)");
        $stmt->execute([
            ':fullname' => 'Test User',
            ':email' => 'test@example.com',
            ':password' => $hashedPassword
        ]);

        $result = $this->userController->login('test@example.com', $password);
        $this->assertTrue($result);

        $this->assertArrayHasKey('user', $_SESSION);
        $this->assertEquals('Test User', $_SESSION['user']['Fullname']);
        $this->assertEquals('test@example.com', $_SESSION['user']['Email']);
    }

    public function testLoginWithInvalidCredentials()
    {
        // Start output buffering
        ob_start();
        $this->userController->login('nonexistent@example.com', 'wrongpassword');
        ob_end_clean();

        $this->assertArrayNotHasKey('user', $_SESSION);
        $this->assertEquals("Invalid email or password", $_SESSION['error']);
    }

    public function testLoginWithEmptyCredentials()
    {
        // Start output buffering
        ob_start();
        $this->userController->login('', '');
        ob_end_clean();

        $this->assertArrayNotHasKey('user', $_SESSION);
        $this->assertEquals("Email and password are required", $_SESSION['error']);
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
        $this->db->exec('TRUNCATE TABLE portefeuille');
        $this->db->exec('TRUNCATE TABLE charges');
        $this->db->exec('TRUNCATE TABLE users');
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
        
        // Clear session data
        $_SESSION = [];
    }
}