export default class PreviewAcfResizer {
    /**
     * Tracks the element being resized.
     *
     * @type {HTMLElement | false}
     */
    isResizing;

    /**
     * Initializes event listeners for resizing ACF fields.
     */
    constructor() {
        jQuery(document).on('mousedown', '.acf-preview-resizer', this.resizeStart.bind(this));
        jQuery(document).on('click', '.acf-preview-quick-resize__button', this.quickResize.bind(this));
    }

    /**
     * Starts the resize process when the user clicks on the resize handle.
     *
     * @param {MouseEvent} e - The mouse event object.
     */
    resizeStart(e) {
        e.preventDefault();

        this.isResizing = jQuery(e.target).parents('.acf-preview-field')[0];

        jQuery(this.isResizing).addClass('is-resizing');
        jQuery('.acf-preview-field:not(.is-resizing)').addClass('pointer-events-none');

        document.addEventListener('mousemove', this.resize.bind(this));
        document.addEventListener('mouseup', this.resizeStop.bind(this));
    }

    /**
     * Handles the resizing logic while the user moves the mouse.
     *
     * @param {MouseEvent} e - The mouse event object.
     */
    resize(e) {
        if (!this.isResizing) return;

        const wrapper = jQuery(this.isResizing).parents('.acf-fields')[0];
        const wrapperWidth = jQuery(wrapper).width();
        const wrapperStyle = getComputedStyle(wrapper);
        const wrapperPositionLeft = wrapper.getBoundingClientRect().left + parseFloat(wrapperStyle.borderLeftWidth);
        const fieldPositionLeft = this.isResizing.getBoundingClientRect().left;
        const offsetLeft = parseFloat((Math.max(fieldPositionLeft - wrapperPositionLeft, 0) * 100 / wrapperWidth).toFixed(2));

        let minWidth = 10;
        let maxWidth = 100 - offsetLeft;

        let newWidth = (e.clientX - fieldPositionLeft) * 100 / wrapperWidth;

        if (offsetLeft > 0 && newWidth > maxWidth) {
            clearTimeout(this.moveToNextRow);
            jQuery(this.isResizing).addClass('next-row');
            this.moveToNextRow = setTimeout(() => {
                maxWidth = 100;
                jQuery(this.isResizing).removeClass('next-row');
                this.setWidth(this.isResizing, this.getWidth(newWidth, minWidth, maxWidth));
            }, 1000);
        }

        this.setWidth(this.isResizing, this.getWidth(newWidth, minWidth, maxWidth));
    }

    /**
     * Stops the resize process when the user releases the mouse button.
     */
    resizeStop() {
        jQuery(this.isResizing).removeClass('is-resizing next-row');
        jQuery('.acf-preview-field').removeClass('pointer-events-none');

        this.isResizing = false;
        clearTimeout(this.moveToNextRow);

        document.removeEventListener('mousemove', this.resize.bind(this));
        document.removeEventListener('mouseup', this.resizeStop.bind(this));
    }

    /**
     * Handles quick resizing when clicking predefined width buttons.
     *
     * @param {MouseEvent} e - The click event object.
     */
    quickResize(e) {
        const el = jQuery(e.target).parents('.acf-preview-field')[0];
        const width = +jQuery(e.target).attr('data-width');

        this.setWidth(el, width);
    }

    /**
     * Ensures the width is within allowed limits and rounded appropriately.
     *
     * @param {number} width - The new width percentage.
     * @param {number} min - The minimum allowed width.
     * @param {number} max - The maximum allowed width.
     *
     * @returns {number} - The validated and rounded width.
     */
    getWidth(width, min, max) {
        return Number(Math.max(min, Math.min(Math.round(width * 6) / 6, max)).toFixed(2));
    }

    /**
     * Sets the width of a field and updates its related inputs.
     *
     * @param {HTMLElement} el - The field element.
     * @param {number} width - The new width percentage.
     */
    setWidth(el, width) {
        el.style.width = `${width}%`;
        jQuery(el).children('.acf-preview-width').children('input').val(width);
        jQuery(el).attr('data-width', width);

        jQuery(`#acf_fields-${jQuery(el).attr('data-id')}-wrapper-width`)?.val(width).trigger('change');
    }
}