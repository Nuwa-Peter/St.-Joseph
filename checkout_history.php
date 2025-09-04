<?php
require_once 'config.php';
require_once 'includes/header.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_role = $_SESSION['role'] ?? '';
$admin_roles = ['root', 'headteacher', 'director', 'librarian'];
$is_admin = in_array($user_role, $admin_roles);

$history_sql = "
    SELECT
        bc.id,
        b.title,
        u.first_name,
        u.last_name,
        bc.checkout_date,
        bc.due_date,
        bc.returned_date
    FROM book_checkouts bc
    JOIN books b ON bc.book_id = b.id
    JOIN users u ON bc.user_id = u.id
";

if (!$is_admin) {
    $history_sql .= " WHERE bc.user_id = ?";
}

$history_sql .= " ORDER BY bc.checkout_date DESC";

$stmt = $conn->prepare($history_sql);

if (!$is_admin) {
    $stmt->bind_param("i", $_SESSION['id']);
}

$stmt->execute();
$history_result = $stmt->get_result();
?>

<h2>Checkout History</h2>
<p><?php echo $is_admin ? 'A complete log of all book checkout transactions.' : 'Your personal book checkout history.'; ?></p>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Book Title</th>
            <th>Student</th>
            <th>Checkout Date</th>
            <th>Due Date</th>
            <th>Returned Date</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($history_result->num_rows > 0): ?>
            <?php while($row = $history_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["title"]); ?></td>
                    <td><?php echo htmlspecialchars($row["first_name"] . ' ' . $row["last_name"]); ?></td>
                    <td><?php echo date("d M Y, g:i A", strtotime($row["checkout_date"])); ?></td>
                    <td><?php echo date("d M Y", strtotime($row["due_date"])); ?></td>
                    <td>
                        <?php if ($row["returned_date"]): ?>
                            <?php echo date("d M Y, g:i A", strtotime($row["returned_date"])); ?>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Not Returned</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">No checkout history found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
