(() => {
  'use strict';
  const app = document.querySelector('#users-app'); if (!app) return;
  const csrf = app.dataset.csrf; const body = document.querySelector('#users-body'); const form = document.querySelector('#users-search-form');
  const dialog = document.querySelector('#edit-user-dialog'); const editForm = document.querySelector('#edit-user-form'); const message = document.querySelector('#users-message');
  let users = [];
  const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
  const showMessage = (text, type = 'success') => { message.textContent = text; message.className = `alert ${type}`; message.hidden = false; };
  const request = async (url, options = {}) => {
    const response = await fetch(url, {credentials: 'same-origin', ...options}); let data;
    try { data = await response.json(); } catch { throw new Error('The server returned an invalid response.'); }
    if (!response.ok || !data.success) throw new Error(data.error || 'Request failed.'); return data;
  };
  const render = () => {
    if (!users.length) { body.innerHTML = '<tr><td colspan="6" class="empty">No users found.</td></tr>'; return; }
    body.innerHTML = users.map((item) => `<tr><td>#${Number(item.id)}</td><td>${escapeHtml(item.name)}</td><td>${escapeHtml(item.email)}</td>
      <td><span class="role-badge">${escapeHtml(item.role)}</span></td><td>${escapeHtml(item.created_at)}</td>
      <td><button class="text-button" type="button" data-edit-user="${Number(item.id)}">Edit</button></td></tr>`).join('');
  };
  const load = async () => {
    body.innerHTML = '<tr><td colspan="6" class="empty">Loading users…</td></tr>'; message.hidden = true;
    const q = encodeURIComponent(document.querySelector('#users-search').value.trim());
    try { const data = await request(`api/users.php?q=${q}`); users = data.users; render(); }
    catch (error) { body.innerHTML = '<tr><td colspan="6" class="empty">Could not load users.</td></tr>'; showMessage(error.message, 'error'); }
  };
  form.addEventListener('submit', (event) => { event.preventDefault(); load(); });
  body.addEventListener('click', (event) => {
    const button = event.target.closest('[data-edit-user]'); if (!button) return;
    const item = users.find((x) => Number(x.id) === Number(button.dataset.editUser)); if (!item) return;
    document.querySelector('#user-id').value = item.id; document.querySelector('#user-name').value = item.name;
    document.querySelector('#user-email').value = item.email; document.querySelector('#user-role').value = item.role; dialog.showModal();
  });
  dialog.querySelectorAll('[data-close]').forEach((button) => button.addEventListener('click', () => dialog.close()));
  editForm.addEventListener('submit', async (event) => {
    event.preventDefault(); const payload = {id: Number(document.querySelector('#user-id').value), name: document.querySelector('#user-name').value, email: document.querySelector('#user-email').value};
    try {
      const data = await request('api/users.php', {method: 'PATCH', headers: {'Content-Type': 'application/json', 'X-CSRF-Token': csrf}, body: JSON.stringify(payload)});
      dialog.close(); showMessage(data.message); await load();
    } catch (error) { showMessage(error.message, 'error'); }
  });
  load();
})();
