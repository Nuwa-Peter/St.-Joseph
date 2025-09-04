<?php
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode([]);
    exit;
}

// FullCalendar sends start and end parameters
$start_str = $_GET['start'] ?? date('Y-m-d');
$end_str = $_GET['end'] ?? date('Y-m-d');

$sql = "
    SELECT
        rb.id,
        rb.notes as title,
        rb.start_time as start,
        rb.end_time as end,
        r.name as resourceName,
        CONCAT(u.first_name, ' ', u.last_name) as bookedBy
    FROM
        resource_bookings rb
    JOIN
        resources r ON rb.resource_id = r.id
    JOIN
        users u ON rb.user_id = u.id
    WHERE
        rb.start_time BETWEEN ? AND ?
        OR rb.end_time BETWEEN ? AND ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $start_str, $end_str, $start_str, $end_str);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
if($result){
    while($row = $result->fetch_assoc()){
        // Prepend resource name to title for display
        $row['title'] = $row['resourceName'] . ' (' . $row['bookedBy'] . ')';
        $bookings[] = $row;
    }
}
$stmt->close();

echo json_encode($bookings);
$conn->close();
?>
