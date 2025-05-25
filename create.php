<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the config.php file for database connection
require_once "config.php";

// Start session to handle success messages
session_start();

// Initialize variables
$username = $email = $password = "";
$success_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error_message'] = "All fields are required!";
        header("Location: create.php");
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['error_message'] = "Password must be at least 8 characters long.";
        header("Location: create.php");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $sql_check_email = "SELECT email FROM users WHERE email = ?";
    if ($stmt = $mysqli->prepare($sql_check_email)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $_SESSION['error_message'] = "Email already exists. Please choose another one.";
            header("Location: create.php");
            exit();
        } else {
            // Insert user into database, using username as the display_name
            $sql = "INSERT INTO users (username, display_name, email, password) VALUES (?, ?, ?, ?)";
            if ($stmt = $mysqli->prepare($sql)) {
                // Use the username as the display_name
                $stmt->bind_param("ssss", $username, $username, $email, $hashed_password);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Account created successfully! Please log in.";
                    header("Location: index.php");
                    exit();
                } else {
                    $_SESSION['error_message'] = "Database error: " . $stmt->error;
                }
            }
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Database error: " . $mysqli->error;
    }
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 355px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: auto;
        }

        .title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: 80%;
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
            width: 80%;
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
            color: white;
            text-decoration: none;
            font-size: 14px;
            display: block;
            margin-top: 10px;
        }

        .forgot-password-link:hover {
            text-decoration: underline;
        }

        .already-account {
            margin-top: 20px;
            font-size: 14px;
        }

        .already-account a {
            color: #FFD700;
            font-weight: 600;
            text-decoration: none;
        }

        .already-account a:hover {
            text-decoration: underline;
        }

        .success-message,
        .error-message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 16px;
            width: 80%;
        }

        .success-message {
            background-color: #28a745;
            color: white;
        }

        .error-message {
            background-color: #dc3545;
            color: white;
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
        <div class="title">Create Your Account</div>

        <?php
        if (isset($_SESSION['success_message'])) {
            echo "<div class='success-message'>" . $_SESSION['success_message'] . "</div>";
            unset($_SESSION['success_message']);
        }

        if (isset($_SESSION['error_message'])) {
            echo "<div class='error-message'>" . $_SESSION['error_message'] . "</div>";
            unset($_SESSION['error_message']);
        }
        ?>

        <form action="create.php" method="POST" onsubmit="return validateForm()">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" minlength="8" required>
            <button type="submit" class="login-btn">GET STARTED</button>
        </form>

        <div class="already-account">
            <span>Already have an account? <a href="index.php">Login here</a></span>
        </div>
    </div>

    <script>
        function validateForm() {
            const password = document.querySelector('input[name="password"]').value;
            if (password.length < 8) {
                alert("Password must be at least 8 characters long.");
                return false;
            }
            return true;
        }
    </script>
</body>

</html>