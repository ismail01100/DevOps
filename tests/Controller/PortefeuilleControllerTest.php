<?php

namespace Tests\Controller;

use PHPUnit\Framework\TestCase;
use PortefeuilleController;
use PDO;
use DatabaseConnection;

class PortefeuilleControllerTest extends TestCase
{
    private $portefeuilleController;
    private $db;
    private $testPortefeuilleId;
    
    protected function setUp(): void
    {
        // Get test database connection
        $this->db = DatabaseConnection::getInstance()->getConnection();
        $this->portefeuilleController = new PortefeuilleController(true);
        
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

    public function testUpdateSalary()
    {
        $newSalary = 6000;
        $testData = ['Salaire' => $newSalary];

        ob_start();
        $this->portefeuilleController->updateSalary($testData);
        ob_end_clean();

        // Verify salary was updated
        $stmt = $this->db->prepare("SELECT Salaire, TotalIncome FROM portefeuille WHERE CodePortefeuille = ?");
        $stmt->execute([$this->testPortefeuilleId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals($newSalary, $result['Salaire']);
        $this->assertEquals(6000, $result['TotalIncome']); // Initial 5000 + 1000 difference
    }

    public function testAddIncome()
    {
        $bonus = 1000;
        $testData = ['Bonus' => $bonus];

        ob_start();
        $this->portefeuilleController->addIncome($testData);
        ob_end_clean();

        // Verify income was added
        $stmt = $this->db->prepare("SELECT Solde, TotalIncome FROM portefeuille WHERE CodePortefeuille = ?");
        $stmt->execute([$this->testPortefeuilleId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(6000, $result['Solde']); // Initial 5000 + 1000 bonus
        $this->assertEquals(6000, $result['TotalIncome']); // Initial 5000 + 1000 bonus
    }

    public function testUpdateSavingPourcentage()
    {
        $newPercentage = 20;
        $testData = ['SavingPourcentage' => $newPercentage];

        ob_start();
        $this->portefeuilleController->updateSavingPourcentage($testData);
        ob_end_clean();

        // Verify saving percentage was updated
        $stmt = $this->db->prepare("SELECT SavingPourcentage FROM portefeuille WHERE CodePortefeuille = ?");
        $stmt->execute([$this->testPortefeuilleId]);
        $result = $stmt->fetchColumn();

        $this->assertEquals($newPercentage, $result);
    }

    public function testResetBalance()
    {
        // First modify the balance
        $stmt = $this->db->prepare("UPDATE portefeuille SET Solde = ?, TotalIncome = ? WHERE CodePortefeuille = ?");
        $stmt->execute([7000, 7000, $this->testPortefeuilleId]);

        ob_start();
        $this->portefeuilleController->resetBalance();
        ob_end_clean();

        // Verify balance was reset to salary amount
        $stmt = $this->db->prepare("SELECT Salaire, Solde, TotalIncome FROM portefeuille WHERE CodePortefeuille = ?");
        $stmt->execute([$this->testPortefeuilleId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals($result['Salaire'], $result['Solde']);
        $this->assertEquals($result['Salaire'], $result['TotalIncome']);
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