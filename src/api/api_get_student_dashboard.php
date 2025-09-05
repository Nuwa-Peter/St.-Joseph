<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

// Basic error handling
if (!isset($_GET['student_id'])) {
    echo json_encode(['error' => 'No student ID provided.']);
    exit;
}

$student_id = intval($_GET['student_id']);
$dashboard_data = [];

// --- 1. Fetch Basic Student Information ---
$stmt = $conn->prepare("SELECT u.id, u.first_name, u.last_name, u.other_name, u.email, u.unique_id, u.role, u.status, u.gender, u.date_of_birth, cl.name as class_name, s.name as stream_name
                        FROM users u
                        LEFT JOIN stream_user su ON u.id = su.user_id
                        LEFT JOIN streams s ON su.stream_id = s.id
                        LEFT JOIN class_levels cl ON s.class_level_id = cl.id
                        WHERE u.id = ? AND u.role = 'student'");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
if ($student_info = $result->fetch_assoc()) {
    $dashboard_data['profile'] = $student_info;
} else {
    echo json_encode(['error' => 'Student not found.']);
    exit;
}
$stmt->close();

// --- 2. Fetch Academic Performance (Marks) ---
$marks_query = "SELECT s.name as subject_name, p.name as paper_name, m.score
                FROM marks m
                JOIN papers p ON m.paper_id = p.id
                JOIN subjects s ON p.subject_id = s.id
                WHERE m.user_id = ?
                ORDER BY s.name, p.name";
$stmt = $conn->prepare($marks_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$dashboard_data['academics']['marks'] = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- 3. Fetch Financial Status (Invoices & Payments) ---
$finance_query = "SELECT i.id, i.total_amount, i.amount_paid, i.due_date, i.status, i.academic_year, i.term
                  FROM invoices i
                  WHERE i.user_id = ?
                  ORDER BY i.due_date DESC";
$stmt = $conn->prepare($finance_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$dashboard_data['finance']['invoices'] = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- 4. Fetch Attendance Records ---
$attendance_query = "SELECT date, check_in, check_out FROM attendances WHERE user_id = ? ORDER BY date DESC LIMIT 30"; // Limit to last 30 records for performance
$stmt = $conn->prepare($attendance_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$dashboard_data['attendance'] = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- 5. Fetch Discipline Logs ---
$discipline_query = "SELECT log_date, type, description FROM discipline_logs WHERE user_id = ? ORDER BY log_date DESC";
$stmt = $conn->prepare($discipline_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$dashboard_data['discipline'] = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- 6. Fetch Student Life (Dorm & Clubs) ---
// Dormitory
$dorm_query = "SELECT dr.room_number, d.name as dormitory_name
               FROM room_assignments ra
               JOIN dormitory_rooms dr ON ra.dormitory_room_id = dr.id
               JOIN dormitories d ON dr.dormitory_id = d.id
               WHERE ra.user_id = ?";
$stmt = $conn->prepare($dorm_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$dashboard_data['student_life']['dormitory'] = $result->fetch_assoc() ?? 'Not Assigned';
$stmt->close();

// Clubs
$clubs_query = "SELECT c.name as club_name, c.description
                FROM club_members cm
                JOIN clubs c ON cm.club_id = c.id
                WHERE cm.user_id = ?";
$stmt = $conn->prepare($clubs_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$dashboard_data['student_life']['clubs'] = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// --- Final Output ---
echo json_encode($dashboard_data, JSON_PRETTY_PRINT);

$conn->close();
?>
