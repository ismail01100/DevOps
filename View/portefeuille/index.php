<!DOCTYPE html>
<html>
<head>
    <title>My Portfolio</title>
</head>
<body>
    <h1>My Portfolio</h1>
    <div>
        <h2>Portfolio Details</h2>
        <p>Name: <?php echo htmlspecialchars($_SESSION['user']['Fullname'] ?? 'Unknown'); ?></p>
        <p>Balance: <?php echo htmlspecialchars($portefeuille['Solde'] ?? 0); ?> DH</p>
        <p>Total Charges for this month: <?php echo htmlspecialchars($totalCharges ?? 0); ?> DH</p>
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