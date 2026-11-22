// Each layer moves down by data-parallax-layer percent of its height as the hero scrolls out of view.
export function initParallax(hero) {
    const layers = [...hero.querySelectorAll('[data-parallax-layer]')].map((element) => ({
        element,
        shift: Number(element.dataset.parallaxLayer),
    }));
    let height = hero.offsetHeight;
    let shown = null;

    // Browsers already deliver scroll events once per frame, so the layers are moved directly.
    const render = () => {
        const progress = Math.min(Math.max(window.scrollY / height, 0), 1);
        if (progress === shown) {
            return;
        }
        shown = progress;
        layers.forEach(({ element, shift }) => {
            element.style.transform = `translate3d(0, ${(progress * shift).toFixed(2)}%, 0)`;
        });
    };

    window.addEventListener('scroll', render, { passive: true });
    window.addEventListener('resize', () => {
        height = hero.offsetHeight;
        shown = null;
        render();
    });
    render();
}
