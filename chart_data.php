<?php
require_once "config.php";

$data = array_fill(0, 24, ['crying' => 0, 'hot' => 0, 'hot_count' => 0]);

$query = "SELECT HOUR(timestamp) AS hour, sound, temperature FROM sensor_data";
$result = $mysqli->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $hour = (int)$row['hour'];
        $sound = (float)$row['sound'];
        $temperature = (float)$row['temperature'];

        if ($sound > 300) {
            $data[$hour]['crying']++;
        }

        // Accumulate temperature values
        $data[$hour]['hot'] += $temperature;
        $data[$hour]['hot_count']++;
    }

    // Compute average temperature per hour
    foreach ($data as $hour => $entry) {
        if ($entry['hot_count'] > 0) {
            $data[$hour]['hot'] = round($entry['hot'] / $entry['hot_count'], 2);
        } else {
            $data[$hour]['hot'] = 0;
        }
        unset($data[$hour]['hot_count']); // clean up
    }
}

$mysqli->close();
echo json_encode($data);
