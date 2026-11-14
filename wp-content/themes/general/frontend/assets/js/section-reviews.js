/******/ (() => { // webpackBootstrap
/*!****************************************!*\
  !*** ./src/scripts/section/reviews.js ***!
  \****************************************/
window.addEventListener("DOMContentLoaded", function () {
  var slider = document.querySelector('.js-reviews-slider');
  if (!slider || typeof Swiper === 'undefined') {
    return;
  }
  var gap = function gap() {
    return rPx(window.innerWidth >= 768 ? 30 : 16);
  };
  var swiper = new Swiper(slider, {
    slidesPerView: 1.1,
    watchSlidesProgress: true,
    spaceBetween: gap(),
    pagination: sliderDots(document.querySelector('.js-reviews-pagination')),
    breakpoints: {
      768: {
        slidesPerView: 2,
        slidesPerGroup: 2
      },
      1200: {
        slidesPerView: 3,
        slidesPerGroup: 3
      }
    }
  });
  window.addEventListener('resize', function () {
    swiper.params.spaceBetween = gap();
    swiper.update();
  });
});
/******/ })()
;