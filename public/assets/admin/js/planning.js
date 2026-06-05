document.getElementById('addToggle').addEventListener('click', function (e) {
  e.stopPropagation();
  document.getElementById('addDropdown').classList.toggle('open');
});
document.addEventListener('click', () => document.getElementById('addDropdown').classList.remove('open'));

function checkConflict() {
  const warn = document.getElementById('conflict-warning');
  const btn  = document.getElementById('btn-submit-planning');

  const modalDate = document.getElementById('f-date')?.value;
  if (modalDate && modalDate !== PAGE_DATE) {
    warn.style.display = 'none';
    if (btn) { btn.disabled = false; btn.style.opacity = ''; }
    return;
  }

  const ivId   = document.getElementById('f-iv').value;
  const hDebut = parseInt(document.getElementById('f-h-debut').value);
  const hFin   = parseInt(document.getElementById('f-h-fin').value);
  const occ    = (OCCUPIED[ivId] || []).map(Number);
  const ivData = SOIGNANTS_DATA.find(s => String(s.id) === String(ivId));
  let conflict = false;
  let conflictMsg = '⚠ Ce soignant est déjà occupé sur ce créneau — choisissez un autre horaire ou un autre soignant.';
  if (ivData && !ivData.disponible) {
    const sl = {conge:'En congé', arret:'Arrêt maladie', formation:'En formation', indisponible:'Indisponible'};
    conflict = true;
    conflictMsg = '⚠ ' + ivData.nom + ' est actuellement : ' + (sl[ivData.statut] || 'Indisponible') + ' — impossible d\'assigner une intervention.';
  } else {
    for (let h = hDebut; h < hFin; h++) {
      if (occ.includes(h)) { conflict = true; break; }
    }
  }
  warn.textContent = conflictMsg;
  warn.style.display = conflict ? 'block' : 'none';
  if (btn) { btn.disabled = conflict; btn.style.opacity = conflict ? '0.5' : ''; }
}

function openInterventionModal(date, ivId, heure) {
  if (date)  document.getElementById('f-date').value   = date;
  if (ivId)  document.getElementById('f-iv').value     = ivId;
  if (heure !== undefined) {
    document.getElementById('f-h-debut').value = heure;
    document.getElementById('f-h-fin').value   = heure + 1;
  }
  checkConflict();
  document.getElementById('modalIntervention').style.display = 'flex';
}
function closeModal() {
  document.getElementById('modalIntervention').style.display = 'none';
}
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { closeModal(); closeReassignModal(); }
});

function openReassignModal(intervId, patient, hdebut, hfin, currentIvId) {
  const hD = parseInt(hdebut.split(':')[0]);
  const hF = parseInt(hfin.split(':')[0]);

  document.getElementById('reassign-info').innerHTML =
    '<strong>' + escH(patient) + '</strong> · ' + hdebut + '–' + hfin;

  document.getElementById('reassign-form').action =
    '/admin/planning/intervention/' + intervId + '/reassigner';

  const list = document.getElementById('reassign-list');
  list.innerHTML = '';
  document.getElementById('reassign-conflict-warn').style.display = 'none';

  SOIGNANTS_DATA.forEach(function (s) {
    if (s.id === currentIvId) return;

    const occ = (OCCUPIED[s.id] || []).map(Number);
    let timeConflict = false;
    for (let h = hD; h < hF; h++) { if (occ.includes(h)) { timeConflict = true; break; } }
    const unavail = !s.disponible;
    const conflict = timeConflict || unavail;

    const statutLabels = {conge:'En congé', arret:'Arrêt maladie', formation:'En formation', indisponible:'Indisponible'};
    const badgeLabel = unavail
      ? (statutLabels[s.statut] || 'Indisponible')
      : (timeConflict ? 'Occupé' : 'Disponible');

    const item = document.createElement('div');
    item.className = 'reassign-item' + (conflict ? ' conflict' : '');

    item.innerHTML =
      '<div class="reassign-avatar" style="background:' + s.color + '">' + escH(s.initiales) + '</div>'
      + '<div><div class="reassign-name">' + escH(s.nom) + '</div>'
      + '<div class="reassign-spe">' + escH(s.specialite) + '</div></div>'
      + '<span class="' + (conflict ? 'reassign-badge-occ' : 'reassign-badge-dispo') + '">'
      + badgeLabel + '</span>';

    if (!conflict) {
      item.onclick = function () {
        const form = document.getElementById('reassign-form');
        let inp = form.querySelector('input[name="iv_id"]');
        if (!inp) { inp = document.createElement('input'); inp.type = 'hidden'; inp.name = 'iv_id'; form.appendChild(inp); }
        inp.value = s.id;
        form.submit();
      };
    }

    list.appendChild(item);
  });

  document.getElementById('modalReassign').style.display = 'flex';
}

function closeReassignModal() {
  document.getElementById('modalReassign').style.display = 'none';
}

function escH(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

document.getElementById('f-h-debut').addEventListener('change', function () {
  const fin = document.getElementById('f-h-fin');
  if (parseInt(fin.value) <= parseInt(this.value)) fin.value = parseInt(this.value) + 1;
  checkConflict();
});
document.getElementById('f-h-fin').addEventListener('change', checkConflict);
document.getElementById('f-iv').addEventListener('change', checkConflict);
document.getElementById('f-date').addEventListener('change', checkConflict);

document.querySelectorAll('.iv-checkbox').forEach(cb => {
  cb.addEventListener('change', function () {
    const id = this.dataset.iv;
    document.querySelectorAll('[data-iv="' + id + '"]').forEach(el => {
      el.style.display = this.checked ? '' : 'none';
    });
  });
});
