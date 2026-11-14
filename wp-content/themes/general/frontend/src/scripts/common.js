window.addEventListener("DOMContentLoaded", () => {
    // scroll bar width
        function updateFullWidthBlocks () {
            document.documentElement.style.setProperty('--scrollbar-width', "".concat(window.innerWidth - document.documentElement.clientWidth, "px"));
        }
        window.addEventListener('resize', updateFullWidthBlocks);
        updateFullWidthBlocks();

});


//If you really need Jquery
/*
$(document).ready(function(){

})
 */