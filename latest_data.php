<?php
require_once "config.php"; // Make sure $mysqli is correctly set up

// Check database connection
if (!$mysqli) {
    error_log("Database connection failed: " . mysqli_connect_error());
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Default response structure
$data = [
    'sound' => 'N/A',
    'temperature' => 'N/A',
    'humidity' => 'N/A',
    'baby_status' => 'Unknown',
    'movement_status' => 'Unknown',
    'stream_url' => '',
    'timestamp' => null // Optional: Include timestamp of the latest data
];

// Fetch latest sensor data
$sensor_query = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1";
$sensor_result = $mysqli->query($sensor_query);

if ($sensor_result && $sensor_result->num_rows > 0) {
    $row = $sensor_result->fetch_assoc();

    $sound_raw = $row['sound'];
    $data['sound'] = $sound_raw;
    $data['temperature'] = $row['temperature'];
    $data['humidity'] = $row['humidity'];
    $data['timestamp'] = $row['timestamp'];

    // Handle movement status
    $movement = strtolower(trim($row['movement_status'] ?? ''));
    if ($movement === '1' || $movement === 'moving') {
        $data['movement_status'] = 'Moving';
    } elseif ($movement === '0' || $movement === 'still') {
        $data['movement_status'] = 'Still';
    } else {
        $data['movement_status'] = 'Unknown';
    }

    // Determine baby status based on sound
    if (is_numeric($sound_raw)) {
        $sound_value = (float)$sound_raw;
        $data['baby_status'] = ($sound_value > 400) ? 'Crying' : 'Calm';
    } else {
        $sound_str = strtolower(trim($sound_raw));
        if ($sound_str === 'crying') {
            $data['baby_status'] = 'Crying';
        } elseif ($sound_str === 'calm') {
            $data['baby_status'] = 'Calm';
        } else {
            $data['baby_status'] = 'Unknown';
            error_log("Unrecognized sound value: '$sound_raw'");
        }
    }
} else {
    error_log("Sensor data query failed or no data found: " . $mysqli->error);
}

// Fetch latest stream URL
$stream_query = "SELECT stream_url FROM video_streams ORDER BY id DESC LIMIT 1";
$stream_result = $mysqli->query($stream_query);

if ($stream_result && $stream_result->num_rows > 0) {
    $stream_row = $stream_result->fetch_assoc();
    $data['stream_url'] = $stream_row['stream_url'];
} else {
    error_log("Stream URL query failed or no data found: " . $mysqli->error);
}

// Close DB connection
$mysqli->close();

// Output as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
