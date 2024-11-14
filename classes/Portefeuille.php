<?php
class Portefeuille {
    private $charges = [];

    public function ajouterCharge($charge) {
        $this->charges[] = $charge;
    }

    public function calculerTotalCharges() {
        $total = 0;
        foreach ($this->charges as $charge) {
            $total += $charge->calculerMontantApresReduction();
        }
        return $total;
    }
}
?>
