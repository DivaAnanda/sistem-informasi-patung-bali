function initGallery(gallery) {
    const photos = JSON.parse(gallery.dataset.photos || '[]');
    const dialog = gallery.querySelector('[data-gallery-dialog]');
    const main = gallery.querySelector('[data-gallery-main]');
    const thumbs = [...gallery.querySelectorAll('[data-gallery-thumb]')];
    let index = 0;

    if (!dialog || photos.length === 0) {
        return;
    }

    const full = dialog.querySelector('[data-gallery-full]');
    const counter = dialog.querySelector('[data-gallery-counter]');
    const original = dialog.querySelector('[data-gallery-original]');
    const caption = dialog.querySelector('[data-gallery-caption]');
    const captions = JSON.parse(gallery.dataset.photoCaptions || '[]');

    const show = (next) => {
        index = (next + photos.length) % photos.length;
        if (main) {
            main.src = photos[index];
        }
        full.src = photos[index];
        // A visible caption already describes the photo, so the alt text would only repeat it.
        full.alt = captions[index] !== undefined ? '' : (gallery.dataset.photoAlt || '').replace('__N__', index + 1);
        caption.textContent = captions[index] ?? '';
        original.href = photos[index];
        counter.textContent = photos.length > 1 ? `${index + 1} / ${photos.length}` : '';
        thumbs.forEach((thumb, position) => thumb.setAttribute('aria-pressed', position === index ? 'true' : 'false'));
    };

    thumbs.forEach((thumb) => thumb.addEventListener('click', () => show(Number(thumb.dataset.galleryThumb))));
    gallery.querySelectorAll('[data-gallery-open]').forEach((button) =>
        button.addEventListener('click', () => {
            show(button.dataset.galleryOpen ? Number(button.dataset.galleryOpen) : index);
            dialog.showModal();
        }),
    );
    dialog.querySelector('[data-gallery-close]').addEventListener('click', () => dialog.close());
    dialog.querySelectorAll('[data-gallery-step]').forEach((button) =>
        button.addEventListener('click', () => show(index + Number(button.dataset.galleryStep))),
    );
    // Clicking anywhere except the photo or a control closes the viewer.
    dialog.addEventListener('click', (event) => {
        if (!event.target.closest('img, button, a')) {
            dialog.close();
        }
    });
    dialog.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowRight') {
            show(index + 1);
        } else if (event.key === 'ArrowLeft') {
            show(index - 1);
        }
    });
}

export function initGalleries() {
    document.querySelectorAll('[data-gallery]').forEach(initGallery);
}
