<?php
include_once 'Portefeuille.php';

class PortefeuilleController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function consulterPortefeuille() {
        $portefeuille = new Portefeuille($this->conn);
        $totalCharges = $portefeuille->calculerTotalCharges();
        $reste = $portefeuille->calculerReste();
        $tauxReduction = $portefeuille->suggererReduction();

        return [
            'totalCharges' => $totalCharges,
            'reste' => $reste,
            'tauxReduction' => $tauxReduction
        ];
    }
}
?>
