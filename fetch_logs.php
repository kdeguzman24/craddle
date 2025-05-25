<?php
require_once "config.php";

header('Content-Type: application/json');

$sql = "SELECT DATE_FORMAT(timestamp, '%Y-%m-%d %H:%i') AS timestamp, sound FROM sensor_data ORDER BY timestamp DESC LIMIT 10";

$result = $mysqli->query($sql);

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sound_raw = $row['sound'];
        $baby_status = 'Unknown';

        if (is_numeric($sound_raw)) {
            $baby_status = ($sound_raw > 350) ? 'Crying' : 'Calm';
        } else {
            $sound_str = strtolower(trim($sound_raw));
            if ($sound_str === 'crying') $baby_status = 'Crying';
            elseif ($sound_str === 'calm') $baby_status = 'Calm';
        }

        $data[] = [
            'timestamp' => substr($row['timestamp'], 0, 19),

            'baby_status' => $baby_status
        ];
    }
}

echo json_encode($data);
$mysqli->close();
