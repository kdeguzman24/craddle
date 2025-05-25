<?php
require_once "config.php";

$email = "";
$email_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                header("Location: index.php?forgot_password=true&email=" . urlencode($email));
                exit();
            } else {
                $email_err = "Email not found in our records.";
            }
            $stmt->close();
        } else {
            $email_err = "Something went wrong. Please try again.";
        }
    }
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DreamNest | Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #6A11CB, #2575FC);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
        }

        .container {
            background: rgba(255, 255, 255, 0.15);
            padding: 30px 20px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 90%;
            max-width: 360px;
        }

        .title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .logo-container img {
            width: 50px;
            height: 50px;
            margin-bottom: 10px;
            border-radius: 50%;
            object-fit: cover;
        }

        input[type="email"] {
            width: 95%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            outline: none;
            margin-bottom: 15px;
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #F37335, #FDC830);
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        .login-btn:hover {
            transform: scale(1.05);
        }

        .error-message {
            color: #FFD700;
            font-size: 14px;
            margin-top: 10px;
        }

        .signup-container {
            margin-top: 20px;
            font-size: 14px;
        }

        .sign-up a {
            color: #FFD700;
            font-weight: 600;
            text-decoration: none;
        }

        .sign-up a:hover {
            text-decoration: underline;
        }

        @media (max-width: 400px) {
            .container {
                padding: 20px 15px;
                border-radius: 15px;
            }

            .title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="DreamNestLogo.png" alt="DreamNest Logo">
        </div>
        <div class="title">Forgot Password</div>

        <form method="POST" action="forgot_password.php">
            <input type="email" name="email" placeholder="Enter your email" required autocomplete="off">
            <button type="submit" class="login-btn">Continue</button>
            <?php if (!empty($email_err)) echo "<p class='error-message'>$email_err</p>"; ?>
        </form>

        <div class="signup-container">
            Remember your password? <span class="sign-up"><a href="index.php">Login here</a></span>
        </div>
    </div>
</body>
</html>
