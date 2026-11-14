/******/ (() => { // webpackBootstrap
/*!*******************************************!*\
  !*** ./src/scripts/section/categories.js ***!
  \*******************************************/
window.addEventListener("DOMContentLoaded", function () {
  var slider = document.querySelector('.js-categories-slider');
  if (!slider || typeof Swiper === 'undefined') {
    return;
  }
  var gap = function gap() {
    return rPx(window.innerWidth >= 768 ? 30 : 16);
  };
  var swiper = new Swiper(slider, {
    slidesPerView: 1.4,
    spaceBetween: gap(),
    pagination: sliderDots(document.querySelector('.js-categories-pagination')),
    breakpoints: {
      768: {
        slidesPerView: 3,
        slidesPerGroup: 3
      },
      1200: {
        slidesPerView: 4,
        slidesPerGroup: 4
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