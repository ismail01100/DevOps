<!DOCTYPE html>
<html>
<head>
    <title>My Portfolio</title>
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-warning {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeeba;
        }
    </style>
</head>
<body>
    <h1>My Portfolio</h1>
    <?php if (isset($firstTime) && $firstTime): ?>
        <div class="alert alert-warning">
            <p>Welcome! Please set your initial salary to get started.</p>
            <form method="post" action="index.php?controller=portefeuille&action=updateSalary">
                <div>
                    <label>Enter your salary:</label>
                    <input type="number" name="Salaire" required>
                </div>
                <button type="submit">Set</button>
            </form>
        </div>
    <?php endif; ?>
    <div>
        <h2>Portfolio Details</h2>
        <p>Name: <?php echo htmlspecialchars($_SESSION['user']['Fullname'] ?? 'Unknown'); ?></p>
        <p>Balance: <?php 
            $withSaving = ($portefeuille['TotalIncome'] * (1 - ($portefeuille['SavingPourcentage'] ?? 0) / 100)) - $totalCharges;
            $_SESSION['user']['BalanceWithSaving'] = $withSaving;
            echo htmlspecialchars($withSaving ?? 0); ?> DH (<?php echo htmlspecialchars($portefeuille['Solde'] ?? 0); ?> DH without saving)</p>
        <p>Total Charges for this month: <?php echo htmlspecialchars($totalCharges ?? 0); ?> DH</p>
    </div>
    <?php if (isset($savingsWarning) && $savingsWarning !== null && $savingsWarning['hasVariableCharges']): ?>
        <div class="alert alert-warning">
            <p><strong>Warning:</strong> Your expenses exceed your available budget.</p>
            <p>Suggested reductions based on your historical spending:</p>
            
            <table>
                <thead>
                    <tr>
                        <th>Charge Name</th>
                        <th>Description</th>
                        <th>Current Amount</th>
                        <th>Historical Minimum</th>
                        <th>Suggested Amount</th>
                        <th>Total Reduction</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($savingsWarning['variableCharges'] as $charge): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($charge['NomCharge']); ?></td>
                        <td><?php echo htmlspecialchars($charge['Description']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($charge['CurrentMontant'], 2)); ?> DH</td>
                        <td><?php echo htmlspecialchars(number_format($charge['HistoricalMin'] ?? $charge['CurrentMontant'], 2)); ?> DH</td>
                        <td>
                            <?php echo htmlspecialchars(number_format($charge['suggestedAmount'], 2)); ?> DH
                            <?php if ($charge['additionalReduction'] > 0): ?>
                                <br><small>(Additional <?php echo $charge['additionalReduction']; ?>% reduction needed)</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars(number_format($charge['CurrentMontant'] - $charge['suggestedAmount'], 2)); ?> DH</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="2"><strong>Total</strong></td>
                        <td><strong><?php echo htmlspecialchars(number_format($savingsWarning['totalCurrent'], 2)); ?> DH</strong></td>
                        <td><strong><?php echo htmlspecialchars(number_format($savingsWarning['totalAfterHistoricalMin'], 2)); ?> DH</strong></td>
                        <td><strong><?php echo htmlspecialchars(number_format($savingsWarning['totalFinal'], 2)); ?> DH</strong></td>
                        <td><strong><?php echo htmlspecialchars(number_format($savingsWarning['totalCurrent'] - $savingsWarning['totalFinal'], 2)); ?> DH</strong></td>
                    </tr>
                </tbody>
            </table>
            
            <p>
                <strong>Total reduction needed: <?php echo htmlspecialchars($savingsWarning['totalReductionPercentage']); ?>%</strong>
                <?php if ($savingsWarning['additionalReductionNeeded'] > 0): ?>
                    <br>Even after reducing to historical minimums, an additional <?php echo htmlspecialchars($savingsWarning['additionalReductionNeeded']); ?>% reduction is needed.
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
    <div>
        <h2>Profile</h2>
        <p>Salary: <?php echo htmlspecialchars($portefeuille['Salaire'] ?? 0); ?> DH</p>
        <p>Saving Pourcentage: <?php echo htmlspecialchars($portefeuille['SavingPourcentage'] ?? 0); ?>%</p>
        <form method="post" action="index.php?controller=portefeuille&action=updateSavingPourcentage">
            <div>
                <label>Update your saving pourcentage:</label>
                <input type="number" name="SavingPourcentage" value="<?php echo $portefeuille['SavingPourcentage']; ?>" required>
            </div>
            <button type="submit">Update</button>
        </form>
        <p>Last Reset Date: <?php echo htmlspecialchars($portefeuille['LastResetDate'] ?? 'Unknown'); ?></p>
        <form method="post" action="index.php?controller=portefeuille&action=resetBalance">
            <button type="submit">Reset my balance</button>
        </form>
        <form method="post" action="index.php?controller=portefeuille&action=updateSalary">
            <div>
                <label>Update your salary:</label>
                <input type="number" name="Salaire" value="<?php echo $portefeuille['Salaire']; ?>" required>
            </div>
            <button type="submit">Update</button>
        </form>
        <form method="post" action="index.php?controller=portefeuille&action=addIncome">
            <div>
                <label>Add a bonus to your balance:</label>
                <input type="number" name="Bonus" value="0" required>
            </div>
            <button type="submit">Update</button>
        </form>
            <a href="index.php?controller=user&action=logout">Logout</a>
    </div>
    <div>
        <h2>Recent Charges</h2>
        <table>
                <thead>
                    <tr>
                        <th>NomCharge</th>
                        <th>Montant</th>
                        <th>DateCharge</th>
                        <th>Variable</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($recentCharges as $charge): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($charge['NomCharge']); ?></td>
                        <td><?php echo htmlspecialchars($charge['Montant']); ?></td>
                        <td><?php echo htmlspecialchars($charge['DateCharge']); ?></td>
                        <td><?php echo htmlspecialchars($charge['Variable'] == 1 ? 'Variable' : 'Fixe'); ?></td>
                        <td><?php echo htmlspecialchars($charge['Description']); ?></td>
                        <td>
                            <a href="index.php?controller=charges&action=delete&id=<?php echo $charge['CodeCharge']; ?>">Delete</a>
                            <a href="index.php?controller=charges&action=edit&id=<?php echo $charge['CodeCharge']; ?>">Edit</a>
                            <a href="index.php?controller=charges&action=show&id=<?php echo $charge['CodeCharge']; ?>">Show</a>
                        </td>
                    </tr>
        <?php endforeach; ?>
            </tbody>
        </table>
        <a href="index.php?controller=charges&action=index">View Charges</a>
    </div>
</body>
</html>