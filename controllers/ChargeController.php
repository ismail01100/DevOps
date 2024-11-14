<?php
include_once 'Charge.php';

class ChargeController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function ajouterCharge($description, $montant, $date, $variable) {
        $charge = new Charge($this->conn);
        $charge->description = $description;
        $charge->montant = $montant;
        $charge->date = $date;
        $charge->variable = $variable;
        return $charge->save();
    }
}
?>
