<?php
class Portefeuille {
    private $CodePortefeuille;
    private $CodeUtilisateur;
    private $Salaire;
    private $Solde;
    private $TotalIncome;
    private $SavingPourcentage;
    private $LastResetDate;


    // Getters
    public function getCodePortefeuille() {
        return $this->CodePortefeuille;
    }

    public function getCodeUtilisateur() {
        return $this->CodeUtilisateur;
    }

    public function getSalaire() {
        return $this->Salaire;
    }

    public function getSolde() {
        return $this->Solde;
    }

    public function getLastResetDate() {
        return $this->LastResetDate;
    }

    public function getSavingPourcentage() {
        return $this->SavingPourcentage;
    }

    public function getTotalIncome() {
        return $this->TotalIncome;
    }

    // Setters
    public function setCodePortefeuille($CodePortefeuille) {
        $this->CodePortefeuille = $CodePortefeuille;
    }

    public function setCodeUtilisateur($CodeUtilisateur) {
        $this->CodeUtilisateur = $CodeUtilisateur;
    }

    public function setSalaire($Salaire) {
        $this->Salaire = $Salaire;
    }

    public function setSolde($Solde) {
        $this->Solde = $Solde;
    }

    public function setSavingPourcentage($SavingPourcentage) {
        $this->SavingPourcentage = $SavingPourcentage;
    }

    public function setLastResetDate($LastResetDate) {
        $this->LastResetDate = $LastResetDate;
    }

    public function setTotalIncome($TotalIncome) {
        $this->TotalIncome = $TotalIncome;
    }
}

?>
