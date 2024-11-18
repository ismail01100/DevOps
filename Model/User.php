<?php
class User {
    private $CodeUtilisateur;
    private $Fullname;
    private $Email;
    private $Password;

    // Getters
    public function getCodeUtilisateur() {
        return $this->CodeUtilisateur;
    }

    public function getFullname() {
        return $this->Fullname;
    }

    public function getEmail() {
        return $this->Email;
    }

    public function getPassword() {
        return $this->Password;
    }

    // Setters
    public function setCodeUtilisateur($CodeUtilisateur) {
        $this->CodeUtilisateur = $CodeUtilisateur;
    }

    public function setFullname($Fullname) {
        $this->Fullname = $Fullname;
    }

    public function setEmail($Email) {
        $this->Email = $Email;
    }

    public function setPassword($Password) {
        $this->Password = $Password;
    }
}
?>
