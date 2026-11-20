if (document.querySelector('[data-statue-map]')) {
    import('./map').then(({ initMaps }) => initMaps());
}

if (document.querySelector('[data-review-panel]')) {
    import('./review').then(({ initReviewPanels }) => initReviewPanels());
}

document.addEventListener('click', (event) => {
    const thumb = event.target.closest('[data-gallery-thumb]');
    if (!thumb) {
        return;
    }

    const gallery = thumb.closest('[data-gallery]');
    gallery.querySelector('[data-gallery-main]').src = thumb.dataset.galleryThumb;
    gallery.querySelector('[data-gallery-link]').href = thumb.dataset.galleryThumb;
    gallery.querySelectorAll('[data-gallery-thumb]').forEach((other) => {
        other.setAttribute('aria-pressed', other === thumb ? 'true' : 'false');
    });
});
