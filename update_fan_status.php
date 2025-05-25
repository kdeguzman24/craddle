<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = $_POST['status'] === 'on' ? 'on' : 'off';
    file_put_contents('fan_status.txt', $status);
    echo "Fan status updated to: $status";
} else {
    http_response_code(400);
    echo "Invalid request";
}
?>
