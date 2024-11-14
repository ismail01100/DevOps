<?php
require_once '../Utilisateur.php';

class UtilisateurController {
    private $utilisateur;

    public function __construct($id, $nom, $email, $salaire) {
        $this->utilisateur = new Utilisateur($id, $nom, $email, $salaire);
    }

    public function ajouterPortefeuille($portefeuille) {
        $this->utilisateur->ajouterPortefeuille($portefeuille);
    }

    public function afficherPortefeuilles() {
        return $this->utilisateur->consulterPortefeuilles();
    }

    public function getDetailsUtilisateur() {
        return [
            'nom' => $this->utilisateur->getNom(),
            'email' => $this->utilisateur->getEmail(),
            'salaire' => $this->utilisateur->getSalaire(),
        ];
    }
}
?>
