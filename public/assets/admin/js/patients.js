function selectPatient(id) {
  document.querySelectorAll('.patient-item').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.patient-detail').forEach(el => el.classList.remove('visible'));
  const item = document.querySelector('.patient-item[data-id="' + id + '"]');
  const detail = document.getElementById('detail-' + id);
  if (item) item.classList.add('active');
  if (detail) detail.classList.add('visible');
}

function showTab(tabName, patientId) {
  const detail = document.getElementById('detail-' + patientId);
  if (!detail) return;
  detail.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
  detail.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
  const pane = document.getElementById('tab-' + tabName + '-' + patientId);
  if (pane) pane.classList.add('active');
  event.currentTarget.classList.add('active');
}

function setFilter(filter, btn) {
  document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}

document.getElementById('searchInput').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.patient-item').forEach(item => {
    const nom = item.querySelector('.patient-nom').textContent.toLowerCase();
    item.style.display = nom.includes(q) ? '' : 'none';
  });
});

function openCreateModal() {
  const form = document.getElementById('modal-form');
  form.action = PATIENT_NEW_URL;
  document.getElementById('modal-token').value = PATIENT_NEW_TOKEN;
  document.getElementById('modal-title').textContent = 'Nouveau dossier';
  document.getElementById('password-label').textContent = 'Mot de passe';
  document.getElementById('f-password').required = true;
  document.getElementById('f-password').placeholder = '••••••••';
  form.reset();
  document.getElementById('f-risque').value = 'modere';
  document.getElementById('f-rdv').value = '';
  document.getElementById('modal-patient').style.display = 'flex';
}

function openEditModal(id) {
  const detail = document.getElementById('detail-' + id);
  const form = document.getElementById('modal-form');
  form.action = detail.dataset.editUrl;
  document.getElementById('modal-token').value = detail.dataset.editToken;
  document.getElementById('modal-title').textContent = 'Modifier le dossier';
  document.getElementById('password-label').textContent = 'Nouveau mot de passe (laisser vide pour ne pas changer)';
  document.getElementById('f-password').required = false;
  document.getElementById('f-password').placeholder = 'Laisser vide pour ne pas changer';
  document.getElementById('f-nom').value = detail.dataset.nom;
  document.getElementById('f-email').value = detail.dataset.email;
  document.getElementById('f-password').value = '';
  document.getElementById('f-datenaissance').value = detail.dataset.datenaissance;
  document.getElementById('f-adresse').value = detail.dataset.adresse;
  document.getElementById('f-pathologie').value = detail.dataset.pathologie;
  document.getElementById('f-risque').value = detail.dataset.risque;
  document.getElementById('f-rdv').value = detail.dataset.rdv || '';
  document.getElementById('modal-patient').style.display = 'flex';
}

function closeModal() {
  document.getElementById('modal-patient').style.display = 'none';
}

function openDeleteModal(id, nom) {
  const detail = document.getElementById('detail-' + id);
  document.getElementById('delete-name').textContent = nom;
  document.getElementById('delete-form').action = detail.dataset.deleteUrl;
  document.getElementById('delete-token').value = detail.dataset.deleteToken;
  document.getElementById('modal-delete').style.display = 'flex';
}

function closeDeleteModal() {
  document.getElementById('modal-delete').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') { closeModal(); closeDeleteModal(); }
});
