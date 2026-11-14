/******/ (() => { // webpackBootstrap
/*!*******************************!*\
  !*** ./src/scripts/common.js ***!
  \*******************************/
window.rPx = function (px) {
  return px * parseFloat(getComputedStyle(document.documentElement).fontSize) / 16;
};
window.sliderDots = function (el) {
  return {
    el: el,
    clickable: true,
    bulletClass: 'slider-dots__item',
    bulletActiveClass: 'slider-dots__item_active',
    bulletElement: 'button',
    modifierClass: 'slider-dots_',
    horizontalClass: 'slider-dots_horizontal',
    lockClass: 'slider-dots_lock'
  };
};
window.addEventListener("DOMContentLoaded", function () {
  // scroll bar width
  function updateFullWidthBlocks() {
    document.documentElement.style.setProperty('--scrollbar-width', "".concat(window.innerWidth - document.documentElement.clientWidth, "px"));
  }
  window.addEventListener('resize', updateFullWidthBlocks);
  updateFullWidthBlocks();
  var hamburger = document.querySelector('.js-hamburger');
  var headerNav = document.querySelector('.js-header-nav');
  if (hamburger && headerNav) {
    var toggleMenu = function toggleMenu(open) {
      hamburger.classList.toggle('open', open);
      headerNav.classList.toggle('open', open);
      document.body.classList.toggle('modal-open', open);
    };
    hamburger.addEventListener('click', function (event) {
      event.stopPropagation();
      toggleMenu(!headerNav.classList.contains('open'));
    });
    document.addEventListener('click', function (event) {
      if (headerNav.classList.contains('open') && !headerNav.contains(event.target)) {
        toggleMenu(false);
      }
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        toggleMenu(false);
      }
    });
    window.matchMedia('(min-width: 1200px)').addEventListener('change', function (event) {
      if (event.matches) {
        toggleMenu(false);
      }
    });
  }
});

//If you really need Jquery
/*
$(document).ready(function(){

})
 */
/******/ })()
;