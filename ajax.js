// assets/js/ajax.js

// Function to fetch notices from the database dynamically
function fetchNotices(search = '', category = '', priority = '') {
    const list = document.getElementById("noticeList");
    
    // Create query string for filtering
    const query = `search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}&priority=${encodeURIComponent(priority)}`;

    // AJAX call to the new bridge file we tested
    fetch(`includes/get_notices.php?${query}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            list.innerHTML = ""; // Clear "Loading notices..."

            if (data.length === 0) {
                list.innerHTML = "<div class='col-12'><p class='text-center mt-5 text-muted'>No notices found matching your criteria.</p></div>";
                return;
            }

            data.forEach(n => {
                // Determine badge color based on priority
                const priorityClass = n.priority.toLowerCase() === 'high' ? 'danger' : 
                                    (n.priority.toLowerCase() === 'medium' ? 'warning' : 'info');

                list.innerHTML += `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-light text-dark border">${n.category_name}</span>
                                <span class="badge bg-${priorityClass}">${n.priority}</span>
                            </div>
                            <h5 class="card-title text-primary">${n.title}</h5>
                            <p class="card-text text-muted" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                ${n.content}
                            </p>
                        </div>
                        <div class="card-footer bg-white border-top-0 pb-3">
                            <a href="view-notice.php?id=${n.notice_id}" class="btn btn-sm btn-outline-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>`;
            });
        })
        .catch(error => {
            console.error('Error loading notices:', error);
            list.innerHTML = `<div class='col-12'><p class='text-center text-danger'>Error loading notices. Please check your database connection.</p></div>`;
        });
}

// Function to post a comment via AJAX
function postComment(noticeId, content) {
    if (!content.trim()) {
        alert("Please enter a comment.");
        return;
    }

    const formData = new FormData();
    formData.append('notice_id', noticeId);
    formData.append('content', content);

    fetch('includes/post_comment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Comment submitted! It will appear after admin approval.");
            location.reload(); 
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error('Error posting comment:', error));
}