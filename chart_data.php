<?php
require_once "config.php";
header('Content-Type: application/json');

$data = array_fill(0, 24, ['crying' => 0, 'hot' => 0, 'hot_count' => 0]);

$query = "SELECT HOUR(timestamp) AS hour, sound, temperature FROM sensor_data";
$result = $mysqli->query($query);

if (!$result) {
    echo json_encode(['error' => $mysqli->error]);
    exit;
}

while ($row = $result->fetch_assoc()) {
    $hour = (int)$row['hour'];
    $sound = (float)$row['sound'];
    $temperature = (float)$row['temperature'];

    if ($sound > 200) {
        $data[$hour]['crying']++;
    }

    $data[$hour]['hot'] += $temperature;
    $data[$hour]['hot_count']++;
}

foreach ($data as $hour => $entry) {
    if ($entry['hot_count'] > 0) {
        $data[$hour]['hot'] = round($entry['hot'] / $entry['hot_count'], 2);
    } else {
        $data[$hour]['hot'] = 0;
    }
    unset($data[$hour]['hot_count']);
}

$mysqli->close();
echo json_encode($data);
