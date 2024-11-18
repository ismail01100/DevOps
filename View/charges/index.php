<!DOCTYPE html>
<html>
<head>
    <title>Charges List</title>
</head>
<body>
    <h1>Charges</h1>
    <a href="index.php?controller=charges&action=create">Add New Charge</a>
    <a href="index.php?controller=portefeuille&action=index">Portfolio</a>
    <table>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Variable</th>
            <th>Actions</th>
        </tr>
        <?php foreach($charges as $charge): ?>
        <tr>
            <td><?php echo htmlspecialchars($charge['NomCharge']); ?></td>
            <td><?php echo htmlspecialchars($charge['Description']); ?></td>
            <td><?php echo htmlspecialchars($charge['Montant']); ?></td>
            <td><?php echo htmlspecialchars($charge['DateCharge']); ?></td>
            <td><?php echo htmlspecialchars($charge['Variable']); ?></td>
            <td>
                <a href="index.php?controller=charges&action=edit&id=<?php echo $charge['CodeCharge']; ?>">Edit</a>
                <a href="index.php?controller=charges&action=delete&id=<?php echo $charge['CodeCharge']; ?>" 
                   onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>