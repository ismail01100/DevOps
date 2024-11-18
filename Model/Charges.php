<?php
class Charges {
    private $CodeCharge;
    private $CodePortefeuille;
    private $NomCharge;
    private $Description;
    private $Montant;
    private $DateCharge;
    private $Variable;

    // Getters
    public function getCodeCharge() {
        if (empty($this->CodeCharge)) {
            return null;
        }
        return $this->CodeCharge;
    }

    public function getCodePortefeuille() {
        return $this->CodePortefeuille;
    }

    public function getNomCharge() {
        return $this->NomCharge;
    }

    public function getDescription() {
        return $this->Description;
    }

    public function getMontant() {
        return $this->Montant;
    }

    public function getDateCharge() {
        return $this->DateCharge;
    }

    public function getVariable() {
        return $this->Variable;
    }

    // Setters
    public function setCodeCharge($CodeCharge) {
        if (!empty($CodeCharge)) {
            $this->CodeCharge = (int)$CodeCharge;
        }
    }

    public function setCodePortefeuille($CodePortefeuille) {
        if (!empty($CodePortefeuille)) {
            $this->CodePortefeuille = (int)$CodePortefeuille;
        }
    }

    public function setNomCharge($NomCharge) {
        $this->NomCharge = $NomCharge;
    }

    public function setDescription($Description) {
        $this->Description = $Description;
    }

    public function setMontant($Montant) {
        $this->Montant = $Montant;
    }

    public function setDateCharge($DateCharge) {
        $this->DateCharge = $DateCharge;
    }

    public function setVariable($Variable) {
        $this->Variable = $Variable;
    }
    
}
?>