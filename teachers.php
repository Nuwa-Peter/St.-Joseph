<?php
require_once 'config.php';
require_once 'includes/header.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Fetch all teachers from the database
$sql = "SELECT id, first_name, last_name, email, phone_number, availability, status FROM users WHERE role = 'teacher' ORDER BY last_name, first_name";
$result = $conn->query($sql);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
    <h2 class="mb-3 mb-md-0">Teachers</h2>
    <a href="teacher_create.php" class="btn btn-success"><i class="bi bi-plus-circle-fill me-2"></i>Add New Teacher</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Availability</th>
                        <th>Status</th>
                        <th style="width: 15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row["first_name"] . ' ' . $row["last_name"]); ?></td>
                                <td><?php echo htmlspecialchars($row["email"]); ?></td>
                                <td><?php echo htmlspecialchars($row["phone_number"]); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $row["availability"]))); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($row["status"])); ?></td>
                                <td>
                                    <a href="teacher_view.php?id=<?php echo $row["id"]; ?>" class="btn btn-sm btn-info">View</a>
                                    <a href="teacher_edit.php?id=<?php echo $row["id"]; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="user_delete.php?id=<?php echo $row["id"]; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this teacher? This action cannot be undone.');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No teachers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
