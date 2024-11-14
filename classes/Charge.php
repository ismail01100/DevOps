<?php
abstract class Charge {
    protected $nom;
    protected $montant;

    public function __construct($nom, $montant) {
        $this->nom = $nom;
        $this->montant = $montant;
    }

    abstract public function calculerMontantApresReduction();
}

class ChargeFixe extends Charge {
    public function calculerMontantApresReduction() {
        return $this->montant;
    }
}

class ChargeVariable extends Charge {
    private $tauxReduction;

    public function __construct($nom, $montant, $tauxReduction) {
        parent::__construct($nom, $montant);
        $this->tauxReduction = $tauxReduction;
    }

    public function calculerMontantApresReduction() {
        return $this->montant * (1 - $this->tauxReduction / 100);
    }
}
?>
