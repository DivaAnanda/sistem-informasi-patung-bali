import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const BALI_CENTER = [-8.4095, 115.1889];
const TILE_URL = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
const ATTRIBUTION = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
const MARKER_STYLE = { radius: 8, color: '#ffffff', weight: 2, fillColor: '#a4471f', fillOpacity: 1 };

function readMarkers(element) {
    const source = document.getElementById(element.dataset.markers);

    return source ? JSON.parse(source.textContent) : [];
}

function popupContent(marker) {
    const wrapper = document.createElement('div');
    const title = document.createElement('a');
    const meta = document.createElement('p');

    wrapper.className = 'map-popup';
    if (marker.photo) {
        const photoLink = document.createElement('a');
        const photo = document.createElement('img');
        photoLink.href = marker.url;
        photoLink.tabIndex = -1;
        photo.src = marker.photo;
        photo.alt = '';
        photo.loading = 'lazy';
        photo.className = 'map-popup-photo';
        photoLink.append(photo);
        wrapper.append(photoLink);
    }

    title.href = marker.url;
    title.textContent = marker.name;
    title.className = 'map-popup-title';
    meta.textContent = marker.place;
    meta.className = 'map-popup-meta';
    wrapper.append(title, meta);

    return wrapper;
}

function tooltipContent(marker) {
    const label = document.createElement('span');

    label.textContent = marker.name;

    return label;
}

function initMap(element) {
    const markers = readMarkers(element);
    const map = L.map(element, { scrollWheelZoom: element.dataset.scrollZoom === 'true' });

    L.tileLayer(TILE_URL, { maxZoom: 19, attribution: ATTRIBUTION }).addTo(map);

    const points = markers.map((marker) => {
        L.circleMarker([marker.lat, marker.lng], MARKER_STYLE)
            .bindTooltip(tooltipContent(marker))
            .bindPopup(popupContent(marker))
            .addTo(map);

        return [marker.lat, marker.lng];
    });

    if (points.length === 1) {
        map.setView(points[0], Number(element.dataset.zoom ?? 17));
    } else if (points.length > 1) {
        map.fitBounds(points, { padding: [32, 32] });
    } else {
        map.setView(BALI_CENTER, 9);
    }
}

export function initMaps() {
    document.querySelectorAll('[data-statue-map]').forEach(initMap);
}
