<?php
require_once 'Portefeuille.php';

class Utilisateur {
    private $id;
    private $nom;
    private $email;
    private $salaire;
    private $portefeuilles = [];

    public function __construct($id, $nom, $email, $salaire) {
        $this->id = $id;
        $this->nom = $nom;
        $this->email = $email;
        $this->salaire = $salaire;
    }

    public function ajouterPortefeuille($portefeuille) {
        $this->portefeuilles[] = $portefeuille;
    }

    public function consulterPortefeuilles() {
        return $this->portefeuilles;
    }

    public function getNom() {
        return $this->nom;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getSalaire() {
        return $this->salaire;
    }
}
?>
