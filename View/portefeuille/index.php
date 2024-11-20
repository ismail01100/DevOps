<!DOCTYPE html>
<html>

<head>
    <title>My Portfolio</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>
    <div class="nav-bar">
        <div>
            <h1>My Portfolio</h1>
            <!-- <p class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['user']['Fullname'] ?? 'User'); ?> -->
            </p>
        </div>
        <div>
            <a href="index.php?controller=portefeuille&action=settings" class="action-button">Settings</a>
            <a href="index.php?controller=user&action=logout" class="action-button">Logout</a>
        </div>
    </div>

    <?php if (isset($firstTime) && $firstTime): ?>
        <div class="card">
            <div class="alert">
                <p>Welcome! Please set your initial salary to get started.</p>
                <form method="post" action="index.php?controller=portefeuille&action=updateSalary">
                    <div>
                        <label>Enter your salary:</label>
                        <input type="number" name="Salaire" required>
                    </div>
                    <button type="submit" class="action-button">Set</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Portfolio Details</h2>
        <p>Name: <?php echo htmlspecialchars($_SESSION['user']['Fullname'] ?? 'Unknown'); ?></p>
        <p>Balance: <?php
        $withSaving = ($portefeuille['TotalIncome'] * (1 - ($portefeuille['SavingPourcentage'] ?? 0) / 100)) - $totalCharges;
        $_SESSION['user']['BalanceWithSaving'] = $withSaving;
        echo htmlspecialchars($withSaving ?? 0); ?> DH
            (<?php echo htmlspecialchars($portefeuille['TotalIncome'] - $totalCharges ?? 0); ?> DH without saving)</p>
        <p>Total Charges for this month: <?php echo htmlspecialchars($totalCharges ?? 0); ?> DH</p>
    </div>

    <?php if (isset($savingsWarning) && $savingsWarning !== null && $savingsWarning['hasVariableCharges']): ?>
        <div class="card">
            <div class="alert">
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
                                <td><?php echo htmlspecialchars(number_format($charge['HistoricalMin'] ?? $charge['CurrentMontant'], 2)); ?>
                                    DH</td>
                                <td>
                                    <?php echo htmlspecialchars(number_format($charge['suggestedAmount'], 2)); ?> DH
                                    <?php if ($charge['additionalReduction'] > 0): ?>
                                        <br><small>(Additional <?php echo $charge['additionalReduction']; ?>% reduction
                                            needed)</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(number_format($charge['CurrentMontant'] - $charge['suggestedAmount'], 2)); ?>
                                    DH</td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="2"><strong>Total</strong></td>
                            <td><strong><?php echo htmlspecialchars(number_format($savingsWarning['totalCurrent'], 2)); ?>
                                    DH</strong></td>
                            <td><strong><?php echo htmlspecialchars(number_format($savingsWarning['totalAfterHistoricalMin'], 2)); ?>
                                    DH</strong></td>
                            <td><strong><?php echo htmlspecialchars(number_format($savingsWarning['totalFinal'], 2)); ?>
                                    DH</strong></td>
                            <td><strong><?php echo htmlspecialchars(number_format($savingsWarning['totalCurrent'] - $savingsWarning['totalFinal'], 2)); ?>
                                    DH</strong></td>
                        </tr>
                    </tbody>
                </table>

                <p>
                    <strong>Total reduction needed:
                        <?php echo htmlspecialchars($savingsWarning['totalReductionPercentage']); ?>%</strong>
                    <?php if ($savingsWarning['additionalReductionNeeded'] > 0): ?>
                        <br>Even after reducing to historical minimums, an additional
                        <?php echo htmlspecialchars($savingsWarning['additionalReductionNeeded']); ?>% reduction is needed.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Recent Charges</h2>
        <table>
            <thead>
                <tr>
                    <th>NomCharge</th>
                    <th>Montant</th>
                    <th>DateCharge</th>
                    <th>Variable</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentCharges as $charge): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($charge['NomCharge']); ?></td>
                        <td><?php echo htmlspecialchars($charge['Montant']); ?></td>
                        <td><?php echo htmlspecialchars($charge['DateCharge']); ?></td>
                        <td><?php echo htmlspecialchars($charge['Variable'] == 1 ? 'Variable' : 'Fixe'); ?></td>
                        <td><?php echo htmlspecialchars($charge['Description']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="index.php?controller=charges&action=index" class="action-button">View All Charges</a>
    </div>
</body>

</html>