<?php
require_once 'Model/Portefeuille.php';
require_once 'Model/DatabaseConnection.php';

class PortefeuilleController {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    public function index() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM portefeuille WHERE CodeUtilisateur = :userId");
            $stmt->execute([':userId' => $_SESSION['user']['CodeUtilisateur']]);
            $portefeuille = $stmt->fetch(PDO::FETCH_ASSOC);

            // Create new portfolio if none exists
            if (!$portefeuille) {
                $stmt = $this->db->prepare("INSERT INTO portefeuille (CodeUtilisateur, Salaire, Solde) VALUES (:userId, 0, 0)");
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
            
            require 'View/portefeuille/index.php';
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function updateSalary($data) {
        try {
            if(isset($data['Salaire']) && !empty($data['Salaire'])) {
                //get old salary
                $stmt = $this->db->prepare("SELECT Salaire FROM portefeuille WHERE CodePortefeuille = :id");
                $stmt->execute([':id' => $_SESSION['user']['CodePortefeuille']]);
                $oldSalaire = $stmt->fetch(PDO::FETCH_ASSOC)['Salaire'];
                // Update salary
                $stmt = $this->db->prepare("UPDATE portefeuille SET Salaire = :salaire WHERE CodePortefeuille = :id");
                $stmt->execute([
                    ':salaire' => $data['Salaire'],
                    ':id' => $_SESSION['user']['CodePortefeuille']
                ]);
                // calculate the difference between the old salary and the new salary and update the total income
                if(isset($oldSalaire) && !empty($oldSalaire)){
                    $difference = $data['Salaire'] - $oldSalaire;
                }else{
                    $difference = $data['Salaire'];
                    $stmt = $this->db->prepare("UPDATE portefeuille SET balance = :salaire WHERE CodePortefeuille = :id");
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
            header('Location: index.php?controller=portefeuille&action=index');
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function addIncome($data) {
        try {
            if(isset($data['Bonus']) && !empty($data['Bonus'])) {
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
            header('Location: index.php?controller=portefeuille&action=index');
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function resetBalance() {
        try {
            $stmt = $this->db->prepare("SELECT Salaire FROM portefeuille WHERE CodePortefeuille = :id");
            $stmt->execute([':id' => $_SESSION['user']['CodePortefeuille']]);
            $salaire = $stmt->fetch(PDO::FETCH_ASSOC)['Salaire'];
            $stmt = $this->db->prepare("UPDATE portefeuille SET Solde = :salaire, TotalIncome = :salaire WHERE CodePortefeuille = :id");
            $stmt->execute([
                ':salaire' => $salaire,
                ':id' => $_SESSION['user']['CodePortefeuille']
            ]);
            header('Location: index.php?controller=portefeuille&action=index');
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    private function checkAndResetBalance($portefeuille) {
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

    public function updateSavingPourcentage($data) {
        try {
            $stmt = $this->db->prepare("UPDATE portefeuille SET SavingPourcentage = :savingPourcentage WHERE CodePortefeuille = :id");
            $stmt->execute([
                ':savingPourcentage' => $data['SavingPourcentage'],
                ':id' => $_SESSION['user']['CodePortefeuille']
            ]);
            header('Location: index.php?controller=portefeuille&action=index');
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function calculateBudgetAndExpenses($portefeuille) {
        // Get total income (salary + bonuses)
        $totalIncome = $portefeuille['Solde'];
        
        // Calculate available budget (80% of income)
        $availableBudget = $totalIncome * (1 - ($portefeuille['SavingPourcentage'] / 100));
        
        // Get fixed and variable charges
        $stmt = $this->db->prepare("
            SELECT 
                SUM(CASE WHEN Variable = 0 THEN Montant ELSE 0 END) as fixed_charges,
                SUM(CASE WHEN Variable = 1 THEN Montant ELSE 0 END) as variable_charges
            FROM charges 
            WHERE CodePortefeuille = :codePortefeuille 
            AND MONTH(DateCharge) = MONTH(CURRENT_DATE())
            AND YEAR(DateCharge) = YEAR(CURRENT_DATE())
        ");
        
        $stmt->execute([':codePortefeuille' => $portefeuille['CodePortefeuille']]);
        $charges = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'availableBudget' => $availableBudget,
            'fixedCharges' => $charges['fixed_charges'] ?? 0,
            'variableCharges' => $charges['variable_charges'] ?? 0
        ];
    }

    public function calculateSuggestedReduction($budget, $fixedCharges, $variableCharges) {
        $totalCharges = $fixedCharges + $variableCharges;
        
        if ($totalCharges <= $budget) {
            return 0; // No reduction needed
        }
        
        // Calculate how much we need to reduce
        $excess = $totalCharges - $budget;
        
        // Calculate reduction percentage needed on variable charges
        $reductionPercentage = ($excess / $variableCharges) * 100;
        
        return ceil($reductionPercentage); // Round up to nearest integer
    }

    public function getSavingsWarning($withSaving, $portefeuille) {
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