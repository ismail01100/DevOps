<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <!-- <link rel="stylesheet" href="/assets/css/style.css"> -->
    <style>
        a {
            text-decoration: none;
        }

        body {
            background: -webkit-linear-gradient(bottom, #2dbd6e, #a6f77b);
            background-repeat: no-repeat;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        label {
            font-family: "Raleway", sans-serif;
            font-size: 11pt;
        }

        .card {
            background: #fbfbfb;
            border-radius: 8px;
            box-shadow: 1px 2px 8px rgba(0, 0, 0, 0.65);
            height: 430px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 6rem auto 8.1rem auto;
            width: 329px;
        }

        .card-content {
            flex-grow: 1;
            padding: 12px 44px;
        }

        .alert {
    background: #fff3cd;
    color: #856404;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    border: none;
    box-shadow: 1px 2px 8px rgba(0, 0, 0, 0.1);
    font-family: "Raleway", sans-serif;
    font-size: 0.9em;
    text-align: center;
}

.alert.success {
    background: #d4edda;
    color: #155724;
}

        .card-title {
            font-family: "Raleway Thin", sans-serif;
            letter-spacing: 4px;
            padding-bottom: 23px;
            padding-top: 13px;
            text-align: center;
        }

        .register-link {
            color: #2dbd6e;
            font-family: "Raleway", sans-serif;
            font-size: 10pt;
            margin-top: 16px;
            text-align: center;
            display: block;
        }

        button[type="submit"] {
            background: -webkit-linear-gradient(right, #a6f77b, #2dbd6e);
            border: none;
            border-radius: 21px;
            box-shadow: 0px 1px 8px #24c64f;
            cursor: pointer;
            color: white;
            font-family: "Raleway SemiBold", sans-serif;
            height: 42.3px;
            margin: 0 auto;
            margin-top: 50px;
            transition: 0.25s;
            width: 153px;
            display: block;
        }

        button[type="submit"]:hover {
            box-shadow: 0px 1px 18px #24c64f;
        }

        .input-container {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        input[type="email"],
        input[type="password"] {
            background: #fbfbfb;
            border: none;
            outline: none;
            padding-top: 14px;
            border-bottom: 1px solid;
            border-image: -webkit-linear-gradient(right, #a6f77b, #2ec06f) 1;
        }

        .underline-title {
            background: -webkit-linear-gradient(right, #a6f77b, #2ec06f);
            height: 2px;
            margin: -1.1rem auto 0 auto;
            width: 89px;
        }
    </style>
</head>

<body>

    <div class="card">
        <div class="card-content">
            <div class="card-title">
                <h2>LOGIN</h2>
                <div class="underline-title"></div>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert">
                    <?php 
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']); 
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success">
                    <?php 
                        echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']); 
                    ?>
                </div>
            <?php endif; ?>

            <form method="post" action="index.php?controller=user&action=login">
                <div class="input-container">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="input-container">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit">Login</button>
            </form>
            <a class="register-link" href="index.php?controller=user&action=register">Register</a>
        </div>
    </div>
</body>

</html>