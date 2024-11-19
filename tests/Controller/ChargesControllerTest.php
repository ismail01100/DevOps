<?php

namespace Tests\Controller;

use PHPUnit\Framework\TestCase;
use ChargesController;
use PDO;
use DatabaseConnection;

class ChargesControllerTest extends TestCase
{
    private $chargesController;
    private $db;
    private $testPortefeuilleId;
    
    protected function setUp(): void
    {
        // Get test database connection
        $this->db = DatabaseConnection::getInstance()->getConnection();
        $this->chargesController = new ChargesController(true);
        
        // Disable foreign key checks, truncate tables, then re-enable checks
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
        $this->db->exec('TRUNCATE TABLE charges');
        $this->db->exec('TRUNCATE TABLE portefeuille');
        $this->db->exec('TRUNCATE TABLE users');
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
        
        // Create test user and portfolio
        $stmt = $this->db->prepare("INSERT INTO users (Fullname, Email, Password) VALUES (?, ?, ?)");
        $stmt->execute(['Test User', 'test@example.com', password_hash('password123', PASSWORD_DEFAULT)]);
        $userId = $this->db->lastInsertId();
        
        $stmt = $this->db->prepare("INSERT INTO portefeuille (CodeUtilisateur, Salaire, Solde, TotalIncome) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, 5000, 5000, 5000]);
        $this->testPortefeuilleId = $this->db->lastInsertId();
        
        // Set up session data
        $_SESSION['user'] = [
            'CodeUtilisateur' => $userId,
            'CodePortefeuille' => $this->testPortefeuilleId
        ];
    }

    public function testCreateCharge()
    {
        $testData = [
            'CodePortefeuille' => $this->testPortefeuilleId,
            'NomCharge' => 'Test Charge',
            'Description' => 'Test Description',
            'Montant' => 100.50,
            'DateCharge' => date('Y-m-d'),
            'Variable' => 1
        ];

        ob_start(); // Start output buffering
        $this->chargesController->create($testData);
        ob_end_clean(); // End output buffering

        // Verify charge was created
        $stmt = $this->db->prepare("SELECT * FROM charges WHERE CodePortefeuille = ? AND NomCharge = ?");
        $stmt->execute([$this->testPortefeuilleId, $testData['NomCharge']]);
        $charge = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($charge);
        $this->assertEquals($testData['Description'], $charge['Description']);
        $this->assertEquals($testData['Montant'], $charge['Montant']);
        $this->assertEquals($testData['DateCharge'], $charge['DateCharge']);
        $this->assertEquals($testData['Variable'], $charge['Variable']);
    }

    public function testDeleteCharge()
    {
        // First create a charge
        $stmt = $this->db->prepare("INSERT INTO charges (CodePortefeuille, NomCharge, Description, Montant, DateCharge, Variable) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $this->testPortefeuilleId,
            'Test Charge',
            'Test Description',
            100.50,
            date('Y-m-d'),
            1
        ]);
        $chargeId = $this->db->lastInsertId();

        // Delete the charge
        $this->chargesController->delete($chargeId);

        // Verify charge was deleted
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM charges WHERE CodeCharge = ?");
        $stmt->execute([$chargeId]);
        $count = $stmt->fetchColumn();

        $this->assertEquals(0, $count);
    }

    public function testUpdateBalance()
    {
        // Create multiple charges for current month
        $currentDate = date('Y-m-d');
        $stmt = $this->db->prepare("INSERT INTO charges (CodePortefeuille, NomCharge, Description, Montant, DateCharge, Variable) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
        
        $charges = [
            ['Charge 1', 'Description 1', 100.00, $currentDate, 1],
            ['Charge 2', 'Description 2', 200.00, $currentDate, 0],
            ['Charge 3', 'Description 3', 300.00, $currentDate, 1]
        ];

        foreach ($charges as $charge) {
            $stmt->execute(array_merge([$this->testPortefeuilleId], $charge));
        }

        // Update balance
        $this->chargesController->updateBalance();

        // Verify balance was updated correctly
        $stmt = $this->db->prepare("SELECT Solde FROM portefeuille WHERE CodePortefeuille = ?");
        $stmt->execute([$this->testPortefeuilleId]);
        $newBalance = $stmt->fetchColumn();

        // Total charges = 600, Initial balance = 5000, Expected = 4400
        $this->assertEquals(4400, $newBalance);
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
        $this->db->exec('TRUNCATE TABLE charges');
        $this->db->exec('TRUNCATE TABLE portefeuille');
        $this->db->exec('TRUNCATE TABLE users');
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
        
        // Clear session data
        $_SESSION = [];
    }
}