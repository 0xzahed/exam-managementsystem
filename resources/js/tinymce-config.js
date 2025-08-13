// TinyMCE Configuration and Initialization
// This file handles all TinyMCE setup across the application

/**
 * Initialize TinyMCE with common configuration
 * @param {string} selector - CSS selector for textarea elements
 * @param {Object} customConfig - Custom configuration options
 */
function initializeTinyMCE(selector = '.tinymce', customConfig = {}) {
    // Default configuration
    const defaultConfig = {
        selector: selector,
        height: 400,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help | link image media | table | code preview fullscreen',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }',
        branding: false,
        promotion: false,
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        },
        // File and image upload settings
        images_upload_url: false,
        automatic_uploads: false,
        file_picker_types: 'image',
        file_picker_callback: function (cb, value, meta) {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');

            input.addEventListener('change', function (e) {
                const file = e.target.files[0];
                const reader = new FileReader();
                reader.addEventListener('load', function () {
                    cb(reader.result, {
                        alt: file.name
                    });
                });
                reader.readAsDataURL(file);
            });

            input.click();
        },
        // Link dialog settings
        link_assume_external_targets: true,
        link_context_toolbar: true,
        // Table settings
        table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
        table_appearance_options: false,
        table_grid: false,
        // Media settings
        media_live_embeds: true,
        // Advanced settings
        paste_data_images: true,
        paste_as_text: false,
        paste_remove_styles_if_webkit: true,
        paste_webkit_styles: 'none',
        // Content filtering
        valid_elements: '*[*]',
        extended_valid_elements: 'script[src|async|defer|type|charset]'
    };

    // Merge custom config with default config
    const finalConfig = { ...defaultConfig, ...customConfig };
    
    // Initialize TinyMCE
    tinymce.init(finalConfig);
}

/**
 * Initialize TinyMCE for specific use cases
 */
const TinyMCEConfigs = {
    // For assignment instructions (bigger height)
    assignment: function() {
        initializeTinyMCE('#instructions', {
            height: 450,
            placeholder: 'Provide detailed instructions for the assignment...'
        });
    },

    // For course descriptions
    course: function() {
        initializeTinyMCE('#description', {
            height: 400,
            placeholder: 'Describe the course content, objectives, and learning outcomes...'
        });
    },

    // For material descriptions (smaller height)
    material: function() {
        initializeTinyMCE('#description', {
            height: 300,
            placeholder: 'Brief description of the material...'
        });
    },

    // For any textarea with tinymce class
    general: function() {
        initializeTinyMCE('.tinymce', {
            height: 350
        });
    },

    // For announcements (if needed later)
    announcement: function() {
        initializeTinyMCE('#announcement_content', {
            height: 300,
            placeholder: 'Write your announcement here...'
        });
    }
};

// Auto-initialize based on page context
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for TinyMCE to load
    setTimeout(function() {
        // Check if we're on assignment creation page
        if (document.getElementById('instructions')) {
            TinyMCEConfigs.assignment();
        }
        // Check if we're on course creation/edit page
        else if (document.getElementById('description') && document.querySelector('input[name="title"]')) {
            // Check if it's course form (has title field)
            TinyMCEConfigs.course();
        }
        // Check if we're on material creation page
        else if (document.getElementById('description')) {
            TinyMCEConfigs.material();
        }
        // General initialization for any .tinymce elements
        else if (document.querySelector('.tinymce')) {
            TinyMCEConfigs.general();
        }
    }, 100);
});

// Export for manual use
window.TinyMCEConfigs = TinyMCEConfigs;
window.initializeTinyMCE = initializeTinyMCE;
