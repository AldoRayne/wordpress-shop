function acfPostObjectState(state) {
    if (!state.id) return state.text;

    return jQuery(state.text).length ? jQuery(state.text) : state.text;
}

if (typeof acf !== 'undefined') {
    acf.add_filter("select2_args", (args, $select, obj) => {
        if (obj && obj.field.data && obj.field.data.type === "post_object") {
            args.templateResult = (state) => acfPostObjectState(state);
            args.templateSelection = (state) => acfPostObjectState(state);
        }

        return args;
    });
}

document.body.addEventListener('mouseenter', function (event) {
    if (event.target.classList.contains('acf-layout-tooltip')) {
        let previewUrl = event.target.getAttribute('data-preview');

        if (!previewUrl) return;

        let previewElement = event.target.querySelector('.acf-layout-preview');
        if (previewElement) {
            previewElement.innerHTML = `<img src="${previewUrl}" alt="Preview">`;
        }

        event.target.removeAttribute('data-preview');
    }
}, true);