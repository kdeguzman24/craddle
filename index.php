<?php
session_start();
require_once "config.php";

$email = $password = "";
$email_err = $password_err = "";
$show_reset_form = false;

// Form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST['email']))) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    } else {
        $email = trim($_POST['email']);
    }

    // Reset password logic
    if (isset($_POST['reset_password'])) {
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);

        if (empty($new_password) || empty($confirm_password)) {
            $password_err = "Please enter and confirm the new password.";
        } elseif ($new_password !== $confirm_password) {
            $password_err = "Passwords do not match.";
        } elseif (strlen($new_password) < 8) {
            $password_err = "Password must be at least 8 characters.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE email = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("ss", $hashed_password, $email);
                if ($stmt->execute()) {
                    echo "Password updated successfully!";
                    header("Location: index.php");
                    exit();
                } else {
                    echo "Error updating password.";
                }
                $stmt->close();
            }
        }
        $show_reset_form = true;
    } else {
        // Login logic
        if (empty(trim($_POST['password']))) {
            $password_err = "Please enter your password.";
        } else {
            $password = trim($_POST['password']);
        }

        if (empty($email_err) && empty($password_err)) {
            $sql = "SELECT id, email, username, password FROM users WHERE email = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("s", $email);
                if ($stmt->execute()) {
                    $stmt->bind_result($db_id, $db_email, $db_username, $db_password);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $db_password)) {
                            $_SESSION['id'] = $db_id;
                            $_SESSION['email'] = $db_email;
                            $_SESSION['username'] = $db_username;
                            header("Location: db.php");
                            exit();
                        } else {
                            $password_err = "Incorrect password. Please try again.";
                        }
                    } else {
                        $email_err = "This email is not registered.";
                    }
                }
                $stmt->close();
            }
        }
    }
}

// If reset link clicked
if (isset($_GET['forgot_password'])) {
    $show_reset_form = true;
    if (isset($_GET['email'])) {
        $email = $_GET['email'];
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>DreamNest | Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #3c0b76, #1b3a73);
            color: #fff;

            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
           
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

        input[type="text"],
        input[type="password"] {
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

        .forgot-password-link {
            color: #FFD700;
            /* Yellow color */
            text-decoration: none;
            font-size: 14px;
            display: block;
            margin-top: 10px;
        }

        .forgot-password-link:hover {
            text-decoration: underline;
        }

        .signup-container {
            margin-top: 20px;
            font-size: 14px;
        }

        .sign-up a {
            color: #FFD700;
            /* Yellow color */
            font-weight: 600;
            text-decoration: none;
        }

        .sign-up a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: #FFD700;
            font-size: 14px;
            margin-top: 10px;
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
        <div class="title">DreamNest</div>

        <?php if ($show_reset_form): ?>
            <form method="POST" action="index.php">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                <input type="password" name="new_password" placeholder="New Password" required autocomplete="off">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required autocomplete="off">
                <button type="submit" name="reset_password" class="login-btn">Reset Password</button>
                <?php if (!empty($password_err)) echo "<p class='error-message'>$password_err</p>"; ?>
            </form>
            <div class="signup-container">
                Have an account? <span class="sign-up"><a href="index.php">Login here</a></span>
            </div>
        <?php else: ?>
            <form method="POST" action="index.php">
                <input type="text" name="email" placeholder="Email" required autocomplete="off">
                <input type="password" name="password" placeholder="Password" required autocomplete="off">
                <button type="submit" class="login-btn">Login</button>
                <?php if (!empty($password_err)) echo "<p class='error-message'>$password_err</p>"; ?>
                <?php if (!empty($email_err)) echo "<p class='error-message'>$email_err</p>"; ?>
            </form>
            <div class="signup-container">
                <span class="sign-up"><a href="forgot_password.php">Forgot password?</a></span>
            </div>
        <?php endif; ?>

        <div class="signup-container">
            New here? <span class="sign-up"><a href="create.php">Create account</a></span>
        </div>
    </div>
</body>

</html>