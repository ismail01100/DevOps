<!DOCTYPE html>
<html>

<head>
  <title>Settings - My Portfolio</title>
  <!-- <link rel="stylesheet" href="/assets/css/style.css"> -->
  <style>
    body {
      background: -webkit-linear-gradient(bottom, #2dbd6e, #a6f77b);
      background-repeat: no-repeat;
      font-family: "Raleway", sans-serif;
      min-height: 100vh;
      margin: 0;
      padding: 20px;
      
      margin: auto;
            max-width: 800px;
            min-width: 800px;
    }

    .card {
      background: #fbfbfb;
      border-radius: 8px;
      box-shadow: 1px 2px 8px rgba(0, 0, 0, 0.65);
      margin: 20px auto;
      padding: 20px;
      max-width: 500px;
    }

    .nav-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px;
      background: #fbfbfb;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .welcome-text {
      color: #2c3e50;
      font-family: "Raleway", sans-serif;
      margin: 0;
      font-size: 14px;
    }

    h1,
    h2 {
      font-family: "Raleway Thin", sans-serif;
      letter-spacing: 2px;
      text-align: center;
      color: #2c3e50;
      margin-bottom: 20px;
    }

    .action-button {
      background: -webkit-linear-gradient(right, #a6f77b, #2dbd6e);
      border: none;
      border-radius: 21px;
      box-shadow: 0px 1px 8px #24c64f;
      cursor: pointer;
      color: white;
      font-family: "Raleway SemiBold", sans-serif;
      padding: 10px 20px;
      transition: 0.25s;
      display: inline-block;
      margin: 5px;
      text-decoration: none;
    }

    .action-button:hover {
      box-shadow: 0px 1px 18px #24c64f;
      color: white;
    }

    .form-group {
      margin-bottom: 25px;
      background: #fff;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: #2c3e50;
      font-weight: 500;
    }

    .form-group input {
      width: 100%;
      padding: 10px;
      padding-right:0px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-family: "Raleway", sans-serif;
      transition: border-color 0.3s;
    }

    .form-group input:focus {
      border-color: #2dbd6e;
      outline: none;
    }

    .settings-section {
      margin-bottom: 30px;
    }

    .settings-section h2 {
      color: #2c3e50;
      font-size: 1.5em;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 2px solid #2dbd6e;
    }

    .info-text {
      color: #666;
      font-size: 0.9em;
      margin-top: 5px;
    }

    .danger-zone {
      border-top: 1px solid #ddd;
      margin-top: 30px;
      padding-top: 20px;
    }

    .danger-button {
      background: #dc3545;
      border: none;
      border-radius: 21px;
      color: white;
      cursor: pointer;
      padding: 10px 20px;
      transition: 0.25s;
    }

    .danger-button:hover {
      background: #c82333;
      box-shadow: 0px 1px 8px rgba(220, 53, 69, 0.5);
    }

    .profile-info {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .profile-info p {
      margin: 8px 0;
      color: #2c3e50;
    }

    .profile-info strong {
      color: #2dbd6e;
    }
  </style>
</head>

<body>
  <div class="nav-bar">
    <div>
      <h1>Settings</h1>
      <!-- <p class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['user']['Fullname'] ?? 'User'); ?></p> -->
    </div>
    <div>
      <a href="index.php?controller=portefeuille&action=index" class="action-button">Back to Dashboard</a>
      <a href="index.php?controller=user&action=logout" class="action-button">Logout</a>
    </div>
  </div>

  <div class="card">
    <div class="settings-section">
      <h2>Profile Information</h2>
      <div class="profile-info">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['user']['Fullname'] ?? 'Unknown'); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user']['Email'] ?? 'Unknown'); ?></p>
      </div>
    </div>

    <div class="settings-section">
      <h2>Financial Settings</h2>
      <form method="post" action="index.php?controller=portefeuille&action=updateSavingPourcentage" class="form-group">
        <label>Saving Percentage:</label>
        <input type="number" name="SavingPourcentage"
          value="<?php echo htmlspecialchars($profile['SavingPourcentage'] ?? 0); ?>" required>
        <p class="info-text">Current: <?php echo htmlspecialchars($profile['SavingPourcentage'] ?? 0); ?>%</p>
        <button type="submit" class="action-button">Update</button>
      </form>

      <form method="post" action="index.php?controller=portefeuille&action=updateSalary" class="form-group">
        <label>Monthly Salary:</label>
        <input type="number" name="Salaire" value="<?php echo htmlspecialchars($profile['Salaire'] ?? 0); ?>" required>
        <p class="info-text">Current: <?php echo htmlspecialchars($profile['Salaire'] ?? 0); ?> DH</p>
        <button type="submit" class="action-button">Update</button>
      </form>

      <form method="post" action="index.php?controller=portefeuille&action=addIncome" class="form-group">
        <label>Add Bonus Income:</label>
        <input type="number" name="Bonus" value="0" required>
        <p class="info-text">One-time addition to your balance</p>
        <button type="submit" class="action-button">Add Bonus</button>
      </form>
    </div>

    <div class="danger-zone">
      <h2>Danger Zone</h2>
      <form method="post" action="index.php?controller=portefeuille&action=resetBalance" class="form-group">
        <p class="info-text">This will reset your current balance. This action cannot be undone.</p>
        <button type="submit" class="danger-button">Reset Balance</button>
      </form>
    </div>
  </div>
</body>

</html>