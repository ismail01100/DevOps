<?php
include_once 'Database.php';
include_once 'Portefeuille.php';
include_once 'PortefeuilleController.php';

$database = new Database();
$db = $database->getConnection();
$portefeuilleController = new PortefeuilleController($db);

$portefeuilleDetails = $portefeuilleController->consulterPortefeuille();

if ($portefeuilleDetails) {
    echo "<h2>Portfolio Details</h2>";
    echo "Total Charges: " . $portefeuilleDetails['totalCharges'] . " DH<br>";
    echo "Remaining Balance: " . $portefeuilleDetails['reste'] . " DH<br>";
    echo "Suggested Reduction for Savings: " . $portefeuilleDetails['tauxReduction'] . "%<br>";
} else {
    echo "No portfolio found.";
}
?>
