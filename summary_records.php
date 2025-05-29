<?php
// summary_records.php

header('Content-Type: application/json');
require 'config.php'; // loads $mysqli connection

if ($mysqli->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit();
}

// ✅ Include all fields needed by JS: baby_status, temperature, humidity, sound, movement_status
$sql = "SELECT timestamp, baby_status, temperature, humidity, sound, movement_status 
        FROM sensor_data 
        ORDER BY timestamp DESC 
        LIMIT 10";

$result = $mysqli->query($sql);

$records = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}

$mysqli->close();
echo json_encode($records);
?>
