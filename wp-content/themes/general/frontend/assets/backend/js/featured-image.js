(() => {
    /**
     * Function to highlight the featured image block and scroll the page to the top.
     */
    function highlightFeaturedImageBlock() {
        // Scroll the page to the top smoothly
        window.scrollTo({
            top: 0,
            behavior: "smooth",
        });
        // Find the tab button for the "Post" settings and click it to switch the tab
        const tabButton = document.querySelector('[data-tab-id="edit-post/document"]');
        if (tabButton) {
            tabButton.click(); // Switch to the "Post" tab
        }
        setTimeout(() => {  // Wait for the tab to switch
            // Locate the "Set featured image" button
            const featuredImageButton = document.querySelector('.editor-post-featured-image__toggle');
            if (featuredImageButton) {
                featuredImageButton.focus(); // Set focus to the button
            }
        }, 100);
    }

    const requiredPostTypes = che_featured_image_vars.required_feature_image_posts;
    // Check if the required_feature_image_posts variable is defined and is an array
    if (typeof che_featured_image_vars === "undefined" || !requiredPostTypes) {
        return;
    }
    // Check if the required_feature_image_posts variable is an array
    if (!Array.isArray(requiredPostTypes)) {
        return;
    }
    const {
        dispatch,
        select,
        subscribe
    } = wp.data;
    const editor = dispatch("core/editor");
    const editorSelect = select("core/editor");
    const notices = dispatch("core/notices");
    let isValidationRequired = false;
    let lastPostStatus = "";
    const savePostOriginal = editor.savePost;
    subscribe(() => {
        const postStatus = editorSelect.getEditedPostAttribute("status");
        isValidationRequired = [
            "publish",
            "future"
        ].includes(postStatus);
        if (postStatus !== "publish") {
            lastPostStatus = postStatus;
        }
    });
    editor.savePost = async function (options = {}) {
        const postType = editorSelect.getCurrentPostType();
        const postTypeInfo = select('core').getPostType(postType);
        //Check post type and if it supports featured image
        if (!requiredPostTypes.includes(postType) || !postTypeInfo.supports.thumbnail) {
            return await savePostOriginal.apply(this, arguments);
        }
        const featuredImageId = editorSelect.getEditedPostAttribute("featured_media");
        notices.removeNotice("feature-image-validation");
        if (options.isAutosave || options.isPreview) {
            return Promise.resolve("Validation ignored (autosave/preview).");
        }
        if (!isValidationRequired) {
            return Promise.resolve("Validation ignored (draft).");
        }
        try {
            if (!featuredImageId || featuredImageId === 0) {
                notices.createErrorNotice(che_featured_image_vars.error_message, {
                    id: "feature-image-validation",
                    isDismissible: true,
                });
                highlightFeaturedImageBlock();
                throw new Error("Featured image is required.");
            }
            editor.unlockPostSaving("featured_image");
            notices.removeNotice("feature-image-validation");
            return await savePostOriginal.apply(this, arguments);
        } catch (error) {
            console.warn(error.message);
        }
    };
})();