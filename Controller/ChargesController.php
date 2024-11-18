<?php
require_once 'Model/Charges.php';
require_once 'Model/DatabaseConnection.php';

class ChargesController {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    public function index() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM charges , portefeuille WHERE portefeuille.CodeUtilisateur = :userId AND portefeuille.CodePortefeuille = charges.CodePortefeuille");
            $stmt->execute([':userId' => $_SESSION['user']['CodeUtilisateur']]);
            $charges = $stmt->fetchAll(PDO::FETCH_ASSOC);
            require 'View/charges/index.php';
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function create($data) {
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
            header('Location: index.php?controller=charges&action=index');
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function updateBalance(){
        //calcualte total of charges for current month and minus it from the total income
        $stmt = $this->db->prepare("SELECT SUM(Montant) AS TotalCharges FROM charges WHERE MONTH(DateCharge) = MONTH(CURRENT_DATE) AND YEAR(DateCharge) = YEAR(CURRENT_DATE)");
        $stmt->execute();
        $totalCharges = $stmt->fetch(PDO::FETCH_ASSOC)['TotalCharges'];
        $stmt = $this->db->prepare("UPDATE portefeuille SET Solde = TotalIncome - :totalCharges WHERE CodePortefeuille = :codePortefeuille");
        $stmt->execute([
            ':totalCharges' => $totalCharges,
            ':codePortefeuille' => $_SESSION['user']['CodePortefeuille']
        ]);
    }
    
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM charges WHERE CodeCharge = :id");
            $stmt->execute([':id' => $id]);
            $this->updateBalance();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function edit($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM charges WHERE CodeCharge = :id");
            $stmt->execute([':id' => $id]);
            $charge = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$charge) {
                header('Location: index.php?controller=charges&action=index');
                exit();
            }
            require 'View/charges/edit.php';
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function update($id, $data) {
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
                ':id' => $id
            ]);
            $this->updateBalance();
            header('Location: index.php?controller=charges&action=index');
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function show($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM charges WHERE CodeCharge = :id");
            $stmt->execute([':id' => $id]);
            $charge = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$charge) {
                header('Location: index.php?controller=charges&action=index');
                exit();
            }
            
            require 'View/charges/show.php';
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>