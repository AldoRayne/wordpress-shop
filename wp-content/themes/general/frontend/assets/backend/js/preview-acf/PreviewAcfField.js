import { acfField } from './templates.js';

/**
 * PreviewAcfField Class
 *
 * This class is responsible for managing individual ACF fields.
 * It extracts field data from the DOM, formats it, and renders it
 * for display in the ACF preview modal.
 */
export default class PreviewAcfField {

    /**
     * Constructor
     *
     * Initializes an instance of the field using its unique key.
     * The key is used to locate the field's data in the DOM.
     *
     * @param {string} key - The unique key of the ACF field
     */
    constructor(key) {
        this.key = key;

        this.getData();
    }

    /**
     * Extracts field data from the DOM.
     *
     * Uses the field's key to locate its DOM element and retrieves
     * attributes such as type, label, layout, and other field-specific settings.
     */
    getData() {
        const $field = jQuery(`.acf-field-object[data-key="${this.key}"]`);

        if (!$field) return;

        const id = $field.attr('data-id');

        this.id = id;
        this.type = $field.attr('data-type');
        this.layout = $field.find(`[name="acf_fields[${id}][layout]"]:checked`)?.val();
        this.label = $field.find(`#acf_fields-${id}-label`).val() || '(no label)';
        this.instruction = $field.find(`#acf_fields-${id}-instructions`).val();
        this.placeholder = $field.find(`#acf_fields-${id}-placeholder`).val() || '';
        this.prepend = $field.find(`#acf_fields-${id}-prepend`).val() || '';
        this.append = $field.find(`#acf_fields-${id}-append`).val() || '';
        this.width = +$field.find(`#acf_fields-${id}-wrapper-width`).val();
        this.subFields = this.constructor.getFields(id);
    }

    /**
     * Renders the field for display.
     *
     * Uses the `acfField` template function to generate the HTML
     * representation of the field.
     *
     * @returns {HTMLElement} - The rendered HTML string for the field
     */
    render() {
        return acfField(this);
    }

    /**
     * Retrieves a list of subfields for a given parent field.
     *
     * This method is static and can fetch either top-level fields or subfields
     * based on the provided parent ID. It creates instances of `ACFPreviewField`
     * for each subfield and collects them in an array.
     *
     * @param {string} [parentId=''] - The ID of the parent field;
     *                                 if empty, retrieves top-level fields.
     * @returns {PreviewAcfField[]} - An array of ACFPreviewField instances
     */
    static getFields(parentId = '') {
        const fields = [];
        const selector = parentId ? `.acf-field-object[data-id="${parentId}"] > .settings > .acf-field-editor > .acf-field-settings > .acf-field-settings-main-general > .acf-field-type-settings > .acf-field-setting-sub_fields > .acf-input > .acf-field-list-wrap > .acf-field-list > .acf-field-object` : '#acf-field-group-fields > .inside > .acf-field-list-wrap > .acf-field-list > .acf-field-object';

        const $fields = jQuery(selector);

        if (!$fields.length) return fields;

        $fields.each((index, nodeField) => {
            const field = new this(nodeField.getAttribute('data-key'));

            if (field.key) fields.push(field);
        });

        return fields;
    }
}