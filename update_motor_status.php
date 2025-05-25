<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = $_POST['status'];
    file_put_contents("motor_status.txt", $status);  // Save status to a file
    echo "Status updated to: " . $status;
} else {
    echo "Invalid request";
}
?>
