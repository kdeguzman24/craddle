<?php
// DB connection info
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "craddle2";

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

// Connect to MySQL
$mysqli = new mysqli($servername, $username, $password, $dbname);
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Connection failed: " . $mysqli->connect_error]);
    exit;
}

// Optional: Only check status without inserting data
if (isset($_POST['check_status_only'])) {
    $swingStatus = @trim(file_get_contents("motor_status.txt")) ?: "off";
    $fanStatus = @trim(file_get_contents("fan_status.txt")) ?: "off";
    echo json_encode(["swing" => $swingStatus, "fan" => $fanStatus]);
    $mysqli->close();
    exit;
}

// Required POST variables
//$required_fields = ['timestamp', 'sound', 'baby_status', 'movement_status'];
$required_fields = ['timestamp', 'sound', 'temperature', 'humidity', 'baby_status', 'movement_status'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field])) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Missing field: $field"]);
        $mysqli->close();
        exit;
    }
}

// Assign and sanitize
$timestamp = $_POST['timestamp'];
$sound = (int)$_POST['sound'];
$temperature = (float)$_POST['temperature'];
$humidity = (float)$_POST['humidity'];
$baby_status = $_POST['baby_status'];
$movement_status = $_POST['movement_status'];

// Prepare and bind statement
$stmt = $mysqli->prepare("INSERT INTO sensor_data (timestamp, sound, temperature, humidity, baby_status, movement_status) VALUES (?, ?, ?, ?, ?, ?)");
//$stmt = $mysqli->prepare("INSERT INTO sensor_data (timestamp, sound, baby_status, movement_status) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Prepare failed: " . $mysqli->error]);
    $mysqli->close();
    exit;
}

$stmt->bind_param("siddss", $timestamp, $sound, $temperature, $humidity, $baby_status, $movement_status);
//$stmt->bind_param("sdss", $timestamp, $sound, $baby_status, $movement_status);


if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Execute failed: " . $stmt->error]);
    $stmt->close();
    $mysqli->close();
    exit;
}

$stmt->close();

// Read swing and fan motor status
$swingStatus = @trim(file_get_contents("motor_status.txt")) ?: "off";
$fanStatus = @trim(file_get_contents("fan_status.txt")) ?: "off";

// Return status JSON
echo json_encode([
    "swing" => $swingStatus,
    "fan" => $fanStatus
]);

$mysqli->close();
?>
