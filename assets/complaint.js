(() => {
  'use strict';
  const form = document.querySelector('#update-complaint-form');
  if (!form) return;
  const message = document.querySelector('#update-message');
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    message.hidden = true;
    try {
      const response = await fetch('api/update_complaint.php', {
        method: 'PATCH', credentials: 'same-origin',
        headers: {'Content-Type': 'application/json', 'X-CSRF-Token': form.dataset.csrf},
        body: JSON.stringify({id: Number(form.dataset.complaintId), status: form.status.value, message: form.message.value})
      });
      let data;
      try { data = await response.json(); } catch { throw new Error('The server returned an invalid response.'); }
      if (!response.ok || !data.success) throw new Error(data.error || 'Could not save the update.');
      window.location.assign(`complaint.php?id=${Number(form.dataset.complaintId)}&saved=1#update`);
    } catch (error) {
      message.textContent = error.message; message.className = 'alert error'; message.hidden = false; button.disabled = false;
    }
  });
})();
