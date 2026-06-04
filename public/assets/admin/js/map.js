(function () {
  const map = L.map('map', { zoomControl: true }).setView(MAP_CENTER, 14);

  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> · © DINUM',
    maxZoom: 19
  }).addTo(map);

  function makeIcon(html, size) {
    return L.divIcon({ html: html, className: '', iconSize: [size, size], iconAnchor: [size/2, size/2] });
  }

  MAP_ROUTES.forEach(function (r) {
    L.polyline(r.points, {
      color: r.color,
      weight: 2.5,
      dashArray: '8, 6',
      opacity: 0.75
    }).addTo(map);
  });

  MAP_PATIENTS.forEach(function (p) {
    const color = p.risque === 'critique' ? '#ef4444'
                : p.risque === 'eleve'    ? '#f97316'
                : '#f472b6';
    const icon = makeIcon(
      '<div class="patient-marker" style="background:' + color + '">' + p.initiales + '</div>', 28
    );
    L.marker([p.lat, p.lng], { icon: icon })
      .bindPopup('<strong>' + p.nom + '</strong><br>Risque : ' + (p.risque ?? 'normal'))
      .addTo(map);
  });

  MAP_SOIGNANTS.forEach(function (s) {
    const icon = makeIcon(
      '<div class="soignant-marker" style="background:' + s.color + '">' + s.initiales + '</div>', 36
    );
    L.marker([s.lat, s.lng], { icon: icon })
      .bindPopup('<strong>' + s.nom + '</strong><br>Soignant actif')
      .addTo(map);
  });
})();
