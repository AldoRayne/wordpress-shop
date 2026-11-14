window.addEventListener("DOMContentLoaded", () => {
    const slider = document.querySelector('.js-reviews-slider');

    if (!slider || typeof Swiper === 'undefined') {
        return;
    }

    const gap = () => rPx(window.innerWidth >= 768 ? 30 : 16);

    const swiper = new Swiper(slider, {
        slidesPerView: 1.1,
        watchSlidesProgress: true,
        spaceBetween: gap(),
        pagination: sliderDots(document.querySelector('.js-reviews-pagination')),
        breakpoints: {
            768: {
                slidesPerView: 2,
                slidesPerGroup: 2,
            },
            1200: {
                slidesPerView: 3,
                slidesPerGroup: 3,
            },
        },
    });

    window.addEventListener('resize', () => {
        swiper.params.spaceBetween = gap();
        swiper.update();
    });
});
