<?php
require_once 'config.php';

echo "--- Initial state of stream_subject table ---\n";
$sql = "SELECT * FROM stream_subject";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Table is empty.\n";
}

$conn->close();
?>
