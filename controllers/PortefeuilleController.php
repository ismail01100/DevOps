<?php
require_once '../Portefeuille.php';

class PortefeuilleController {
    private $portefeuille;

    public function __construct() {
        $this->portefeuille = new Portefeuille();
    }

    public function ajouterCharge($charge) {
        $this->portefeuille->ajouterCharge($charge);
    }

    public function calculerTotalCharges() {
        return $this->portefeuille->calculerTotalCharges();
    }

    public function obtenirPortefeuille() {
        return $this->portefeuille;
    }
}
?>
