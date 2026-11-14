import PreviewAcfField from './PreviewAcfField.js';
import PreviewAcfResizer from './PreviewAcfResizer.js';

/**
 * PreviewAcf Class
 *
 * This class adds a "Preview" mode to ACF (Advanced Custom Fields),
 * allowing admins to see how fields will look in the admin interface
 * and adjust their widths via drag-and-drop resizing.
 */
export default class PreviewAcf {

    /**
     * Constructor
     *
     * Initializes the preview button, binds event listeners,
     * and sets up field resizing functionality.
     */
    constructor() {
        this.showButton();
        this.actions();

        new PreviewAcfResizer();
    }

    /**
     * Displays the "Preview" button in the ACF header bar.
     *
     * The button is dynamically added to the page and allows users to
     * open the preview modal for viewing and interacting with fields.
     */
    showButton() {
        jQuery('.acf-headerbar #submitpost').prepend(`
            <a href="#" class="acf-btn acf-btn-secondary acf-preview">
                <i class="acf-icon acf-icon-play"></i>Preview
            </a>
        `);

        this.$button = jQuery('a.acf-preview');
    }

    /**
     * Binds event listeners for user interactions.
     *
     * Handles clicks on the "Preview" button to show the modal
     * and clicks on the modal close button to hide it.
     */
    actions() {
        this.$button.on('click', this.showModal.bind(this));
        jQuery(document).on('click', '#acf-preview-modal .acf-modal-close', this.hideModal);
    }

    /**
     * Opens the preview modal.
     *
     * Dynamically creates a modal containing ACF fields
     * with the current label placement settings.
     *
     * @param {Event} e - The click event object
     */
    showModal(e) {
        e.preventDefault();
        let labelPlacement = jQuery('#acf-field-group-options [name="acf_field_group[label_placement]"]:checked').val() || 'top';
        let wrapperClass = 'acf-fields -' + labelPlacement;

        jQuery('body').append(`
            <div id="acf-preview-modal" class="acf-browse-fields-modal-wrap">
                <div class="acf-modal acf-browse-fields-modal">
                    <div id="acf-preview-wrapper" class="${wrapperClass}"></div>
                </div>
                <div class="acf-modal-backdrop acf-modal-close"></div>
            </div>
        `);

        this.$modalWrapper = jQuery('#acf-preview-wrapper');

        this.render();
    }

    /**
     * Closes the preview modal.
     *
     * Removes the modal from the DOM when the close button is clicked.
     *
     * @param {Event} e - The click event object
     */
    hideModal(e) {
        e.preventDefault();
        jQuery('#acf-preview-modal').remove();
    }

    /**
     * Renders ACF fields inside the modal.
     *
     * Retrieves all fields using the `ACFPreviewField` class
     * and appends them to the modal wrapper for display.
     */
    render() {
        PreviewAcfField.getFields().forEach(field => {
            this.$modalWrapper.append(field.render());
        });
    }
}