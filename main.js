// Function to fetch real notices from the database
async function loadNotices() {
    // This calls your PHP backend instead of using the fake array
    const response = await fetch('includes/functions.php?action=get_notices');
    const notices = await response.json();
    
    const list = document.getElementById("noticeList");
    list.innerHTML = "";
    
    notices.forEach(n => {
        list.innerHTML += `
        <div class="col-md-6 mb-3">
            <div class="card notice-card notice-${n.priority.toLowerCase()} shadow-sm">
                <div class="card-body">
                    <h5>${n.title}</h5>
                    <span>${n.category_name}</span> | <span>${n.priority}</span><br>
                    <a href="view-notice.php?id=${n.notice_id}" class="btn btn-sm btn-primary mt-2">View Details</a>
                </div>
            </div>
        </div>`;
    });
}

document.addEventListener("DOMContentLoaded", loadNotices);