<?php
if (isset($_POST['status'])) {
    $status = $_POST['status'];
    echo "Received status: " . $status . "\n"; // Debugging output

    if (file_put_contents("toy_status.txt", $status) === false) {
        echo "Error: Could not write to file.\n";
    } else {
        echo "Status updated to: " . $status;
    }
} else {
    echo "Error: No status received.";
}
?>
