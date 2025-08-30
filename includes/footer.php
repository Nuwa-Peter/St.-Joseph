</main>
            <footer class="footer mt-auto py-3 bg-light">
              <div class="container text-center">
                <span class="text-muted">St. Joseph's Vocational SS Nyamityobora &copy; <?php echo date('Y'); ?>. All Rights Reserved.</span>
              </div>
            </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="assets/libs/cropperjs/cropper.min.js"></script>
    <!-- FullCalendar JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/main.min.js"></script>

    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
        <!-- Floating Chat Button -->
        <a href="messages.php" class="floating-chat-btn">
            <i class="bi bi-chat-dots-fill"></i>
        </a>
    <?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('live-search-input');
    const searchResults = document.getElementById('live-search-results');
    let debounceTimer;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();

            clearTimeout(debounceTimer);

            if (query.length > 1) {
                debounceTimer = setTimeout(() => {
                    fetch(`api_live_search.php?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            searchResults.innerHTML = '';
                            if (Array.isArray(data) && data.length > 0) {
                                data.forEach(user => {
                                    const a = document.createElement('a');
                                    let href = 'profile.php?id=' + user.id;
                                    if (user.role === 'student') {
                                        href = 'student_view.php?id=' + user.id;
                                    }

                                    a.href = href;
                                    a.classList.add('list-group-item', 'list-group-item-action');

                                    let content = `<div class="d-flex align-items-center">`;
                                    if (user.photo && user.photo.length > 0) {
                                        content += `<img src="${user.photo}" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">`;
                                    } else {
                                        const initials = (user.first_name.charAt(0) + user.last_name.charAt(0)).toUpperCase();
                                        content += `<div class="avatar-initials-sm me-2" style="width: 32px; height: 32px; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; border-radius: 50%; background-color: #eee; color: #333;">${initials}</div>`;
                                    }
                                    content += `<div><strong>${user.first_name} ${user.last_name}</strong><br><small class="text-muted">${user.role}</small></div></div>`;

                                    a.innerHTML = content;
                                    searchResults.appendChild(a);
                                });
                                searchResults.style.display = 'block';
                            } else {
                                searchResults.innerHTML = '<span class="list-group-item disabled">No results found.</span>';
                                searchResults.style.display = 'block';
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching search results:', error);
                            searchResults.style.display = 'none';
                        });
                }, 300);
            } else {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
            }
        });

        document.addEventListener('click', function(event) {
            if (!searchResults.contains(event.target) && event.target !== searchInput) {
                searchResults.style.display = 'none';
            }
        });
    }
});
</script>
</body>
</html>
