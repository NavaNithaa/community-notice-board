async function loadNotices() {
    const list = document.getElementById("noticeList");
    try {
        const response = await fetch('includes/functions.php?action=get_notices');
        const text = await response.text(); // get raw text first

        let notices = [];
        try {
            notices = JSON.parse(text); // parse safely
        } catch (e) {
            console.error("Invalid JSON from server:", text);
            list.innerHTML = `<div class="col-12"><p class="text-center text-danger">Error loading notices</p></div>`;
            return;
        }

        list.innerHTML = "";
        if (!notices || notices.length === 0) {
            list.innerHTML = `<div class="col-12"><p class="text-center text-muted">No notices found.</p></div>`;
            return;
        }

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
    } catch (err) {
        console.error("Fetch error:", err);
        list.innerHTML = `<div class="col-12"><p class="text-center text-danger">Error loading notices</p></div>`;
    }
}

document.addEventListener("DOMContentLoaded", loadNotices);