import { initGalleries } from './gallery';
import { initParallax } from './parallax';

initGalleries();

const hero = document.querySelector('[data-parallax]');
if (hero && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    initParallax(hero);
}

if (document.querySelector('[data-statue-map]')) {
    import('./map').then(({ initMaps }) => initMaps());
}

if (document.querySelector('[data-review-panel]')) {
    import('./review').then(({ initReviewPanels }) => initReviewPanels());
}

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-street-view-load]');
    if (!trigger) {
        return;
    }

    const frame = document.createElement('iframe');
    frame.src = trigger.dataset.streetViewLoad;
    frame.title = trigger.dataset.streetViewTitle;
    frame.className = 'h-full w-full border-0';
    frame.allowFullscreen = true;
    frame.loading = 'lazy';
    frame.referrerPolicy = 'no-referrer-when-downgrade';
    frame.tabIndex = -1;
    trigger.closest('[data-street-view]').replaceChildren(frame);
    frame.focus();
});
