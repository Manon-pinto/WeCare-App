function checkConflictDash() {
  const warn = document.getElementById('dash-conflict-warning');
  const btn  = document.getElementById('btn-submit-dash');
  if (!warn || !btn) return;

  const modalDate = document.querySelector('#modalIntervention input[name="date"]')?.value;
  if (modalDate && modalDate !== TODAY_STR) {
    warn.style.display = 'none';
    btn.disabled = false; btn.style.opacity = '';
    return;
  }

  const ivId   = document.querySelector('#modalIntervention select[name="iv_id"]')?.value;
  const hDebut = parseInt(document.getElementById('dash-h-debut')?.value);
  const hFin   = parseInt(document.getElementById('dash-h-fin')?.value);
  if (!ivId) return;
  const occ = (OCCUPIED_DASH[ivId] || []).map(Number);
  let conflict = false;
  for (let h = hDebut; h < hFin; h++) {
    if (occ.includes(h)) { conflict = true; break; }
  }
  warn.style.display = conflict ? 'block' : 'none';
  btn.disabled = conflict;
  btn.style.opacity = conflict ? '0.5' : '';
}

document.getElementById('dash-h-debut')?.addEventListener('change', function () {
  const fin = document.getElementById('dash-h-fin');
  if (parseInt(fin.value) <= parseInt(this.value)) fin.value = parseInt(this.value) + 1;
  checkConflictDash();
});
document.getElementById('dash-h-fin')?.addEventListener('change', checkConflictDash);
document.querySelector('#modalIntervention select[name="iv_id"]')?.addEventListener('change', checkConflictDash);
document.querySelector('#modalIntervention input[name="date"]')?.addEventListener('change', checkConflictDash);

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.getElementById('modalIntervention').style.display = 'none';
});

(function () {
  const avatarRow = document.querySelector('.cal-avatar-row');
  const calGrid   = document.querySelector('.cal-grid');

  function rebuildCols() {
    const boxes = document.querySelectorAll('.int-row input[type="checkbox"]');
    if (!boxes.length) return;

    const cols = ['32px'];
    boxes.forEach(cb => {
      cols.push(cb.checked ? '1fr' : '0px');
      cb.closest('.int-row').classList.toggle('checked', cb.checked);
    });

    const colStr = cols.join(' ');
    if (avatarRow) avatarRow.style.gridTemplateColumns = colStr;
    if (calGrid)   calGrid.style.gridTemplateColumns   = colStr;
  }

  document.querySelectorAll('.int-row input[type="checkbox"]').forEach(cb => {
    cb.addEventListener('change', rebuildCols);
  });
})();
