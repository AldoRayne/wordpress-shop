/**
 * Renders ACF group fields in block layout.
 *
 * This function processes each subfield and renders them inside a block layout with a border.
 *
 * @param {Object} field - The ACF field object containing subfields
 * @returns {string} - The HTML string representing the block layout
 */
function acfInputGroupBlock(field) {
    let rows = '';

    field.subFields.forEach(subfield => {
        rows += subfield.render().outerHTML;
    });

    return `<div class="acf-fields -top -border">${rows}</div>`;
}

/**
 * Renders ACF group fields in table layout.
 *
 * This function creates a table where subfields are displayed as table headers and rows.
 *
 * @param {Object} field - The ACF field object containing subfields
 * @returns {string} - The HTML string representing the table layout
 */
function acfInputGroupTable(field) {
    let labels = '';
    let rows = '';

    field.subFields.forEach(subfield => {
        let attrs = subfield.width ? ` style="width: ${subfield.width}%" data-width="${subfield.width}"` : '';

        labels += `
            <th class="acf-th"${attrs}>
                <label>${subfield.label}</label>
                ${subfield.instruction ? '<p class="description">' + subfield.instruction + '</p>' : ''}
            </th>
        `;
        rows += `
            <td class="acf-field">
                <div class="acf-input">${getAcfInput(subfield).input}</div>
            </td>
        `;
    });

    return `
        <table class="acf-table">
            <thead>
                <tr>${labels}</tr>
            </thead>
            <tbody>
                <tr class="acf-row">${rows}</tr>
            </tbody>
        </table>
    `;
}

/**
 * Renders ACF group fields in row layout.
 *
 * This function displays subfields in a row-oriented layout with a border on the left.
 *
 * @param {Object} field - The ACF field object containing subfields
 * @returns {string} - The HTML string representing the row layout
 */
function acfInputGroupRow(field) {
    let rows = '';

    field.subFields.forEach(subfield => {
        rows += subfield.render().outerHTML;
    });

    return `<div class="acf-fields -left -border">${rows}</div>`;
}

/**
 * Renders ACF repeater fields in block layout.
 *
 * This function creates a repeater block layout for subfields with a sortable table structure.
 *
 * @param {Object} field - The ACF field object containing subfields
 * @returns {string} - The HTML string representing the repeater block layout
 */
function acfInputRepeaterBlock(field) {
    let rows = '';

    field.subFields.forEach(subfield => {
        rows += subfield.render().outerHTML;
    });

    return `
        <div class="acf-repeater -block">
            <table class="acf-table">
                <tbody>
                    <tr class="acf-row">
                        <td class="acf-row-handle order" style="pointer-events: none">
                            <span class="acf-row-number">1</span>
                        </td>
                        <td class="acf-fields">${rows}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    `;
}

/**
 * Renders ACF repeater fields in table layout.
 *
 * This function creates a table layout where each subfield is a column header and row.
 *
 * @param {Object} field - The ACF field object containing subfields
 * @returns {string} - The HTML string representing the repeater table layout
 */
function acfInputRepeaterTable(field) {
    let labels = '';
    let rows = '';

    field.subFields.forEach(subfield => {
        labels += `
            <th class="acf-th">
                <label>${subfield.label}</label>
                ${subfield.instruction ? '<p class="description">' + subfield.instruction + '</p>' : ''}
            </th>
        `;
        rows += `
            <td class="acf-field">
                <div class="acf-input">${getAcfInput(subfield).input}</div>
            </td>
        `;
    });

    return `
        <div class="acf-repeater -table">
            <table class="acf-table">
                <thead>
                    <tr>
                        <th class="acf-row-handle"></th>
                        ${labels}
                    </tr>
                </thead>
                <tbody>
                    <tr class="acf-row">
                        <td class="acf-row-handle order" style="pointer-events: none">
                            <span class="acf-row-number">1</span>
                        </td>
                        ${rows}
                    </tr>
                </tbody>
            </table>
        </div>
    `;
}

