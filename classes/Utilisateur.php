<?php
class Utilisateur {
    private $conn;
    private $table_name = "Utilisateur";

    public $id;
    public $nom;
    public $email;
    public $salaire;
    public $password;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET Nom=:nom, Email=:email, Salaire=:salaire, Password=:password";
        $stmt = $this->conn->prepare($query);

        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":salaire", $this->salaire);
        $stmt->bindParam(":password", $this->password);

        return $stmt->execute();
    }

    public function login($email, $password) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE Email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['Password'])) {
                $this->id = $row['id'];
                $this->nom = $row['Nom'];
                $this->email = $row['Email'];
                $this->salaire = $row['Salaire'];
                return true;
            }
        }
        return false;
    }

    public function consulterPortefeuille() {
        $query = "SELECT * FROM Portefeuille WHERE UtilisateurID = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
