window.addEventListener("DOMContentLoaded", () => {
    // scroll bar width
        function updateFullWidthBlocks () {
            document.documentElement.style.setProperty('--scrollbar-width', "".concat(window.innerWidth - document.documentElement.clientWidth, "px"));
        }
        window.addEventListener('resize', updateFullWidthBlocks);
        updateFullWidthBlocks();

    const hamburger = document.querySelector('.js-hamburger');
    const headerNav = document.querySelector('.js-header-nav');

    if (hamburger && headerNav) {
        const toggleMenu = (open) => {
            hamburger.classList.toggle('open', open);
            headerNav.classList.toggle('open', open);
            document.body.classList.toggle('modal-open', open);
        };

        hamburger.addEventListener('click', (event) => {
            event.stopPropagation();
            toggleMenu(!headerNav.classList.contains('open'));
        });

        document.addEventListener('click', (event) => {
            if (headerNav.classList.contains('open') && !headerNav.contains(event.target)) {
                toggleMenu(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                toggleMenu(false);
            }
        });

        window.matchMedia('(min-width: 1200px)').addEventListener('change', (event) => {
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