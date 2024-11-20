<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <!-- <link rel="stylesheet" href="/assets/css/style.css"> -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        a {
            text-decoration: none;
        }

        body {
            background: -webkit-linear-gradient(bottom, #2dbd6e, #a6f77b);
            background-repeat: no-repeat;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            width: 100vw;
        }

        label {
            font-family: "Raleway", sans-serif;
            font-size: 11pt;
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
        #card {
            background: #fbfbfb;
            border-radius: 8px;
            box-shadow: 1px 2px 8px rgba(0, 0, 0, 0.65);
            height: 480px;
            display: flex;
            align-items: center;
            justify-content: center;    
            /* Slightly taller for register form */
            margin: 6rem auto 8.1rem auto;
            width: 329px;
        }

        #card-content {
            padding: 12px 44px;
            flex-grow: 1;
        }

        #card-title {
            font-family: "Raleway Thin", sans-serif;
            letter-spacing: 4px;
            padding-bottom: 23px;
            padding-top: 13px;
            text-align: center;
        }

        .form {
            align-items: left;
            display: flex;
            flex-direction: column;
        }

        .form-content {
            background: #fbfbfb;
            border: none;
            outline: none;
            padding-top: 14px;
        }

        .form-border {
            background: -webkit-linear-gradient(right, #a6f77b, #2ec06f);
            height: 1px;
            width: 100%;
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
            margin-top: 30px;
            transition: 0.25s;
            width: 153px;
            display: block;
        }

        button[type="submit"]:hover {
            box-shadow: 0px 1px 18px #24c64f;
        }

        .underline-title {
            background: -webkit-linear-gradient(right, #a6f77b, #2ec06f);
            height: 2px;
            margin: -1.1rem auto 0 auto;
            width: 89px;
        }

        .login-link {
            color: #2dbd6e;
            font-family: "Raleway", sans-serif;
            font-size: 11pt;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div id="card">
        <div id="card-content">
            <div id="card-title">
                <h2>REGISTER</h2>
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

            <form method="post" action="index.php?controller=user&action=register" class="form">
                <label for="user-fullname" style="padding-top:13px">&nbsp;Full Name</label>
                <input id="user-fullname" class="form-content" type="text" name="Fullname" required />
                <div class="form-border"></div>

                <label for="user-email" style="padding-top:13px">&nbsp;Email</label>
                <input id="user-email" class="form-content" type="email" name="Email" required />
                <div class="form-border"></div>

                <label for="user-password" style="padding-top:13px">&nbsp;Password</label>
                <input id="user-password" class="form-content" type="password" name="Password" required />
                <div class="form-border"></div>

                <button type="submit">REGISTER</button>
                <a href="index.php?controller=user&action=login" class="login-link">Already have an account?</a>
            </form>
        </div>
    </div>
</body>

</html>