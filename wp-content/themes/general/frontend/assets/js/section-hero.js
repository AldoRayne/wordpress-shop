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
    pagination: sliderDots(slider.querySelector('.js-hero-pagination'))
  });
});
/******/ })()
;