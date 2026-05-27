import { Controller } from '@hotwired/stimulus';
import L from 'leaflet';

export default class extends Controller {
    static values = {
        apiUrl: String,
    };

    #map = null;
    #markersLayer = null;
    #vue = 'jour';
    #date = new Date().toISOString().slice(0, 10);

    connect() {
        this.#initMap();
        this.#loadTournee();
    }

    disconnect() {
        if (this.#map) {
            this.#map.remove();
            this.#map = null;
        }
    }

    changeVue(event) {
        this.#vue = event.currentTarget.dataset.vue;
        this.element.querySelectorAll('[data-vue]').forEach(btn => btn.classList.remove('active'));
        event.currentTarget.classList.add('active');
        this.#loadTournee();
    }

    changeDate(event) {
        this.#date = event.currentTarget.value;
        this.#loadTournee();
    }

    #initMap() {
        this.#map = L.map(this.element.querySelector('#map-canvas')).setView([46.603354, 1.888334], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
        }).addTo(this.#map);

        this.#markersLayer = L.layerGroup().addTo(this.#map);
    }

    async #loadTournee() {
        const url = `${this.apiUrlValue}?date=${this.#date}&vue=${this.#vue}`;
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });

        if (!res.ok) return;

        const data = await res.json();
        this.#renderTournee(data);
    }

    #renderTournee(data) {
        this.#markersLayer.clearLayers();

        const infoEl = this.element.querySelector('#map-info');
        if (infoEl) infoEl.textContent = `${data.date} — ${data.nbVisites} visite(s)`;

        const bounds = [];

        data.tournee.forEach((item, index) => {
            const { latitude, longitude, nom, adresse, niveauRisque } = item.beneficiaire;

            if (!latitude || !longitude) return;

            const color = this.#colorForRisque(niveauRisque);

            const icon = L.divIcon({
                className: '',
                html: `<div style="
                    background:${color};
                    color:#fff;
                    border-radius:50%;
                    width:28px;height:28px;
                    display:flex;align-items:center;justify-content:center;
                    font-weight:bold;font-size:13px;
                    border:2px solid #fff;
                    box-shadow:0 1px 4px rgba(0,0,0,.4)
                ">${index + 1}</div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14],
            });

            const marker = L.marker([latitude, longitude], { icon }).addTo(this.#markersLayer);
            marker.bindPopup(`
                <strong>${nom}</strong><br>
                ${adresse}<br>
                <small>${item.dateDebut} – ${item.dateFin}</small><br>
                <span style="color:${color}">● ${niveauRisque ?? 'N/A'}</span>
            `);

            bounds.push([latitude, longitude]);
        });

        if (bounds.length > 0) {
            this.#map.fitBounds(bounds, { padding: [40, 40] });
        }
    }

    #colorForRisque(niveau) {
        switch (niveau?.toLowerCase()) {
            case 'élevé':
            case 'eleve':
                return '#e53935';
            case 'moyen':
                return '#fb8c00';
            case 'faible':
                return '#43a047';
            default:
                return '#1e88e5';
        }
    }
}
