let selectedCreneaux = {};

if (OPEN_ROW) {
  const expandRow = document.getElementById('expand-' + OPEN_ROW);
  const dataRow   = document.getElementById('row-' + OPEN_ROW);
  if (expandRow && dataRow) {
    expandRow.style.display = '';
    dataRow.classList.add('selected');
    setTimeout(() => dataRow.scrollIntoView({ behavior: 'smooth', block: 'center' }), 100);
  }
}

function toggleExpand(id, benId, row) {
  const expandRow = document.getElementById('expand-' + id);
  const isOpen    = expandRow.style.display !== 'none';

  document.querySelectorAll('.expand-row').forEach(r => r.style.display = 'none');
  document.querySelectorAll('.data-row').forEach(r => r.classList.remove('selected'));

  if (!isOpen) {
    expandRow.style.display = '';
    document.getElementById('row-' + id).classList.add('selected');
    renderSoignants(id, benId, selectedCreneaux[id] ?? null);
  }
}

function closeExpand(id) {
  document.getElementById('expand-' + id).style.display = 'none';
  document.getElementById('row-' + id).classList.remove('selected');
  closeAssignSection(id);
}

let pendingIntervId = {};

function selectCreneau(rowId, h, intervId, benId) {
  selectedCreneaux[rowId] = h;
  if (intervId) pendingIntervId[rowId] = intervId;
  else delete pendingIntervId[rowId];

  const section = document.getElementById('assign-section-' + rowId);
  if (section) section.style.display = '';

  const resolvedBenId = benId || parseInt(
    document.querySelector('[data-row="' + rowId + '"]')?.dataset.ben || '0'
  );
  renderSoignants(rowId, resolvedBenId, h);

  section.scrollIntoView({behavior: 'smooth', block: 'nearest'});
}

function closeAssignSection(rowId) {
  const section = document.getElementById('assign-section-' + rowId);
  if (section) section.style.display = 'none';
  selectedCreneaux[rowId] = null;
  delete pendingIntervId[rowId];
  document.getElementById('soignant-list-' + rowId).innerHTML = '';
}

function renderSoignants(rowId, benId, selectedH) {
  const label = document.getElementById('dispo-label-' + rowId);
  const list  = document.getElementById('soignant-list-' + rowId);
  const existingIntervId = pendingIntervId[rowId] || null;

  const hLabel = selectedH !== null ? pad(selectedH) + 'h–' + pad(selectedH + 1) + 'h' : '';
  label.textContent = 'Soignants disponibles' + (hLabel ? ' — ' + hLabel : '');
  list.innerHTML = '';

  SOIGNANTS.forEach(function (s) {
    const occ          = (s.occupiedHours || []).map(Number);
    const timeConflict = selectedH !== null && occ.includes(selectedH);
    const unavail      = !s.disponible;
    const blocked      = timeConflict || unavail;

    const statutLabels = {conge:'En congé', arret:'Arrêt maladie', formation:'En formation', indisponible:'Indisponible'};

    const row = document.createElement('div');
    row.className = 'soignant-row' + (blocked ? ' occupied' : '');

    const av = document.createElement('div');
    av.className = 'sv-avatar';
    av.style.background = s.color;
    av.textContent = s.initiales;
    row.appendChild(av);

    const info = document.createElement('div');
    info.className = 'sv-info';
    info.innerHTML = '<div class="sv-name">' + escHtml(s.nom) + '</div>'
      + '<div class="sv-meta">' + escHtml(s.specialite) + ' · ' + s.rayon.toFixed(1) + ' km</div>';
    row.appendChild(info);

    const actions = document.createElement('div');
    actions.style.cssText = 'display:flex;align-items:center;gap:8px;margin-left:auto;';

    const badgeLabel = unavail ? (statutLabels[s.statut] || 'Indisponible') : (timeConflict ? 'Occupé' : 'Disponible');
    const badge = document.createElement('span');
    badge.className = 'sv-badge ' + (blocked ? 'occupé' : 'dispo');
    badge.textContent = badgeLabel;
    actions.appendChild(badge);

    if (!blocked) {
      const form = document.createElement('form');
      form.method = 'POST';

      if (existingIntervId) {
        form.action = '/admin/planning/intervention/' + existingIntervId + '/reassigner';
        form.innerHTML =
          '<input type="hidden" name="iv_id" value="' + s.id + '">'
          + '<input type="hidden" name="date" value="' + PAGE_DATE + '">'
          + '<input type="hidden" name="row_id" value="' + rowId + '">'
          + '<button type="submit" class="btn-assigner-sv">Assigner</button>';
      } else {
        form.action = '/admin/prises-en-charge/planifier';
        form.innerHTML =
          '<input type="hidden" name="ben_id" value="' + benId + '">'
          + '<input type="hidden" name="iv_id" value="' + s.id + '">'
          + '<input type="hidden" name="date" value="' + PAGE_DATE + '">'
          + '<input type="hidden" name="h_debut" value="' + (selectedH || 9) + '">'
          + '<input type="hidden" name="row_id" value="' + rowId + '">'
          + '<select name="type" class="type-select">'
          + '<option value="soins">Soins</option><option value="toilette">Toilette</option>'
          + '<option value="menage">Ménage</option><option value="repas">Repas</option>'
          + '<option value="accompagnement">Accompagnement</option><option value="surveillance">Surveillance</option>'
          + '</select>'
          + '<button type="submit" class="btn-assigner-sv">Assigner</button>';
      }
      actions.appendChild(form);
    }

    row.appendChild(actions);
    list.appendChild(row);
  });
}

function pad(n) { return n < 10 ? '0' + n : '' + n; }
function escHtml(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function openModifier(id, prenom, nom, telephone, email, lien, patientNom) {
  document.getElementById('modifier-form').action = '/admin/prises-en-charge/' + id + '/modifier';
  document.getElementById('modifier-patient-nom').textContent = patientNom;
  document.getElementById('modifier-prenom').value    = prenom;
  document.getElementById('modifier-nom').value       = nom;
  document.getElementById('modifier-telephone').value = telephone;
  document.getElementById('modifier-email').value     = email;
  const sel = document.getElementById('modifier-lien');
  for (let i = 0; i < sel.options.length; i++) {
    sel.options[i].selected = sel.options[i].value === lien;
  }
  document.getElementById('modalModifier').style.display = 'flex';
}

document.getElementById('pecSearch').addEventListener('input', function () {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#pecBody .data-row').forEach(function (row) {
    const show = row.textContent.toLowerCase().includes(q);
    row.style.display = show ? '' : 'none';
    const expRow = document.getElementById('expand-' + row.id.replace('row-', ''));
    if (!show && expRow) expRow.style.display = 'none';
  });
});

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.getElementById('modalAjouter').style.display  = 'none';
    document.getElementById('modalModifier').style.display = 'none';
  }
});
