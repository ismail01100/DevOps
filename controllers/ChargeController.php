<?php
require_once '../Charge.php';

class ChargeController {
    public function creerChargeFixe($nom, $montant) {
        return new ChargeFixe($nom, $montant);
    }

    public function creerChargeVariable($nom, $montant, $tauxReduction) {
        return new ChargeVariable($nom, $montant, $tauxReduction);
    }

    public function calculerMontantApresReduction($charge) {
        return $charge->calculerMontantApresReduction();
    }
}
?>
