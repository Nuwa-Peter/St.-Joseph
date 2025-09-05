<?php
require_once __DIR__ . '/../../config.php';

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Fetch user details from DB to get email
$user_email = 'Not available';
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
if($row = $result->fetch_assoc()) {
    $user_email = $row['email'];
}
$stmt->close();


require_once __DIR__ . '/../../src/includes/header.php';
?>

<div class="container">
    <h1 class="my-4">About & User Roles</h1>
    <p>This page provides information about your user account and the permissions associated with your role.</p>

    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-person-circle me-2"></i>Your Information
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user_email); ?></p>
            <p><strong>Role:</strong> <span class="badge bg-primary fs-6"><?php echo ucfirst(htmlspecialchars($_SESSION['role'])); ?></span></p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-key-fill me-2"></i>Your Role & Permissions
        </div>
        <div class="card-body">
            <?php
            $current_role = $_SESSION['role'] ?? 'guest';
            echo "<h4>As a(n) " . ucfirst(htmlspecialchars($current_role)) . ", you can:</h4>";
            echo "<ul class='list-group list-group-flush'>";

            if ($current_role === 'root') {
                echo "<li class='list-group-item'>Access and manage **all** system features, including all menus and settings.</li>";
                echo "<li class='list-group-item'>Perform all administrative and financial duties.</li>";
            } elseif ($current_role === 'headteacher') {
                echo "<li class='list-group-item'>Access most system features, including the Admin and Finance modules.</li>";
                echo "<li class='list-group-item'>Oversee all academic, student, and financial information.</li>";
            } elseif ($current_role === 'bursar') {
                echo "<li class='list-group-item'>Access the full **Finance** module to manage fees, invoices, and expenses.</li>";
                echo "<li class='list-group-item'>Make and view requisitions.</li>";
            } elseif ($current_role === 'lab_attendant') {
                echo "<li class='list-group-item'>Access the **Laboratory** module to manage lab inventory and dashboards.</li>";
                echo "<li class='list-group-item'>Make and view requisitions.</li>";
            } else { // Default for other roles like 'student', 'teacher', etc.
                echo "<li class='list-group-item'>Access academic information relevant to your classes and subjects.</li>";
                echo "<li class='list-group-item'>Use the Library module to view and check out books.</li>";
                echo "<li class='list-group-item'>Make and view your own requisitions.</li>";
            }

            echo "</ul>";
            ?>
        </div>
    </div>

</div>

<?php
require_once __DIR__ . '/../../src/includes/footer.php';
?>
