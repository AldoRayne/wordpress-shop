/******/ (() => { // webpackBootstrap
/*!*************************************!*\
  !*** ./src/scripts/section/hero.js ***!
  \*************************************/
window.addEventListener("DOMContentLoaded", function () {
  var slider = document.querySelector('.js-hero-slider');
  if (!slider || typeof Swiper === 'undefined') {
    return;
  }
  new Swiper(slider, {
    loop: true,
    speed: 600,
    pagination: {
      el: slider.querySelector('.js-hero-pagination'),
      clickable: true,
      bulletClass: 'hero__dot',
      bulletActiveClass: 'hero__dot_active',
      bulletElement: 'button',
      modifierClass: 'hero__pagination_',
      horizontalClass: 'hero__pagination_horizontal'
    }
  });
});
/******/ })()
;