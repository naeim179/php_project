(() => {
  'use strict';
  const app = document.querySelector('#admin-app');
  if (!app) return;
  const csrf = app.dataset.csrf;
  const body = document.querySelector('#complaints-body');
  const message = document.querySelector('#admin-message');
  const filter = document.querySelector('#complaint-filter');
  const dialog = document.querySelector('#edit-complaint-dialog');
  const editForm = document.querySelector('#edit-complaint-form');
  let complaints = [];
  const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
  const labels = {pending: 'Pending', in_progress: 'In Progress', resolved: 'Resolved'};
  const showMessage = (text, type = 'success') => { message.textContent = text; message.className = `alert ${type}`; message.hidden = false; };
  const request = async (url, options = {}) => {
    const response = await fetch(url, {credentials: 'same-origin', ...options});
    let data;
    try { data = await response.json(); } catch { throw new Error('The server returned an invalid response.'); }
    if (!response.ok || !data.success) throw new Error(data.error || 'Request failed.');
    return data;
  };
  const updateCounts = (summary) => {
    document.querySelector('#count-all').textContent = Number(summary.total || 0);
    document.querySelector('#count-pending').textContent = Number(summary.pending || 0);
    document.querySelector('#count-progress').textContent = Number(summary.in_progress || 0);
    document.querySelector('#count-resolved').textContent = Number(summary.resolved || 0);
  };
  const render = () => {
    if (!complaints.length) { body.innerHTML = '<tr><td colspan="7" class="empty">No complaints found.</td></tr>'; return; }
    body.innerHTML = complaints.map((item) => `<tr>
      <td>#${Number(item.id)}</td><td class="truncate">${escapeHtml(item.title)}</td>
      <td>${escapeHtml(item.student_name)}<div class="tiny">${escapeHtml(item.student_email)}</div></td>
      <td>${escapeHtml(item.category)}</td><td><span class="badge ${escapeHtml(item.status)}">${escapeHtml(labels[item.status] || item.status)}</span></td>
      <td>${escapeHtml(item.created_at)}</td><td><div class="row-actions">
        <a href="complaint.php?id=${Number(item.id)}">View</a><button type="button" data-edit="${Number(item.id)}">Edit</button>
        <a href="complaint.php?id=${Number(item.id)}#update">Update</a><button class="danger-link" type="button" data-delete="${Number(item.id)}">Delete</button>
      </div></td></tr>`).join('');
  };
  const load = async () => {
    body.innerHTML = '<tr><td colspan="7" class="empty">Loading complaints…</td></tr>'; message.hidden = true;
    const params = new URLSearchParams(new FormData(filter));
    try { const data = await request(`api/complaints.php?${params}`); complaints = data.complaints; updateCounts(data.summary || {}); render(); }
    catch (error) { body.innerHTML = '<tr><td colspan="7" class="empty">Could not load complaints.</td></tr>'; showMessage(error.message, 'error'); }
  };
  filter.addEventListener('submit', (event) => { event.preventDefault(); load(); });
  body.addEventListener('click', async (event) => {
    const editButton = event.target.closest('[data-edit]');
    const deleteButton = event.target.closest('[data-delete]');
    if (editButton) {
      const item = complaints.find((x) => Number(x.id) === Number(editButton.dataset.edit)); if (!item) return;
      document.querySelector('#edit-id').value = item.id; document.querySelector('#edit-title').value = item.title;
      document.querySelector('#edit-category').value = item.category; document.querySelector('#edit-description').value = item.description;
      dialog.showModal();
    }
    if (deleteButton) {
      const id = Number(deleteButton.dataset.delete); const item = complaints.find((x) => Number(x.id) === id);
      if (!item || !window.confirm(`Delete complaint #${id} “${item.title}” and all its responses? This cannot be undone.`)) return;
      deleteButton.disabled = true;
      try { const data = await request(`api/complaints.php?id=${id}`, {method: 'DELETE', headers: {'X-CSRF-Token': csrf}}); showMessage(data.message); await load(); }
      catch (error) { showMessage(error.message, 'error'); deleteButton.disabled = false; }
    }
  });
  dialog.querySelectorAll('[data-close]').forEach((button) => button.addEventListener('click', () => dialog.close()));
  editForm.addEventListener('submit', async (event) => {
    event.preventDefault(); const id = Number(document.querySelector('#edit-id').value);
    const payload = {title: document.querySelector('#edit-title').value, category: document.querySelector('#edit-category').value, description: document.querySelector('#edit-description').value};
    try {
      const data = await request(`api/complaints.php?id=${id}`, {method: 'PATCH', headers: {'Content-Type': 'application/json', 'X-CSRF-Token': csrf}, body: JSON.stringify(payload)});
      dialog.close(); showMessage(data.message); await load();
    } catch (error) { showMessage(error.message, 'error'); }
  });
  load();
})();
