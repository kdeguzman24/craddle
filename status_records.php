<?php
require 'config.php';

$sql = "SELECT timestamp, status FROM sensor_data ORDER BY timestamp DESC LIMIT 50";
$result = $conn->query($sql);

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = [
        'timestamp' => $row['timestamp'],
        'status' => ucfirst($row['status'])  // Capitalize first letter
    ];
}

header('Content-Type: application/json');
echo json_encode($records);
?>
