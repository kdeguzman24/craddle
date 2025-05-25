<?php
// summary_records.php

// Database connection
header('Content-Type: application/json');
require 'config.php'; // loads $mysqli connection

if ($mysqli->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit();
}

// Fetch latest 10 records
$sql = "SELECT timestamp, sound, movement_status FROM baby_status ORDER BY timestamp DESC LIMIT 10";
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
