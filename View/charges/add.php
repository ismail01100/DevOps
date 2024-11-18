<!DOCTYPE html>
<html>
<head>
    <title>Add Charge</title>
</head>
<body>
    <h1>Add Charge</h1>
    <form action="index.php?controller=charges&amp;action=create" method="post">
        <div>
            <input type="hidden" name="CodePortefeuille" value="<?php echo htmlspecialchars($_SESSION['user']['CodePortefeuille']); ?>">
        </div>
        <div>
            <input type="text" name="NomCharge" placeholder="Nom Charge" required>
        </div>
        <div>
            <input type="text" name="Description" placeholder="Description" required>
        </div>
        <div>
            <input type="number" name="Montant" placeholder="Montant" step="0.01" required>
        </div>
        <div>
            <input type="date" name="DateCharge" required>
        </div>
        <div>
            <div class="radio-group">
                <input type="radio" id="variable" name="Variable" value="1" required>
                <label for="variable">Variable</label>
                <input type="radio" id="fixe" name="Variable" value="0">
                <label for="fixe">Fixe</label>
            </div>
        </div>
        <div>  
            <button type="submit">Add Charge</button>
        </div>
    </form>
</body>
</html>