<?php
class Portefeuille {
    private $conn;
    private $table_name = "Portefeuille";

    public $id;
    public $pourcentageEpargne;
    public $remunerationTotale;
    public $reste;
    public $tauxReduction;
    public $utilisateurID;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function ajouterCharge($charge) {
        $query = "INSERT INTO Charge SET Description=:description, Montant=:montant, Date=:date, Variable=:variable, PortefeuilleID=:portefeuilleID";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":description", $charge->description);
        $stmt->bindParam(":montant", $charge->montant);
        $stmt->bindParam(":date", $charge->date);
        $stmt->bindParam(":variable", $charge->variable);
        $stmt->bindParam(":portefeuilleID", $this->id);

        return $stmt->execute();
    }

    public function calculerTotalCharges() {
        $query = "SELECT SUM(Montant) as Total FROM Charge WHERE PortefeuilleID = :portefeuilleID";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":portefeuilleID", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['Total'];
    }

    public function calculerReste() {
        $totalCharges = $this->calculerTotalCharges();
        $this->reste = $this->remunerationTotale - $totalCharges;
        return $this->reste;
    }

    public function suggererReduction() {
        $fixedChargesQuery = "SELECT SUM(Montant) as TotalFixed FROM Charge WHERE PortefeuilleID = :portefeuilleID AND Variable = 0";
        $variableChargesQuery = "SELECT SUM(Montant) as TotalVariable FROM Charge WHERE PortefeuilleID = :portefeuilleID AND Variable = 1";

        $stmt = $this->conn->prepare($fixedChargesQuery);
        $stmt->bindParam(":portefeuilleID", $this->id);
        $stmt->execute();
        $totalFixedCharges = $stmt->fetch(PDO::FETCH_ASSOC)['TotalFixed'];

        $stmt = $this->conn->prepare($variableChargesQuery);
        $stmt->bindParam(":portefeuilleID", $this->id);
        $stmt->execute();
        $totalVariableCharges = $stmt->fetch(PDO::FETCH_ASSOC)['TotalVariable'];

        $requiredSavings = $this->remunerationTotale * $this->pourcentageEpargne;
        $currentSavings = $this->remunerationTotale - $totalFixedCharges - $totalVariableCharges;

        if ($currentSavings >= $requiredSavings) {
            return 0; // No reduction needed
        } else {
            $neededReduction = $requiredSavings - $currentSavings;
            return ($neededReduction / $totalVariableCharges) * 100;
        }
    }
}
?>
