import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Centre de la Tunisie, utilisé quand aucun point n'est placé
const DEFAULT_CENTER = [34.0, 9.5];
const DEFAULT_ZOOM = 6;

const STATE_COLORS = {
    'Ouvert': '#059669',
    'Plein': '#d97706',
    'En maintenance': '#ea580c',
    'Fermé': '#dc2626',
};

function baseMap(element, center = DEFAULT_CENTER, zoom = DEFAULT_ZOOM) {
    const map = L.map(element, { scrollWheelZoom: false }).setView(center, zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; contributeurs <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

    return map;
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

function pointMarker(point, highlighted = false) {
    return L.circleMarker([point.lat, point.lng], {
        radius: highlighted ? 12 : 9,
        color: highlighted ? '#064e3b' : '#ffffff',
        weight: highlighted ? 4 : 2,
        fillColor: STATE_COLORS[point.state] ?? '#64748b',
        fillOpacity: 0.95,
    });
}

// Carte publique : tous les points, position de l'utilisateur, point le plus proche mis en avant
function initPointsMap(element) {
    const points = JSON.parse(element.dataset.points || '[]');
    const user = element.dataset.user ? JSON.parse(element.dataset.user) : null;
    const nearestId = element.dataset.nearest ? Number(element.dataset.nearest) : null;

    const map = baseMap(element);
    const bounds = [];
    const markers = {};

    points.forEach((point) => {
        const distance = point.distance !== null
            ? `<p class="mt-1 text-xs font-semibold text-emerald-700">à ${point.distance} km</p>`
            : '';
        const action = point.depositUrl
            ? `<a href="${point.depositUrl}" class="mt-2 inline-block rounded bg-emerald-600 px-3 py-1 text-xs font-semibold !text-white">Déposer ici</a>`
            : '<p class="mt-2 text-xs text-slate-500">N’accepte pas de dépôts actuellement</p>';

        const marker = pointMarker(point, point.id === nearestId)
            .bindPopup(`
                <p class="font-bold">${escapeHtml(point.name)}</p>
                <p class="text-xs">${escapeHtml(point.address)}, ${escapeHtml(point.city)}</p>
                <p class="text-xs">État : ${escapeHtml(point.state)}</p>
                ${distance}
                ${action}
            `)
            .addTo(map);

        markers[point.id] = marker;
        bounds.push([point.lat, point.lng]);
    });

    if (user) {
        L.circleMarker([user.lat, user.lng], { radius: 8, color: '#ffffff', weight: 3, fillColor: '#2563eb', fillOpacity: 1 })
            .bindTooltip('Vous êtes ici')
            .addTo(map);
        bounds.push([user.lat, user.lng]);
    }

    if (user && nearestId && markers[nearestId]) {
        // Cadre l'utilisateur et le point le plus proche
        map.fitBounds([[user.lat, user.lng], markers[nearestId].getLatLng()], { padding: [60, 60], maxZoom: 14 });
        markers[nearestId].openPopup();
    } else if (bounds.length) {
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 13 });
    }

    // Les boutons "Voir sur la carte" de la liste centrent la carte sur un point
    document.querySelectorAll('[data-focus-point]').forEach((button) => {
        button.addEventListener('click', () => {
            const marker = markers[Number(button.dataset.focusPoint)];
            if (!marker) return;
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            map.setView(marker.getLatLng(), 15);
            marker.openPopup();
        });
    });
}

// Bouton de géolocalisation : recharge la page avec ?lat=..&lng=.. pour que le serveur trie par distance
function initLocateButton(button) {
    const message = document.querySelector(button.dataset.message);
    const label = button.textContent;
    const showMessage = (text) => {
        if (message) {
            message.textContent = text;
            message.classList.remove('hidden');
        }
    };

    button.addEventListener('click', () => {
        if (!window.isSecureContext || !navigator.geolocation) {
            showMessage('La géolocalisation nécessite HTTPS (ou localhost). Choisissez un point directement sur la carte.');
            return;
        }

        button.disabled = true;
        button.textContent = 'Localisation en cours…';

        navigator.geolocation.getCurrentPosition(
            ({ coords }) => {
                const url = new URL(window.location.href);
                url.searchParams.set('lat', coords.latitude.toFixed(6));
                url.searchParams.set('lng', coords.longitude.toFixed(6));
                window.location.href = url.toString();
            },
            () => {
                button.disabled = false;
                button.textContent = label;
                showMessage('Impossible d’obtenir votre position. Autorisez la localisation dans votre navigateur.');
            },
            { enableHighAccuracy: true, timeout: 10000 },
        );
    });
}

// Formulaire admin : un clic ou un glisser du marqueur remplit latitude et longitude
function initPickerMap(element) {
    const latInput = document.querySelector(element.dataset.latInput);
    const lngInput = document.querySelector(element.dataset.lngInput);
    const hasValue = latInput.value !== '' && lngInput.value !== '';
    const start = hasValue ? [Number(latInput.value), Number(lngInput.value)] : DEFAULT_CENTER;

    const map = baseMap(element, start, hasValue ? 15 : DEFAULT_ZOOM);
    let marker = null;

    const place = (latlng) => {
        if (!marker) {
            marker = L.marker(latlng, {
                draggable: true,
                icon: L.divIcon({
                    className: '',
                    html: '<div style="width:22px;height:22px;border-radius:50%;background:#059669;border:3px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4)"></div>',
                    iconSize: [22, 22],
                    iconAnchor: [11, 11],
                }),
            }).addTo(map);
            marker.on('dragend', () => place(marker.getLatLng()));
        }
        marker.setLatLng(latlng);
        latInput.value = Number(latlng.lat).toFixed(7);
        lngInput.value = Number(latlng.lng).toFixed(7);
    };

    if (hasValue) place({ lat: start[0], lng: start[1] });

    map.on('click', (event) => place(event.latlng));

    // Saisie manuelle des coordonnées : déplace le marqueur
    [latInput, lngInput].forEach((input) => input.addEventListener('change', () => {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
            place({ lat, lng });
            map.setView([lat, lng], Math.max(map.getZoom(), 13));
        }
    }));

    document.querySelector(element.dataset.clearButton)?.addEventListener('click', () => {
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        latInput.value = '';
        lngInput.value = '';
    });
}

// Petite carte en lecture seule (fiche d'un point)
function initSingleMap(element) {
    const point = JSON.parse(element.dataset.point);
    const map = baseMap(element, [point.lat, point.lng], 15);
    pointMarker(point, true).addTo(map);
}

document.querySelectorAll('[data-map="points"]').forEach(initPointsMap);
document.querySelectorAll('[data-map="picker"]').forEach(initPickerMap);
document.querySelectorAll('[data-map="single"]').forEach(initSingleMap);
document.querySelectorAll('[data-locate]').forEach(initLocateButton);
