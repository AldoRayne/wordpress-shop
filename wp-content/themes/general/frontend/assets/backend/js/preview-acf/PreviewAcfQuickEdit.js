import PreviewAcfResizer from './PreviewAcfResizer.js';

/**
 * Class representing the ACF Quick Edit feature.
 */
export default class PreviewAcfQuickEdit {
    /**
     * Initializes the quick edit functionality.
     */
    constructor() {
        if (typeof acf === 'undefined') return;

        this.showEditButtons();
        this.actions();

        new PreviewAcfResizer();
    }

    /**
     * Displays edit buttons for each postbox.
     */
    showEditButtons() {
        const postboxes = acf.getPostboxes();

        if (postboxes.length) {
            postboxes.forEach(postbox => {
                this.showButton(postbox.$el);
            });
        }
    }

    /**
     * Creates an edit button inside the postbox.
     *
     * @param {jQuery} postboxEl - The postbox element.
     * @param {string} [type='edit'] - The type of button to display.
     */
    showButton(postboxEl, type = 'edit') {
        const types = {
            edit: { icon: 'image-flip-horizontal', title: acf.__('Resize fields') },
            cancel: { icon: 'no', title: acf.__('Cancel') },
            save: { icon: 'saved', title: acf.__('Save') },
        };

        if (!types.hasOwnProperty(type)) type = 'edit';

        const id = postboxEl.attr('id');

        postboxEl.find('.handle-actions').prepend(`<a href="#${id}" class="acf-preview-quick-edit-button acf-preview-quick-edit-button--${type} dashicons dashicons-${types[type].icon}${type === 'edit' ? ' acf-hndle-cog' : ''} acf-js-tooltip" title="${types[type].title}"></a>`);
    }

    /**
     * Attaches event listeners for quick edit actions.
     */
    actions() {
        jQuery(document.body).on('click', '.acf-preview-quick-edit-button', (e) => {
            e.preventDefault();

            const id = e.target.getAttribute('href').replace('#', '');

            if (e.target.classList.contains('acf-preview-quick-edit-button--edit')) {
                this.showQuickEdit(id);
            }

            if (e.target.classList.contains('acf-preview-quick-edit-button--cancel')) {
                this.hideQuickEdit(id, true);
            }

            if (e.target.classList.contains('acf-preview-quick-edit-button--save')) {
                this.save(e);
            }
        });
    }

    /**
     * Enables quick edit mode for a postbox.
     *
     * @param {string} id - The ID of the postbox.
     */
    showQuickEdit(id) {
        const postbox = jQuery(`#${id}`);
        const fields = postbox.find('.acf-field');

        postbox.addClass('acf-preview-quick-edit');
        postbox.find('.acf-preview-quick-edit-button').remove();

        this.showButton(postbox, 'save');
        this.showButton(postbox, 'cancel');

        fields.each((index, field) => {
            const $field = jQuery(field);
            const width = $field.attr('data-width') || 0;

            $field.addClass('acf-preview-field');
            $field.attr('data-initial-width', width);

            $field.prepend(`
                <div class="acf-preview-quick-resize">
                    <span class="acf-preview-quick-resize__button" data-width="25">1/4</span>
                    <span class="acf-preview-quick-resize__button" data-width="33.33">1/3</span>
                    <span class="acf-preview-quick-resize__button" data-width="50">1/2</span>
                    <span class="acf-preview-quick-resize__button" data-width="100">1</span>
                </div>
                <div class="acf-preview-resizer"></div>
                <div class="acf-preview-width">
                    <input type="number" name="acf-preview-resize-123" value="${width || 100}" readonly>%
                </div>
            `);
        });
    }

    /**
     * Disables quick edit mode and optionally resets the fields.
     *
     * @param {string} id - The ID of the postbox.
     * @param {boolean} [reset=false] - Whether to reset field widths.
     */
    hideQuickEdit(id, reset = false) {
        const postbox = jQuery(`#${id}`);
        const fields = postbox.find('.acf-field');

        postbox.removeClass('acf-preview-quick-edit saving');

        postbox.find('.spinner').remove();
        postbox.find('.acf-preview-quick-edit-button').remove();

        this.showButton(postbox);

        fields.removeClass('acf-preview-field');
        fields.find('.acf-preview-quick-resize').remove();
        fields.find('.acf-preview-resizer').remove();
        fields.find('.acf-preview-width').remove();

        if (reset) {
            fields.each((index, field) => {
                const $field = jQuery(field);

                this.reset($field);
            });
        }
    }

    /**
     * Resets a field to its initial width.
     *
     * @param {jQuery} field - The field element.
     */
    reset(field) {
        const initialWidth = +field.attr('data-initial-width');

        if (initialWidth) {
            field.css('width', initialWidth + '%');
            field.attr('data-width', initialWidth);
        }
    }

    /**
     * Saves the updated field widths via AJAX.
     *
     * @param {Event} e - The event object.
     */
    save(e) {
        const postbox = jQuery(e.target).parents('.acf-postbox');
        const fields = postbox.find('.acf-field');
        const postboxId = postbox.attr('id');
        const groupKey = postboxId.replace('acf-', '');

        let data = new FormData();
        let send = false;

        data.append('action', 'preview_acf_quick_edit');
        data.append('nonce', previewAcf.nonce);
        data.append('group_key', groupKey);

        fields.each((index, field) => {
            const $field = jQuery(field);
            const width = +$field.attr('data-width') || 0;
            const key = $field.attr('data-key');

            if (!width) return;

            data.append(`fields[${key}]`, width);
            send = true;
        });

        if (!send) return;

        jQuery(e.target).after('<span class="spinner is-active"></span>');
        postbox.addClass('saving');

        fetch(previewAcf.url, {
            method: 'POST',
            body: data,
        })
            .then(response => response.json())
            .then(() => {
                this.hideQuickEdit(postboxId);
            })
            .catch(error => {
                console.error(error);
                this.hideQuickEdit(postboxId, true);
            });
    }
}