<?php
require_once "config.php";

$email = $_GET['email'] ?? '';
$password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if (empty($password) || strlen($password) < 8 || $password !== $confirm) {
        $password_err = "Passwords must match and be at least 8 characters.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ss", $hashed, $email);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // ✅ Redirect to login page
            header("Location: index.php?reset=success");
            exit;
        } else {
            $password_err = "Error updating password. Try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Change Password</title></head>
<body>
<h2>Change Password for <?= htmlspecialchars($email) ?></h2>
<form method="post">
    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
    <input type="password" name="password" placeholder="New password" required>
    <input type="password" name="confirm" placeholder="Confirm password" required>
    <button type="submit">Change Password</button>
</form>
<?php if ($password_err) echo "<p style='color:red;'>$password_err</p>"; ?>
</body>
</html>
