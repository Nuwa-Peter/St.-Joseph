<?php
require_once 'config.php';

// Ensure user is an admin
$admin_roles = ['root', 'headteacher'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $admin_roles)) {
    header("location: dashboard.php");
    exit;
}

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <h1 class="my-4"><i class="bi bi-person-bounding-box me-2"></i>Student Analytics Dashboard</h1>

    <!-- Student Search Section -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header fw-bold">
                    <i class="bi bi-search me-2"></i>Find a Student
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" id="student-search-input" class="form-control" placeholder="Search for a student by name or ID...">
                    </div>
                    <div id="student-search-results" class="list-group mt-2" style="max-height: 300px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Content - Hidden by default -->
    <div id="analytics-dashboard-content" class="d-none">
        <h2 id="student-name-header" class="text-primary"></h2>
        <hr>
        <div class="row">
            <!-- Column 1: Profile & Vitals -->
            <div class="col-lg-4">
                <div id="profile-card" class="card mb-4"></div>
                <div id="student-life-card" class="card mb-4"></div>
            </div>

            <!-- Column 2: Academics & Finance -->
            <div class="col-lg-8">
                <div id="academic-performance-card" class="card mb-4"></div>
                <div id="attendance-discipline-card" class="card mb-4"></div>
                <div id="finance-card" class="card mb-4"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('student-search-input');
    const searchResultsContainer = document.getElementById('student-search-results');
    const dashboardContent = document.getElementById('analytics-dashboard-content');
    let searchDebounceTimer;
    let academicChart = null;

    // 1. EVENT LISTENERS
    searchInput.addEventListener('input', () => {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            const query = searchInput.value;
            if (query.length > 1) {
                searchStudents(query);
            } else {
                searchResultsContainer.innerHTML = '';
            }
        }, 300);
    });

    // 2. STUDENT SEARCH
    function searchStudents(query) {
        fetch(`api_search_users.php?role=student&q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => displaySearchResults(data))
            .catch(error => console.error('Error searching students:', error));
    }

    function displaySearchResults(students) {
        searchResultsContainer.innerHTML = '';
        if (students.length === 0) {
            searchResultsContainer.innerHTML = '<div class="list-group-item">No students found.</div>';
            return;
        }
        students.forEach(student => {
            const studentDiv = document.createElement('a');
            studentDiv.href = '#';
            studentDiv.className = 'list-group-item list-group-item-action';
            studentDiv.textContent = `${student.first_name} ${student.last_name} (${student.unique_id})`;
            studentDiv.addEventListener('click', (e) => {
                e.preventDefault();
                selectStudent(student.id);
            });
            searchResultsContainer.appendChild(studentDiv);
        });
    }

    // 3. SELECT STUDENT & FETCH DASHBOARD DATA
    function selectStudent(studentId) {
        searchInput.value = '';
        searchResultsContainer.innerHTML = '';
        dashboardContent.classList.add('d-none'); // Hide while loading

        fetch(`api_get_student_dashboard.php?student_id=${studentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                    return;
                }
                renderDashboard(data);
                dashboardContent.classList.remove('d-none'); // Show when loaded
            })
            .catch(error => console.error('Error fetching dashboard data:', error));
    }

    // 4. RENDER FUNCTIONS
    function renderDashboard(data) {
        document.getElementById('student-name-header').textContent = `${data.profile.first_name} ${data.profile.last_name}'s Dashboard`;
        renderProfileCard(data.profile);
        renderStudentLifeCard(data.student_life);
        renderAcademicsCard(data.academics);
        renderAttendanceDisciplineCard(data.attendance, data.discipline);
        renderFinanceCard(data.finance);
    }

    function renderProfileCard(profile) {
        const card = document.getElementById('profile-card');
        card.innerHTML = `
            <div class="card-header fw-bold"><i class="bi bi-person-badge me-2"></i>Student Profile</div>
            <div class="card-body">
                <p><strong>ID:</strong> ${profile.unique_id || 'N/A'}</p>
                <p><strong>Class:</strong> ${profile.class_name || 'N/A'}</p>
                <p><strong>Stream:</strong> ${profile.stream_name || 'N/A'}</p>
                <p><strong>Gender:</strong> ${profile.gender || 'N/A'}</p>
                <p><strong>Date of Birth:</strong> ${profile.date_of_birth || 'N/A'}</p>
                <p><strong>Status:</strong> <span class="badge bg-success">${profile.status}</span></p>
            </div>
        `;
    }

    function renderStudentLifeCard(studentLife) {
        const card = document.getElementById('student-life-card');
        let clubsHtml = studentLife.clubs.length > 0
            ? studentLife.clubs.map(c => `<li>${c.club_name}</li>`).join('')
            : '<li>Not a member of any clubs.</li>';

        card.innerHTML = `
            <div class="card-header fw-bold"><i class="bi bi-house-door-fill me-2"></i>Student Life</div>
            <div class="card-body">
                <h6>Dormitory</h6>
                <p>${studentLife.dormitory.dormitory_name || 'N/A'} - Room ${studentLife.dormitory.room_number || 'N/A'}</p>
                <hr>
                <h6>Clubs</h6>
                <ul>${clubsHtml}</ul>
            </div>
        `;
    }

    function renderAcademicsCard(academics) {
        const card = document.getElementById('academic-performance-card');
        let marksHtml = academics.marks.length > 0
            ? academics.marks.map(m => `<tr><td>${m.subject_name}</td><td>${m.paper_name}</td><td>${m.score}%</td></tr>`).join('')
            : '<tr><td colspan="3">No marks entered.</td></tr>';

        card.innerHTML = `
            <div class="card-header fw-bold"><i class="bi bi-bar-chart-line-fill me-2"></i>Academic Performance</div>
            <div class="card-body">
                <canvas id="academic-chart" style="max-height: 250px;"></canvas>
                <hr>
                <h6>Recent Marks</h6>
                <div class="table-responsive" style="max-height: 200px;">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Subject</th><th>Paper</th><th>Score</th></tr></thead>
                        <tbody>${marksHtml}</tbody>
                    </table>
                </div>
            </div>
        `;

        // Chart.js implementation
        if (academicChart) {
            academicChart.destroy();
        }
        const ctx = document.getElementById('academic-chart').getContext('2d');
        const chartData = {
            labels: academics.marks.map(m => `${m.subject_name.substring(0,5)}. - ${m.paper_name.substring(0,5)}`),
            datasets: [{
                label: 'Score',
                data: academics.marks.map(m => m.score),
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        };
        academicChart = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                scales: { y: { beginAtZero: true, max: 100 } },
                plugins: { legend: { display: false } }
            }
        });
    }

    function renderAttendanceDisciplineCard(attendance, discipline) {
        const card = document.getElementById('attendance-discipline-card');
        let attendanceHtml = attendance.length > 0
            ? attendance.map(a => `<li>${a.date}: Checked in at ${a.check_in || 'N/A'}</li>`).join('')
            : '<li>No recent attendance records.</li>';
        let disciplineHtml = discipline.length > 0
            ? discipline.map(d => `<li><strong>${d.log_date} (${d.type}):</strong> ${d.description}</li>`).join('')
            : '<li>No discipline records.</li>';

        card.innerHTML = `
            <div class="card-header fw-bold"><i class="bi bi-check-circle-fill me-2"></i>Attendance & Discipline</div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                <h6>Last 30 Attendance Records</h6>
                <ul>${attendanceHtml}</ul>
                <hr>
                <h6>Discipline Logs</h6>
                <ul>${disciplineHtml}</ul>
            </div>
        `;
    }

    function renderFinanceCard(finance) {
        const card = document.getElementById('finance-card');
        let totalDue = 0;
        let invoicesHtml = finance.invoices.length > 0
            ? finance.invoices.map(i => {
                const balance = i.total_amount - i.amount_paid;
                if (i.status !== 'paid') totalDue += balance;
                return `<tr><td>${i.academic_year} T${i.term}</td><td>${i.status}</td><td class="text-end">${parseFloat(balance).toFixed(2)}</td></tr>`;
              }).join('')
            : '<tr><td colspan="3">No invoices found.</td></tr>';

        card.innerHTML = `
            <div class="card-header fw-bold"><i class="bi bi-cash-coin me-2"></i>Financial Status</div>
            <div class="card-body">
                <h5>Total Amount Due: <span class="text-danger">UGX ${totalDue.toFixed(2)}</span></h5>
                <hr>
                <h6>Invoice History</h6>
                <div class="table-responsive" style="max-height: 200px;">
                    <table class="table table-sm">
                        <thead><tr><th>Term</th><th>Status</th><th class="text-end">Balance</th></tr></thead>
                        <tbody>${invoicesHtml}</tbody>
                    </table>
                </div>
            </div>
        `;
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>