/**
 * Renders ACF repeater fields in row layout.
 *
 * This function creates a row-oriented layout with drag-and-drop handles for sorting.
 *
 * @param {Object} field - The ACF field object containing subfields
 * @returns {string} - The HTML string representing the repeater row layout
 */
function acfInputRepeaterRow(field) {
    let rows = '';

    field.subFields.forEach(subfield => {
        rows += subfield.render().outerHTML;
    });

    return `
        <div class="acf-repeater -row">
            <table class="acf-table">
                <tbody>
                    <tr class="acf-row">
                        <td class="acf-row-handle order" style="pointer-events: none">
                            <span class="acf-row-number">1</span>
                        </td>
                        <td class="acf-fields -left">${rows}</td>
                        <td class="acf-row-handle remove"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    `;
}

/**
 * Determines and renders the layout for a given ACF field.
 *
 * Maps the field type and layout to the appropriate rendering function and returns its result.
 *
 * @param {Object} field - The ACF field object containing type and layout
 * @returns {string} - The rendered HTML string or undefined if no layout matches
 */
function acfInputGroup(field) {
    const layoutMap = {
        group: {
            block: acfInputGroupBlock,
            table: acfInputGroupTable,
            row: acfInputGroupRow
        },
        repeater: {
            block: acfInputRepeaterBlock,
            table: acfInputRepeaterTable,
            row: acfInputRepeaterRow
        },
    };

    return layoutMap[field.type][field.layout] ? layoutMap[field.type][field.layout](field) : '';
}

/**
 * Processes the input rendering for a single ACF field.
 *
 * Adjusts rendering logic based on the field type (e.g., group, repeater, tab, or default).
 *
 * @param {Object} field - The ACF field object
 * @returns {Object} - The updated field object with its input property set
 */
function getAcfInput(field) {
    switch (field.type) {
        case 'group':
        case 'repeater':
            field.input = acfInputGroup(field);
            break;
        case 'tab':
        case 'accordion':
            field.input = '';
            field.label = `${field.type.charAt(0).toUpperCase() + field.type.slice(1)} "${field.label}"`;
            field.attrs += ' style="background: #f9fafb"';
            break;
        default:
            field.input = `
                ${field.prepend ? '<div class="acf-input-prepend">' + field.prepend + '</div>' : ''}
                ${field.append ? '<div class="acf-input-append">' + field.append + '</div>' : ''}
                <div class="acf-input-wrap">
                    <input type="text" placeholder="${field.placeholder}" disabled>
                </div>
            `;
    }

    return field;
}

/**
 * Creates a rendered ACF field element.
 *
 * Generates a DOM element based on the field properties and its input content.
 *
 * @param {Object} field - The ACF field object
 * @returns {HTMLElement} - The rendered ACF field element
 */
export function acfField(field) {
    const tempDiv = document.createElement('div');
    const attrsObj = {
        'data-type': field.type,
        'data-id': field.id
    };

    if (field.width) {
        attrsObj.style = `width: ${field.width}%`;
        attrsObj['data-width'] = field.width;
    }

    field.attrs = Object.entries(attrsObj)
        .map(([key, value]) => `${key}="${value}"`)
        .join(" ");

    field = getAcfInput(field);

    tempDiv.innerHTML = `
        <div class="acf-field acf-preview-field" ${field.attrs}>
            <div class="acf-preview-quick-resize">
                <span class="acf-preview-quick-resize__button" data-width="25">1/4</span>
                <span class="acf-preview-quick-resize__button" data-width="33.33">1/3</span>
                <span class="acf-preview-quick-resize__button" data-width="50">1/2</span>
                <span class="acf-preview-quick-resize__button" data-width="100">1</span>
            </div>
            <div class="acf-preview-resizer"></div>
            <div class="acf-preview-width">
                <input type="number" name="acf-preview-resize-${field.id}" value="${field.width || 100}" readonly>%
            </div>
            <div class="acf-label" style="display: block">
                <label style="font-weight: 500">${field.label}</label>
                ${field.instruction ? '<p class="description">' + field.instruction + '</p>' : ''}
            </div>
            <div class="acf-input">${field.input}</div>
        </div>
    `;

    return tempDiv.firstElementChild;
}