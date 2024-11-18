<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h1>Register</h1>
    <form method="post" action="index.php?controller=user&action=register">
        <div>
            <label>Full Name:</label>
            <input type="text" name="Fullname" required>
        </div>
        <div>
            <label>Email:</label>
            <input type="email" name="Email" required>
        </div>
        <div>
            <label>Password:</label>
            <input type="password" name="Password" required>
        </div>
        <button type="submit">Register</button>
    </form>
</body>
</html>
