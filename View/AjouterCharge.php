<?php
include_once 'Database.php';
include_once 'Charge.php';
include_once 'ChargeController.php';

$database = new Database();
$db = $database->getConnection();

if ($_POST) {
    $chargeController = new ChargeController($db);
    $description = $_POST['description'];
    $montant = $_POST['montant'];
    $date = $_POST['date'];
    $variable = isset($_POST['variable']) ? 1 : 0;

    if ($chargeController->ajouterCharge($description, $montant, $date, $variable)) {
        echo "Charge added successfully!";
    } else {
        echo "Error adding charge.";
    }
}
?>

<form method="post">
    Description: <input type="text" name="description"><br>
    Montant: <input type="number" name="montant" step="0.01"><br>
    Date: <input type="date" name="date"><br>
    Variable: <input type="checkbox" name="variable" value="1"><br>
    <input type="submit" value="Add Charge">
</form>
