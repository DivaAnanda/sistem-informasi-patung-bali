import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Leaflet guesses its default icon path from the CSS location, which breaks once Vite fingerprints the files.
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({ iconUrl: markerIcon, iconRetinaUrl: markerIcon2x, shadowUrl: markerShadow });

const BALI_CENTER = [-8.4095, 115.1889];
const TILE_URL = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
const ATTRIBUTION = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
const PRECISION = 7;

// Phone photos are resized before upload so the request stays far below the hosting body limit;
// the server still makes the final 1280 px version.
const UPLOAD_MAX_SIDE = 2400;
const UPLOAD_QUALITY = 0.9;

function initLocationPicker(element) {
    const latInput = document.getElementById(element.dataset.latInput);
    const lngInput = document.getElementById(element.dataset.lngInput);
    const current = () => {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);

        return Number.isFinite(lat) && Number.isFinite(lng) ? [lat, lng] : null;
    };

    const start = current();
    const map = L.map(element).setView(start ?? BALI_CENTER, start ? 17 : 9);
    L.tileLayer(TILE_URL, { attribution: ATTRIBUTION, maxZoom: 19 }).addTo(map);

    let marker = null;
    const place = (latlng, { fromInputs = false } = {}) => {
        if (!marker) {
            marker = L.marker(latlng, { draggable: true }).addTo(map);
            marker.on('dragend', () => place(marker.getLatLng()));
        }
        marker.setLatLng(latlng);

        if (!fromInputs) {
            latInput.value = latlng.lat.toFixed(PRECISION);
            lngInput.value = latlng.lng.toFixed(PRECISION);
        }
    };

    if (start) {
        place(L.latLng(start), { fromInputs: true });
    }

    map.on('click', (event) => place(event.latlng));

    [latInput, lngInput].forEach((input) => input.addEventListener('change', () => {
        const point = current();
        if (point) {
            place(L.latLng(point), { fromInputs: true });
            map.setView(point, Math.max(map.getZoom(), 16));
        }
    }));
}

async function shrink(file) {
    if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
        return file;
    }

    const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
    const scale = Math.min(1, UPLOAD_MAX_SIDE / Math.max(bitmap.width, bitmap.height));
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(bitmap.width * scale);
    canvas.height = Math.round(bitmap.height * scale);
    const context = canvas.getContext('2d');
    context.fillStyle = '#ffffff';
    context.fillRect(0, 0, canvas.width, canvas.height);
    context.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
    bitmap.close();

    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', UPLOAD_QUALITY));

    return blob ? new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }) : file;
}

function initPhotoUpload(form) {
    const input = form.querySelector('[data-photo-input]');
    const button = form.querySelector('[data-photo-submit]');
    const progress = form.querySelector('[data-photo-progress]');
    const token = document.querySelector('meta[name="csrf-token"]').content;

    input.multiple = true;
    button.textContent = 'Unggah foto terpilih';

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const files = [...input.files];
        if (files.length === 0) {
            return;
        }

        button.disabled = true;
        const failures = [];

        for (const [index, file] of files.entries()) {
            progress.textContent = `Mengunggah ${index + 1} dari ${files.length}: ${file.name}`;
            const body = new FormData();
            body.append('photo', await shrink(file).catch(() => file));

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
                    body,
                });

                if (!response.ok) {
                    const data = await response.json().catch(() => ({}));
                    failures.push(`${file.name}: ${data.errors?.photo?.[0] ?? data.message ?? `gagal (kode ${response.status})`}`);
                }
            } catch {
                failures.push(`${file.name}: koneksi terputus`);
            }
        }

        if (failures.length > 0) {
            progress.textContent = `Selesai dengan ${failures.length} masalah. ${failures.join(' · ')}`;
            button.disabled = false;
            if (failures.length < files.length) {
                progress.textContent += ' Muat ulang halaman untuk melihat foto yang berhasil.';
            }

            return;
        }

        window.location.reload();
    });
}

export function initAdmin() {
    document.querySelectorAll('[data-admin-map]').forEach(initLocationPicker);
    document.querySelectorAll('[data-photo-upload]').forEach(initPhotoUpload);
}
