<?php
// This widget requires the $conn database connection to be available.

// Fetch statistics
// Total Students
$total_students_stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
$total_students = $total_students_stmt->fetch_assoc()['count'];

// Total Teachers
$total_teachers_stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'teacher'");
$total_teachers = $total_teachers_stmt->fetch_assoc()['count'];

// Total Parents
$total_parents_stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'parent'");
$total_parents = $total_parents_stmt->fetch_assoc()['count'];

// Total Classes (Streams)
$total_classes_stmt = $conn->query("SELECT COUNT(*) as count FROM streams");
$total_classes = $total_classes_stmt->fetch_assoc()['count'];

?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Total Students</h5>
                        <h2 class="card-text"><?php echo $total_students; ?></h2>
                    </div>
                    <i class="bi bi-people-fill" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Total Teachers</h5>
                        <h2 class="card-text"><?php echo $total_teachers; ?></h2>
                    </div>
                    <i class="bi bi-person-video3" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Total Parents</h5>
                        <h2 class="card-text"><?php echo $total_parents; ?></h2>
                    </div>
                    <i class="bi bi-person-hearts" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Total Classes</h5>
                        <h2 class="card-text"><?php echo $total_classes; ?></h2>
                    </div>
                    <i class="bi bi-easel-fill" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>
