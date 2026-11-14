window.addEventListener("DOMContentLoaded", () => {
    const slider = document.querySelector('.js-hero-slider');

    if (!slider || typeof Swiper === 'undefined') {
        return;
    }

    new Swiper(slider, {
        loop: true,
        speed: 600,
        pagination: sliderDots(slider.querySelector('.js-hero-pagination')),
    });
});
