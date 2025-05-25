<?php
session_start();
require_once 'config.php';

// Handle logout via ?logout=true
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// Redirect to login if not authenticated
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch current display name
$query = "SELECT display_name FROM users WHERE email = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($display_name);
$stmt->fetch();
$stmt->close();

// Handle display name update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_display_name'])) {
    $newDisplayName = $_POST['new_display_name'];
    $update = $mysqli->prepare("UPDATE users SET display_name = ? WHERE email = ?");
    $update->bind_param("ss", $newDisplayName, $email);
    $update->execute();
    $update->close();
    header("Location: settings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>DreamNest Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Quicksand', sans-serif;
            background: linear-gradient(to right, #3c0b76, #1b3a73);
            color: #fff;

        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: rgba(0, 0, 0, 0.2);
            flex-wrap: wrap;
        }

        .branding {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .branding img {
            height: 40px;
            width: 40px;
            border-radius: 50%;
        }

        .brand-title {
            font-size: 22px;
            font-weight: 600;
        }

        .nav-links a {
            margin-left: 25px;
            text-decoration: none;
            color: white;
            font-weight: 600;
            transition: 0.3s;
        }

        .nav-links a:hover {
            color: #ddd;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(12px);
        }

        header h1 {
            text-align: center;
            font-size: 26px;
            margin-bottom: 30px;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            padding: 15px 20px;
            border-radius: 12px;
        }

        .setting-label {
            font-size: 16px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="email"] {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            padding: 10px 14px;
            border-radius: 8px;
            color: #fff;
            width: 60%;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 24px;
        }

        .slider:before {
            content: "";
            position: absolute;
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #4CAF50;
        }

        input:checked+.slider:before {
            transform: translateX(24px);
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 30px;
        }

        button:hover {
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }

            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="top-bar">
        <div class="branding">
            <img src="DreamNestLogo.png" alt="DreamNest Logo">
            <span class="brand-title">DreamNest</span>
        </div>
        <div class="nav-links">
            <a href="db.php">Dashboard</a>
            <a href="records.php">Records</a>
            <a href="settings.php?logout=true" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
        </div>
    </div>

    <div class="container">
        <header>
            <h1>Settings</h1>
        </header>

        <form method="POST" id="settingsForm">
            <div class="settings-grid">
                <div class="setting-item">
                    <span class="setting-label">Email</span>
                    <input type="email" value="<?= htmlspecialchars($email) ?>" readonly>
                </div>

                <div class="setting-item">
                    <span class="setting-label">Display Name</span>
                    <input type="text" name="new_display_name" value="<?= htmlspecialchars($display_name) ?>" required>
                </div>

                <div class="setting-item">
                    <span class="setting-label">Notifications</span>
                    <label class="switch">
                        <input type="checkbox" id="notifications" name="notifications" checked>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <button type="submit">Save Changes</button>
        </form>
    </div>

    <script>
        const notificationToggle = document.querySelector('#notifications');
        notificationToggle.addEventListener('change', () => {
            const status = notificationToggle.checked ? 'enabled' : 'disabled';
            console.log(`Notifications are now ${status}`);
        });
    </script>
</body>

</html>