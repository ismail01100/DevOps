<?php
require_once 'Model/Portefeuille.php';
require_once 'Model/DatabaseConnection.php';

class PortefeuilleController
{
    private $db;
    private $isTestMode;

    public function __construct($isTestMode = false)
    {
        $this->db = DatabaseConnection::getInstance()->getConnection();
        $this->isTestMode = $isTestMode;
    }

    public function index()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM portefeuille WHERE CodeUtilisateur = :userId");
            $stmt->execute([':userId' => $_SESSION['user']['CodeUtilisateur']]);
            $portefeuille = $stmt->fetch(PDO::FETCH_ASSOC);

            // Create new portfolio if none exists
            if (!$portefeuille) {
                $stmt = $this->db->prepare("INSERT INTO portefeuille (CodeUtilisateur) VALUES (:userId)");
                $stmt->execute([':userId' => $_SESSION['user']['CodeUtilisateur']]);
                // Fetch the newly created portfolio
                $stmt = $this->db->prepare("SELECT * FROM portefeuille WHERE CodeUtilisateur = :userId");
                $stmt->execute([':userId' => $_SESSION['user']['CodeUtilisateur']]);
                $portefeuille = $stmt->fetch(PDO::FETCH_ASSOC);
                $firstTime = true;
            }
            // Check and reset balance if needed
            $wasReset = $this->checkAndResetBalance($portefeuille);
            if ($wasReset) {
                // Refresh portfolio data after reset
                $stmt = $this->db->prepare("SELECT * FROM portefeuille WHERE CodeUtilisateur = :userId");
                $stmt->execute([':userId' => $_SESSION['user']['CodeUtilisateur']]);
                $portefeuille = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Get recent charges
            $stmt = $this->db->prepare("SELECT * FROM charges WHERE CodePortefeuille = :codePortefeuille ORDER BY DateCharge DESC LIMIT 3");
            $stmt->execute([':codePortefeuille' => $portefeuille['CodePortefeuille']]);
            $recentCharges = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total charges for current month
            $startDate = date('Y-m-01'); // First day of current month
            $endDate = date('Y-m-t'); // Last day of current month
            $stmt = $this->db->prepare("SELECT SUM(Montant) AS TotalCharges FROM charges WHERE CodePortefeuille = :codePortefeuille AND DateCharge >= :startDate AND DateCharge <= :endDate");
            $stmt->execute([':codePortefeuille' => $portefeuille['CodePortefeuille'], ':startDate' => $startDate, ':endDate' => $endDate]);
            $totalCharges = $stmt->fetch(PDO::FETCH_ASSOC)['TotalCharges'] ?? 0;

            $_SESSION['user']['CodePortefeuille'] = $portefeuille['CodePortefeuille'];
            $_SESSION['user']['SavingPourcentage'] = $portefeuille['SavingPourcentage'];

            $withSaving = ($portefeuille['TotalIncome'] * (1 - ($portefeuille['SavingPourcentage'] ?? 0) / 100)) - $totalCharges;
            $savingsWarning = $this->getSavingsWarning($withSaving, $portefeuille);
            if($portefeuille['Salaire'] == 0){
                $firstTime = true;
            }
            require 'View/portefeuille/index.php';
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function settings()
    {
        // needs salary , saving pourcentage, last reset date
        $stmt = $this->db->prepare("SELECT Salaire, SavingPourcentage, LastResetDate FROM portefeuille WHERE CodePortefeuille = :id");
        $stmt->execute([':id' => $_SESSION['user']['CodePortefeuille']]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        require 'View/portefeuille/settings.php';
    }

    public function updateSalary($data)
    {
        try {
            if (isset($data['Salaire']) && $data['Salaire'] !== null && $data['Salaire'] >= 0) {
                $stmt = $this->db->prepare("SELECT Salaire FROM portefeuille WHERE CodePortefeuille = :id");
                $stmt->execute([':id' => $_SESSION['user']['CodePortefeuille']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $oldSalaire = $result ? $result['Salaire'] : 0;

                $stmt = $this->db->prepare("UPDATE portefeuille SET Salaire = :salaire WHERE CodePortefeuille = :id");
                $stmt->execute([
                    ':salaire' => $data['Salaire'],
                    ':id' => $_SESSION['user']['CodePortefeuille']
                ]);
                $difference = 0;
                if ($oldSalaire !== null) {
                    $difference = $data['Salaire'] - $oldSalaire;
                } else {
                    $difference = $data['Salaire'];
                    $stmt = $this->db->prepare("UPDATE portefeuille SET Solde = :salaire WHERE CodePortefeuille = :id");
                    $stmt->execute([
                        ':salaire' => $data['Salaire'],
                        ':id' => $_SESSION['user']['CodePortefeuille']
                    ]);
                }

                $stmt = $this->db->prepare("UPDATE portefeuille SET TotalIncome = TotalIncome + :difference WHERE CodePortefeuille = :id");
                $stmt->execute([
                    ':difference' => $difference,
                    ':id' => $_SESSION['user']['CodePortefeuille']
                ]);
            }
            if (!$this->isTestMode) {
                header('Location: index.php?controller=portefeuille&action=index');
            }
            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function addIncome($data)
    {
        if($data['Bonus'] <= 0){
            return false;
        }
        try {
            if (isset($data['Bonus']) && !empty($data['Bonus'])) {
                $stmt = $this->db->prepare("SELECT TotalIncome, Solde FROM portefeuille WHERE CodePortefeuille = :id");
                $stmt->execute([':id' => $_SESSION['user']['CodePortefeuille']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $TotalIncome = $result['TotalIncome'];
                $oldSolde = $result['Solde'];
                $newSolde = $oldSolde + $data['Bonus'];
                $newTotalIncome = $TotalIncome + $data['Bonus'];
                $stmt = $this->db->prepare("UPDATE portefeuille SET TotalIncome = :TotalIncome, Solde = :Solde WHERE CodePortefeuille = :id");
                $stmt->execute([
                    ':TotalIncome' => $newTotalIncome,
                    ':Solde' => $newSolde,
                    ':id' => $_SESSION['user']['CodePortefeuille']
                ]);

            }
            if (!$this->isTestMode) {
                header('Location: index.php?controller=portefeuille&action=index');
            }
            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function resetBalance()
    {
        try {
            $stmt = $this->db->prepare("SELECT Salaire FROM portefeuille WHERE CodePortefeuille = :id");
            $stmt->execute([':id' => $_SESSION['user']['CodePortefeuille']]);
            $salaire = $stmt->fetch(PDO::FETCH_ASSOC)['Salaire'];
            $stmt = $this->db->prepare("UPDATE portefeuille SET Solde = :salaire, TotalIncome = :salaire WHERE CodePortefeuille = :id");
            $stmt->execute([
                ':salaire' => $salaire,
                ':id' => $_SESSION['user']['CodePortefeuille']
            ]);
            if (!$this->isTestMode) {
                header('Location: index.php?controller=portefeuille&action=index');
            }
            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function checkAndResetBalance($portefeuille)
    {
        // Get the last reset date from the database or session
        $stmt = $this->db->prepare("SELECT LastResetDate FROM portefeuille WHERE CodePortefeuille = :id");
        $stmt->execute([':id' => $portefeuille['CodePortefeuille']]);
        $lastResetDate = $stmt->fetch(PDO::FETCH_ASSOC)['LastResetDate'];

        $today = new DateTime();
        $firstDayOfMonth = new DateTime('first day of this month');

        // If last reset date is null or from previous month, perform reset
        if (!$lastResetDate || (new DateTime($lastResetDate))->format('Y-m') < $today->format('Y-m')) {
            $stmt = $this->db->prepare("UPDATE portefeuille SET Solde = Salaire, TotalIncome = Salaire, LastResetDate = :today WHERE CodePortefeuille = :id");
            $stmt->execute([
                ':today' => $today->format('Y-m-d'),
                ':id' => $portefeuille['CodePortefeuille']
            ]);
            return true;
        }
        return false;
    }

    public function updateSavingPourcentage($data)
    {
        if($data['SavingPourcentage'] < 0 || $data['SavingPourcentage'] > 100){
            return false;
        }
        try {
            $stmt = $this->db->prepare("UPDATE portefeuille SET SavingPourcentage = :savingPourcentage WHERE CodePortefeuille = :id");
            $stmt->execute([
                ':savingPourcentage' => $data['SavingPourcentage'],
                ':id' => $_SESSION['user']['CodePortefeuille']
            ]);
            if (!$this->isTestMode) {
                header('Location: index.php?controller=portefeuille&action=index');
            }
            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function getSavingsWarning($withSaving, $portefeuille)
    {
        if ($withSaving >= 0) {
            return null;
        }

        // Get all current variable charges with their minimum historical amounts
        $stmt = $this->db->prepare("
            SELECT 
                c1.NomCharge,
                c1.Montant as CurrentMontant,
                c1.Description,
                (
                    SELECT MIN(c2.Montant)
                    FROM charges c2 
                    WHERE c2.NomCharge = c1.NomCharge 
                    AND c2.CodePortefeuille = c1.CodePortefeuille
                    AND c2.Variable = 1
                    AND c2.DateCharge < c1.DateCharge
                ) as HistoricalMin
            FROM charges c1
            WHERE c1.CodePortefeuille = :codePortefeuille 
            AND c1.Variable = 1
            AND MONTH(c1.DateCharge) = MONTH(CURRENT_DATE())
            AND YEAR(c1.DateCharge) = YEAR(CURRENT_DATE())
        ");

        $stmt->execute([':codePortefeuille' => $portefeuille['CodePortefeuille']]);
        $variableCharges = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($variableCharges)) {
            return ['hasVariableCharges' => false];
        }

        // First step: Calculate savings if we reduce to historical minimums
        $totalCurrentCharges = 0;
        $totalAfterHistoricalMin = 0;
        $reducedCharges = [];

        foreach ($variableCharges as $charge) {
            $currentAmount = $charge['CurrentMontant'];
            $historicalMin = $charge['HistoricalMin'] ?? $currentAmount;

            $totalCurrentCharges += $currentAmount;
            $totalAfterHistoricalMin += $historicalMin;

            $charge['suggestedAmount'] = $historicalMin;
            $charge['additionalReduction'] = 0;
            $reducedCharges[] = $charge;
        }

        // Check if reducing to historical minimums is enough
        $remainingDeficit = abs($withSaving);
        $additionalReductionNeeded = 0;

        if ($totalAfterHistoricalMin > ($totalCurrentCharges - $remainingDeficit)) {
            // Calculate additional reduction needed
            $excessAfterHistoricalMin = $totalAfterHistoricalMin - ($totalCurrentCharges - $remainingDeficit);
            $additionalReductionNeeded = ceil(($excessAfterHistoricalMin / $totalAfterHistoricalMin) * 100);

            // Apply additional reduction to each charge
            foreach ($reducedCharges as &$charge) {
                $historicalMin = $charge['HistoricalMin'] ?? $charge['CurrentMontant'];
                $charge['suggestedAmount'] = $historicalMin * (1 - ($additionalReductionNeeded / 100));
                $charge['additionalReduction'] = $additionalReductionNeeded;
            }
        }

        $totalFinalReduction = $totalCurrentCharges - array_sum(array_column($reducedCharges, 'suggestedAmount'));
        $totalReductionPercentage = ceil(($totalFinalReduction / $totalCurrentCharges) * 100);

        return [
            'hasVariableCharges' => true,
            'variableCharges' => $reducedCharges,
            'totalCurrent' => $totalCurrentCharges,
            'totalAfterHistoricalMin' => $totalAfterHistoricalMin,
            'totalFinal' => $totalCurrentCharges - $totalFinalReduction,
            'baseReductionMessage' => 'Try reducing to historical minimums first',
            'additionalReductionNeeded' => $additionalReductionNeeded,
            'totalReductionPercentage' => $totalReductionPercentage
        ];
    }
}
?>