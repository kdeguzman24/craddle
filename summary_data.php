<?php
header('Content-Type: application/json');
require 'config.php'; // loads $mysqli connection

if ($mysqli->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit();
}

try {
    // Query to get total calm periods
    $result = $mysqli->query("SELECT COUNT(*) AS count FROM sensor_data WHERE baby_status = 'calm'");
    $calm_count = ($result) ? $result->fetch_assoc()['count'] : 0;

    // Query to get total crying instances
    $result = $mysqli->query("SELECT COUNT(*) AS count FROM sensor_data WHERE baby_status = 'crying'");
    $crying_count = ($result) ? $result->fetch_assoc()['count'] : 0;

    // Query to get the latest activity timestamp
    $result = $mysqli->query("SELECT MAX(timestamp) AS latest FROM sensor_data");
    $latest_entry = ($result) ? $result->fetch_assoc()['latest'] : null;

    // Query to get average temperature
    $result = $mysqli->query("SELECT AVG(temperature) AS avg_temp FROM sensor_data WHERE temperature IS NOT NULL");
    $avg_temp = ($result) ? $result->fetch_assoc()['avg_temp'] : null;

    // Query to get average humidity
    $result = $mysqli->query("SELECT AVG(humidity) AS avg_hum FROM sensor_data WHERE humidity IS NOT NULL");
    $avg_hum = ($result) ? $result->fetch_assoc()['avg_hum'] : null;

    // Query to get average movement
    $result = $mysqli->query("SELECT AVG(movement_status) AS avg_movement FROM sensor_data WHERE movement_status IS NOT NULL");
    $avg_movement = ($result) ? $result->fetch_assoc()['avg_movement'] : null;

    echo json_encode([
        'calm_count' => (int)$calm_count,
        'crying_count' => (int)$crying_count,
        'latest_entry' => $latest_entry ?: null,
        'avg_temp' => $avg_temp !== null ? round(floatval($avg_temp), 1) : null,
        'avg_hum' => $avg_hum !== null ? round(floatval($avg_hum), 1) : null,
        'avg_movement' => $avg_movement !== null ? round(floatval($avg_movement), 2) : null,
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Query error: ' . $e->getMessage()]);
}
?>
