window.addEventListener("DOMContentLoaded", () => {
    const slider = document.querySelector('.js-categories-slider');

    if (!slider || typeof Swiper === 'undefined') {
        return;
    }

    const gap = () => rPx(window.innerWidth >= 768 ? 30 : 16);

    const swiper = new Swiper(slider, {
        slidesPerView: 1.4,
        spaceBetween: gap(),
        pagination: sliderDots(document.querySelector('.js-categories-pagination')),
        breakpoints: {
            768: {
                slidesPerView: 3,
                slidesPerGroup: 3,
            },
            1200: {
                slidesPerView: 4,
                slidesPerGroup: 4,
            },
        },
    });

    window.addEventListener('resize', () => {
        swiper.params.spaceBetween = gap();
        swiper.update();
    });
});
