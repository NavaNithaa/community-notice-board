// assets/js/ajax.js

// Function to fetch notices from the database dynamically
function fetchNotices(search = '', category = '', priority = '') {
    const list = document.getElementById("noticeList");

    // Build query string
    const query = `search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}&priority=${encodeURIComponent(priority)}`;

    fetch(`get_notices.php?${query}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            list.innerHTML = ""; // Clear list

            if (data.length === 0) {
                list.innerHTML = `<div class='col-12'><p class='text-center mt-5 text-muted'>No notices found matching your criteria.</p></div>`;
                return;
            }

            data.forEach(n => {
                const priorityClass = n.priority.toLowerCase() === 'high' ? 'danger' :
                                      n.priority.toLowerCase() === 'medium' ? 'warning' : 'info';

                const bmClass = n.isBookmarked ? 'btn-success' : 'btn-outline-secondary';
                const bmText = n.isBookmarked ? 'Bookmarked ✅' : 'Bookmark';

                list.innerHTML += `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 notice-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-light text-dark border">${n.category_name}</span>
                                <span class="badge bg-${priorityClass}">${n.priority}</span>
                            </div>
                            <h5 class="card-title text-primary">${n.title}</h5>
                            <p class="card-text text-muted" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                ${n.content}
                            </p>
                            <button class="btn btn-sm ${bmClass} bookmark-btn" data-id="${n.notice_id}">${bmText}</button>
                        </div>
                        <div class="card-footer bg-white border-top-0 pb-3">
                            <a href="view-notice.php?id=${n.notice_id}" class="btn btn-sm btn-outline-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>`;
            });

            // Add bookmark button listeners
            document.querySelectorAll('.bookmark-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const noticeId = btn.getAttribute('data-id');

                    fetch('includes/functions.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'toggle_bookmark=1&notice_id=' + noticeId
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'added') {
                            btn.classList.remove('btn-outline-secondary');
                            btn.classList.add('btn-success');
                            btn.innerText = 'Bookmarked ✅';
                        } else if (data.status === 'removed') {
                            btn.classList.remove('btn-success');
                            btn.classList.add('btn-outline-secondary');
                            btn.innerText = 'Bookmark';
                        }
                    })
                    .catch(err => console.error('Error toggling bookmark:', err));
                });
            });
        })
        .catch(error => {
            console.error('Error loading notices:', error);
            list.innerHTML = `<div class='col-12'><p class='text-center text-danger'>Error loading notices. Please check your database connection.</p></div>`;
        });
}
