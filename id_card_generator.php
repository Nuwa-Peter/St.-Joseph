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
                    <label for="user_search_input" class="form-label">Search for User (Name or LIN)</label>
                    <input type="text" id="user_search_input" class="form-control" placeholder="Start typing to search..." disabled>
                    <input type="hidden" name="user_id" id="user_id_hidden">
                    <div id="search-results" class="list-group position-absolute" style="z-index: 1000;"></div>
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
    const searchInput = document.getElementById('user_search_input');
    const hiddenInput = document.getElementById('user_id_hidden');
    const resultsContainer = document.getElementById('search-results');
    const roleSelect = document.getElementById('role');
    let debounceTimer;

    function toggleIndividualSearch() {
        const scope = document.querySelector('input[name="generation_scope"]:checked').value;
        if (scope === 'individual') {
            userContainer.style.display = 'block';
            searchInput.disabled = !roleSelect.value;
        } else {
            userContainer.style.display = 'none';
            searchInput.disabled = true;
            searchInput.value = '';
            hiddenInput.value = '';
            resultsContainer.innerHTML = '';
        }
    }

    scopeRadios.forEach(radio => radio.addEventListener('change', toggleIndividualSearch));
    roleSelect.addEventListener('change', function() {
        searchInput.value = '';
        hiddenInput.value = '';
        resultsContainer.innerHTML = '';
        toggleIndividualSearch();
    });

    searchInput.addEventListener('input', function() {
        const query = this.value;
        const role = roleSelect.value;
        hiddenInput.value = ''; // Clear hidden ID when user types

        clearTimeout(debounceTimer);
        if (query.length < 2) {
            resultsContainer.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`api_search_users.php?role=${role}&query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = '';
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }
                    if (data.length > 0) {
                        data.forEach(user => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.className = 'list-group-item list-group-item-action';
                            item.textContent = `${user.first_name} ${user.last_name} (${user.lin || 'No LIN'})`;
                            item.dataset.id = user.id;
                            item.dataset.name = `${user.first_name} ${user.last_name}`;
                            resultsContainer.appendChild(item);
                        });
                    } else {
                        resultsContainer.innerHTML = '<span class="list-group-item">No results found</span>';
                    }
                });
        }, 300); // Debounce for 300ms
    });

    resultsContainer.addEventListener('click', function(e) {
        e.preventDefault();
        const target = e.target;
        if (target.matches('a.list-group-item-action')) {
            searchInput.value = target.dataset.name;
            hiddenInput.value = target.dataset.id;
            resultsContainer.innerHTML = '';
        }
    });

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!userContainer.contains(e.target)) {
            resultsContainer.innerHTML = '';
        }
    });
});
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>
