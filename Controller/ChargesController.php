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
            $stmt = $this->db->prepare("UPDATE portefeuille SET Solde = Solde - :montant WHERE CodePortefeuille = :codePortefeuille");
            $stmt->execute([
                ':montant' => $data['Montant'],
                ':codePortefeuille' => $data['CodePortefeuille']
            ]);
            header('Location: index.php?controller=charges&action=index');
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM charges WHERE CodeCharge = :id");
            $stmt->execute([':id' => $id]);
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