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
        $this->db = DatabaseConnection::getInstance()->getConnection();
        $this->userController = new UserController(true);
        $this->cleanDatabase();
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

    public function testRegistrationWithExistingEmail()
    {
        // First registration
        $testData = [
            'Fullname' => 'Test User',
            'Email' => 'test@example.com',
            'Password' => 'password123'
        ];
        $this->userController->register($testData);

        // Second registration with same email
        $testData['Fullname'] = 'Different User';
        ob_start();
        $result = $this->userController->register($testData);
        ob_end_clean();

        $this->assertFalse($result);
        $this->assertEquals("Email already exists", $_SESSION['error']);
    }

    public function testRegistrationWithSQLInjection()
    {
        // Test different SQL injection attack vectors
        $maliciousData = [
            [
                'Fullname' => "Robert'; DROP TABLE users; --",  // Attempt to drop table
                'Email' => "normal@email.com",
                'Password' => "password123",
                'description' => "Classic SQL injection with table drop attempt"
            ],
            [
                'Fullname' => "Normal Name",
                'Email' => "attacker@evil.com' OR '1'='1",  // Always true condition
                'Password' => "password123",
                'description' => "UNION-based SQL injection attempt"
            ],
            [
                'Fullname' => "Normal Name",
                'Email' => "normal@email.com",
                'Password' => "password123'; DELETE FROM users; --",  // Attempt to delete records
                'description' => "Deletion attempt through password field"
            ]
        ];

        foreach ($maliciousData as $testCase) {
            ob_start();
            $this->userController->register([
                'Fullname' => $testCase['Fullname'],
                'Email' => $testCase['Email'],
                'Password' => $testCase['Password']
            ]);
            ob_end_clean();

            // After each injection attempt, verify database integrity
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users");
            $stmt->execute();
            $count = $stmt->fetchColumn();
            
            $this->assertEquals(1, $count, "Database should remain intact after injection attempt: " . $testCase['description']);
        }
    }

    public function testLoginWithSQLInjection()
    {
        // Create legitimate user first
        $this->createTestUser();

        $maliciousInputs = [
            ["' OR '1'='1", "anything"],
            ["admin'--", "anything"],
            ["' UNION SELECT 'admin','admin','admin' FROM users --", "anything"],
            ["test@example.com' AND 1=1--", "anything"],
            ["test@example.com'; DROP TABLE users; --", "password123"]
        ];

        foreach ($maliciousInputs as $input) {
            ob_start();
            $result = $this->userController->login($input[0], $input[1]);
            ob_end_clean();

            $this->assertFalse($result, "SQL Injection attempt should fail: " . $input[0]);
            $this->assertArrayNotHasKey('user', $_SESSION);
        }

        // Verify database integrity
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count, "Database should remain intact after injection attempts");
    }

    public function testPasswordComplexity()
    {
        $weakPasswords = [
            ['password' => '123', 'expected' => 'Password must be at least 8 characters'],
            ['password' => 'password', 'expected' => 'Password must contain at least one number'],
            ['password' => '12345678', 'expected' => 'Password must contain at least one letter'],
        ];

        foreach ($weakPasswords as $test) {
            $testData = [
                'Fullname' => 'Test User',
                'Email' => 'test@example.com',
                'Password' => $test['password']
            ];

            ob_start();
            $result = $this->userController->register($testData);
            ob_end_clean();

            $this->assertFalse($result);
            $this->assertEquals($test['expected'], $_SESSION['error']);
        }
    }

    public function testEmailValidation()
    {
        $invalidEmails = [
            'notanemail',
            'still@not@anemail',
            '@nocontent.com',
            'spaces in@email.com',
            'missing.domain@',
            '.starting.dot@domain.com',
            'ending.dot.@domain.com',
            'double..dot@domain.com'
        ];

        foreach ($invalidEmails as $email) {
            $testData = [
                'Fullname' => 'Test User',
                'Email' => $email,
                'Password' => 'password123'
            ];

            ob_start();
            $result = $this->userController->register($testData);
            ob_end_clean();

            $this->assertFalse($result);
            $this->assertEquals("Invalid email format", $_SESSION['error']);
        }
    }

    private function createTestUser(): void
    {
        $stmt = $this->db->prepare("INSERT INTO users (Fullname, Email, Password) VALUES (?, ?, ?)");
        $stmt->execute(['Test User', 'test@example.com', password_hash('password123', PASSWORD_DEFAULT)]);
    }

    private function cleanDatabase(): void
    {
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
        $this->db->exec('TRUNCATE TABLE portefeuille');
        $this->db->exec('TRUNCATE TABLE charges');
        $this->db->exec('TRUNCATE TABLE users');
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    protected function tearDown(): void
    {
        $this->cleanDatabase();
        $_SESSION = [];
    }
}