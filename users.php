<?php
require_once 'helpers.php';
$user = require_role('admin');
page_top('User Management');
?>
<div id="users-app" data-csrf="<?= h(csrf_token()) ?>">
    <div class="split">
        <div><div class="eyebrow">Administration</div><h1>User Management</h1><p class="sub">View and update registered students and administrators.</p></div>
        <a class="btn secondary" href="admin.php">Back to Complaints</a>
    </div>
    <div id="users-message" class="alert" hidden></div>
    <section class="panel">
        <div class="panel-header"><h2>Registered Users</h2>
            <form id="users-search-form" class="filter" role="search">
                <label class="sr-only" for="users-search">Search users</label>
                <input class="field search-field" id="users-search" type="search" maxlength="100" placeholder="Search name or email…">
                <button class="btn secondary" type="submit">Search</button>
            </form>
        </div>
        <div class="table-wrap"><table>
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Registered</th><th>Action</th></tr></thead>
            <tbody id="users-body"><tr><td colspan="6" class="empty">Loading users…</td></tr></tbody>
        </table></div>
    </section>
    <dialog id="edit-user-dialog" class="modal">
        <form id="edit-user-form">
            <div class="modal-head"><h2>Edit User</h2><button class="icon-btn" type="button" data-close aria-label="Close">×</button></div>
            <input id="user-id" type="hidden">
            <div class="form-field"><label for="user-name">Name</label><input class="field" id="user-name" maxlength="100" required></div>
            <div class="form-field"><label for="user-email">Email</label><input class="field" id="user-email" type="email" maxlength="255" required></div>
            <div class="form-field"><label>Role</label><input class="field" id="user-role" disabled><div class="help">Roles are protected and cannot be changed from this screen.</div></div>
            <div class="actions"><button class="btn" type="submit">Save User</button><button class="btn secondary" type="button" data-close>Cancel</button></div>
        </form>
    </dialog>
</div>
<script src="assets/users.js" defer></script>
<?php page_bottom(); ?>
