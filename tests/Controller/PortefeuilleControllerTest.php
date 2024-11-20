<?php

namespace Tests\Controller;

use DatabaseConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use PortefeuilleController;
use DateTime;

class PortefeuilleControllerTest extends TestCase
{
    private $portefeuilleController;
    private $db;
    private $testPortefeuilleId;
    private $testUserId;

    protected function setUp(): void
    {
        $this->db = DatabaseConnection::getInstance()->getConnection();
        $this->portefeuilleController = new PortefeuilleController(true);
        
        // Clean up and prepare database
        $this->cleanDatabase();
        
        // Create test user
        $stmt = $this->db->prepare("INSERT INTO users (Fullname, Email, Password) VALUES (?, ?, ?)");
        $stmt->execute(['Test User', 'test@example.com', password_hash('password123', PASSWORD_DEFAULT)]);
        $this->testUserId = $this->db->lastInsertId();
        
        // Create test portfolio
        $stmt = $this->db->prepare("INSERT INTO portefeuille (CodeUtilisateur, Salaire, Solde, TotalIncome, SavingPourcentage) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$this->testUserId, 5000, 5000, 5000, 10]);
        $this->testPortefeuilleId = $this->db->lastInsertId();
        
        // Set up session
        $_SESSION['user'] = [
            'CodeUtilisateur' => $this->testUserId,
            'CodePortefeuille' => $this->testPortefeuilleId
        ];
    }

    public function testSalaryManagement()
    {
        try {
            // Test updating salary to valid amount
            $this->portefeuilleController->updateSalary(['Salaire' => 6000]);
            $result = $this->getPortefeuilleData();
            $this->assertEquals(6000, $result['Salaire']);
            $this->assertEquals(6000, $result['TotalIncome']);

            // Test updating salary to zero
            $this->portefeuilleController->updateSalary(['Salaire' => 0]);
            $result = $this->getPortefeuilleData();
            $this->assertEquals(0, $result['Salaire']);
            $this->assertEquals(0, $result['TotalIncome']);

            // Test updating salary with negative value (should be rejected)
            $this->portefeuilleController->updateSalary(['Salaire' => -1000]);
            $result = $this->getPortefeuilleData();
            $this->assertEquals(0, $result['Salaire']);

            // Test updating salary with null value (should maintain previous value)
            $this->portefeuilleController->updateSalary(['Salaire' => null]);
            $result = $this->getPortefeuilleData();
            $this->assertEquals(0, $result['Salaire']);
        } catch (\PDOException $e) {
            $this->fail('Failed in testSalaryManagement: ' . $e->getMessage());
        }
    }

    public function testIncomeManagement()
    {
        // Test adding valid bonus
        $this->portefeuilleController->addIncome(['Bonus' => 1000]);
        $result = $this->getPortefeuilleData();
        $this->assertEquals(6000, $result['Solde']); // 5000 + 1000
        $this->assertEquals(6000, $result['TotalIncome']);

        // Test adding negative bonus (should be rejected)
        $this->portefeuilleController->addIncome(['Bonus' => -500]);
        $result = $this->getPortefeuilleData();
        $this->assertEquals(6000, $result['Solde']); // Should remain unchanged
        
        // Test adding zero bonus
        $this->portefeuilleController->addIncome(['Bonus' => 0]);
        $result = $this->getPortefeuilleData();
        $this->assertEquals(6000, $result['Solde']); // Should remain unchanged
    }

    public function testSavingPourcentageManagement()
    {
        // Test updating saving percentage
        $this->portefeuilleController->updateSavingPourcentage(['SavingPourcentage' => 20]);
        $result = $this->getPortefeuilleData();
        $this->assertEquals(20, $result['SavingPourcentage']);

        // Test invalid percentage (negative)
        $this->portefeuilleController->updateSavingPourcentage(['SavingPourcentage' => -10]);
        $result = $this->getPortefeuilleData();
        $this->assertEquals(20, $result['SavingPourcentage']); // Should remain unchanged

        // Test percentage > 100
        $this->portefeuilleController->updateSavingPourcentage(['SavingPourcentage' => 150]);
        $result = $this->getPortefeuilleData();
        $this->assertEquals(20, $result['SavingPourcentage']); // Should remain unchanged
    }

    public function testBalanceReset()
    {
        // Test initial reset
        $portefeuille = [
            'CodePortefeuille' => $this->testPortefeuilleId,
            'Salaire' => 5000
        ];
        
        $wasReset = $this->portefeuilleController->checkAndResetBalance($portefeuille);
        $this->assertTrue($wasReset);
        
        $result = $this->getPortefeuilleData();
        $this->assertEquals(5000, $result['Solde']);
        $this->assertEquals(5000, $result['TotalIncome']);
        $this->assertEquals((new DateTime())->format('Y-m-d'), $result['LastResetDate']);

        // Test no reset needed (same day)
        $wasReset = $this->portefeuilleController->checkAndResetBalance($portefeuille);
        $this->assertFalse($wasReset);
    }

    private function getPortefeuilleData(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM portefeuille WHERE CodePortefeuille = ?");
        $stmt->execute([$this->testPortefeuilleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function cleanDatabase(): void
    {
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
        $this->db->exec('TRUNCATE TABLE charges');
        $this->db->exec('TRUNCATE TABLE portefeuille');
        $this->db->exec('TRUNCATE TABLE users');
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    protected function tearDown(): void
    {
        $this->cleanDatabase();
        $_SESSION = [];
    }
}