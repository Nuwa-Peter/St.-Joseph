<?php
session_start();
require_once 'config.php';
require_once 'includes/header.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Define roles for the dropdown
$roles = ['student' => 'Students', 'teacher' => 'Teachers', 'headteacher' => 'Head Teacher'];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Generate ID Cards</h2>
</div>

<div class="card">
    <div class="card-header">ID Card Options</div>
    <div class="card-body">
        <form action="generate_id_card_pdf.php" method="post" target="_blank">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="role" class="form-label">Generate for</label>
                    <select name="role" id="role" class="form-select" required>
                        <option value="">Select Role...</option>
                        <?php foreach($roles as $role_key => $role_value): ?>
                            <option value="<?php echo $role_key; ?>"><?php echo $role_value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="generation_scope" class="form-label">Scope</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="generation_scope" id="scope_all" value="all" checked>
                        <label class="form-check-label" for="scope_all">All users in selected role</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="generation_scope" id="scope_individual" value="individual">
                        <label class="form-check-label" for="scope_individual">Individual User</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3" id="individual-user-container" style="display: none;">
                    <label for="user_id" class="form-label">Select User</label>
                    <select name="user_id" id="user_id" class="form-select" disabled>
                        <option value="">Select a role first...</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="issue_date" class="form-label">Date of Issue</label>
                    <input type="date" name="issue_date" id="issue_date" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="expiry_date" class="form-label">Expiry Date</label>
                    <input type="date" name="expiry_date" id="expiry_date" class="form-control" required>
                </div>
            </div>

            <hr>
            <button type="submit" class="btn btn-primary"><i class="bi bi-person-vcard-fill me-2"></i>Generate ID Cards</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scopeRadios = document.querySelectorAll('input[name="generation_scope"]');
    const userContainer = document.getElementById('individual-user-container');
    const userSelect = document.getElementById('user_id');
    const roleSelect = document.getElementById('role');

    scopeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'individual') {
                userContainer.style.display = 'block';
                userSelect.disabled = !roleSelect.value;
            } else {
                userContainer.style.display = 'none';
                userSelect.disabled = true;
            }
        });
    });

    roleSelect.addEventListener('change', function() {
        const role = this.value;
        const scope = document.querySelector('input[name="generation_scope"]:checked').value;
        userSelect.disabled = true;
        userSelect.innerHTML = '<option value="">Loading...</option>';

        if (scope === 'individual' && role) {
            fetch(`api_get_users_by_role.php?role=${role}`)
                .then(response => response.json())
                .then(data => {
                    userSelect.innerHTML = '<option value="">Select User...</option>';
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }
                    data.forEach(user => {
                        userSelect.innerHTML += `<option value="${user.id}">${user.first_name} ${user.last_name}</option>`;
                    });
                    userSelect.disabled = false;
                });
        } else {
            userSelect.innerHTML = '<option value="">Select a role first...</option>';
        }
    });
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
