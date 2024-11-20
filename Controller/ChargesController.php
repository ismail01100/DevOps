<?php
require_once 'Model/Charges.php';
require_once 'Model/DatabaseConnection.php';

class ChargesController
{
    private $db;
    private $isTestMode;

    public function __construct($isTestMode = false)
    {
        $this->db = DatabaseConnection::getInstance()->getConnection();
        $this->isTestMode = $isTestMode;
    }

    public function index()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM charges , portefeuille WHERE portefeuille.CodeUtilisateur = :userId AND portefeuille.CodePortefeuille = charges.CodePortefeuille");
            $stmt->execute([':userId' => $_SESSION['user']['CodeUtilisateur']]);
            $charges = $stmt->fetchAll(PDO::FETCH_ASSOC);
            require 'View/charges/index.php';
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function create($data)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO charges (CodePortefeuille, NomCharge, Description, Montant, DateCharge, Variable) 
                                      VALUES (:codePortefeuille, :nomCharge, :description, :montant, :dateCharge, :variable)");
            $stmt->execute([
                ':codePortefeuille' => $data['CodePortefeuille'],
                ':nomCharge' => $data['NomCharge'],
                ':description' => $data['Description'],
                ':montant' => $data['Montant'],
                ':dateCharge' => $data['DateCharge'],
                ':variable' => $data['Variable']
            ]);
            $this->updateBalance();
            if (!$this->isTestMode) {
                header('Location: index.php?controller=charges&action=index');
            }
            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function updateBalance()
    {
        // Calculate total of charges for current month and minus it from the total income
        $stmt = $this->db->prepare("SELECT SUM(Montant) AS TotalCharges FROM charges WHERE MONTH(DateCharge) = MONTH(CURRENT_DATE) AND YEAR(DateCharge) = YEAR(CURRENT_DATE) AND CodePortefeuille = :codePortefeuille");
        $stmt->execute([':codePortefeuille' => $_SESSION['user']['CodePortefeuille']]);
        $totalCharges = $stmt->fetch(PDO::FETCH_ASSOC)['TotalCharges'] ?? 0;  // Use null coalescing operator

        // Get current TotalIncome
        $stmt = $this->db->prepare("SELECT TotalIncome FROM portefeuille WHERE CodePortefeuille = :codePortefeuille");
        $stmt->execute([':codePortefeuille' => $_SESSION['user']['CodePortefeuille']]);
        $totalIncome = $stmt->fetch(PDO::FETCH_ASSOC)['TotalIncome'] ?? 0;

        // Calculate new balance
        $newBalance = $totalIncome - $totalCharges;

        $stmt = $this->db->prepare("UPDATE portefeuille SET Solde = :solde WHERE CodePortefeuille = :codePortefeuille");
        $stmt->execute([
            ':solde' => $newBalance,
            ':codePortefeuille' => $_SESSION['user']['CodePortefeuille']
        ]);
    }

    public function delete($data)
    {
        if (isset($data['CodeCharge'])) {
            $chargeId = $data['CodeCharge'];
            try {
                $stmt = $this->db->prepare("DELETE FROM charges WHERE CodeCharge = :id");
                $stmt->execute([':id' => $chargeId]);
                $this->updateBalance();
                if (!$this->isTestMode) {
                    header('Location: index.php?controller=charges&action=index');
                }
                return true;
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                return false;
            }
        }
    }

    public function update($data)
    {
        try {
            $stmt = $this->db->prepare("UPDATE charges 
                SET NomCharge = :nomCharge, 
                    Description = :description, 
                    Montant = :montant, 
                    DateCharge = :dateCharge, 
                    Variable = :variable 
                WHERE CodeCharge = :id");

            $stmt->execute([
                ':nomCharge' => $data['NomCharge'],
                ':description' => $data['Description'],
                ':montant' => $data['Montant'],
                ':dateCharge' => $data['DateCharge'],
                ':variable' => $data['Variable'],
                ':id' => $data['CodeCharge'],
            ]);
            $this->updateBalance();
            header('Location: index.php?controller=charges&action=index');
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
    public function get($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM charges WHERE CodeCharge = :id");
            $stmt->execute([':id' => $id]);
            $charge = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$charge) {
                http_response_code(404);
                echo json_encode(['error' => 'Charge not found']);
                return;
            }

            header('Content-Type: application/json');
            echo json_encode($charge);
            return;

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            return;
        }
    }

    public function show($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM charges WHERE CodeCharge = :id");
            $stmt->execute([':id' => $id]);
            $charge = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$charge) {
                header('Location: index.php?controller=charges&action=index');
                exit();
            }
            require 'View/charges/show.php';
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>