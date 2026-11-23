<?php
require_once 'helpers.php';
$user = require_role('admin');
page_top('Admin Dashboard');
?>
<div id="admin-app" data-csrf="<?= h(csrf_token()) ?>">
    <div class="split">
        <div>
            <div class="eyebrow">Administration</div>
            <h1>Complaint Management</h1>
            <p class="sub">Search, review, edit and safely resolve student complaints.</p>
        </div>
        <a class="btn secondary" href="users.php">Manage Users</a>
    </div>
    <div class="grid admin-metrics">
        <div class="metric"><span>All Complaints</span><strong id="count-all">—</strong></div>
        <div class="metric"><span>Pending</span><strong id="count-pending">—</strong></div>
        <div class="metric"><span>In Progress</span><strong id="count-progress">—</strong></div>
        <div class="metric"><span>Resolved</span><strong id="count-resolved">—</strong></div>
    </div>
    <div id="admin-message" class="alert" hidden></div>
    <section class="panel">
        <div class="panel-header">
            <h2>Student Complaints</h2>
            <form id="complaint-filter" class="filter" role="search">
                <label class="sr-only" for="complaint-search">Search complaints</label>
                <input class="field search-field" id="complaint-search" name="q" type="search" maxlength="100" placeholder="Search title or student…">
                <label class="sr-only" for="status-filter">Status</label>
                <select class="field" id="status-filter" name="status">
                    <option value="all">All statuses</option><option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option><option value="resolved">Resolved</option>
                </select>
                <button class="btn secondary" type="submit">Search</button>
            </form>
        </div>
        <p class="table-hint">Swipe the table to see all columns.</p>
        <div class="table-wrap"><table>
            <thead><tr><th>ID</th><th>Complaint</th><th>Student</th><th>Category</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody id="complaints-body"><tr><td colspan="7" class="empty">Loading complaints…</td></tr></tbody>
        </table></div>
    </section>
    <dialog id="edit-complaint-dialog" class="modal">
        <form id="edit-complaint-form">
            <div class="modal-head"><h2>Edit Complaint</h2><button class="icon-btn" type="button" data-close aria-label="Close">×</button></div>
            <input id="edit-id" type="hidden">
            <div class="form-field"><label for="edit-title">Title</label><input class="field" id="edit-title" maxlength="200" required></div>
            <div class="form-field"><label for="edit-category">Category</label><input class="field" id="edit-category" maxlength="100" required></div>
            <div class="form-field"><label for="edit-description">Description</label><textarea class="field" id="edit-description" minlength="10" maxlength="5000" required></textarea></div>
            <div class="actions"><button class="btn" type="submit">Save Changes</button><button class="btn secondary" type="button" data-close>Cancel</button></div>
        </form>
    </dialog>
</div>
<script src="assets/admin.js" defer></script>
<?php page_bottom(); ?>
