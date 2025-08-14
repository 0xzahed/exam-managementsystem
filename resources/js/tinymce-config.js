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
            
            // Handle paste events to clean content
            editor.on('PastePreProcess', function (e) {
                // Clean up pasted content
                e.content = cleanPastedContent(e.content);
            });
            
            // Clean content before form submission
            editor.on('submit', function() {
                const content = editor.getContent();
                const cleanedContent = cleanPastedContent(content);
                if (cleanedContent !== content) {
                    editor.setContent(cleanedContent);
                }
            });
            
            // Clean content when editor loses focus
            editor.on('blur', function() {
                const content = editor.getContent();
                const cleanedContent = cleanPastedContent(content);
                if (cleanedContent !== content) {
                    editor.setContent(cleanedContent);
                }
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
        // Content filtering - More restrictive to prevent unwanted HTML
        valid_elements: 'p,br,strong,b,em,i,u,strike,del,ins,mark,small,big,sub,sup,h1,h2,h3,h4,h5,h6,ul,ol,li,blockquote,pre,code,hr,div,span,a[href|target=_blank],img[src|alt|title|width|height],table,thead,tbody,tfoot,tr,td,th,caption,colgroup,col,address,article,aside,footer,header,nav,section,time,figure,figcaption',
        extended_valid_elements: 'script[src|async|defer|type|charset]',
        // Paste settings
        paste_preprocess: function(plugin, args) {
            args.content = cleanPastedContent(args.content);
        },
        // Remove unwanted elements and attributes
        invalid_elements: 'script,iframe,object,embed,form,input,textarea,select,button,label,fieldset,legend',
        // Clean up on init
        init_instance_callback: function(editor) {
            // Clean content when editor initializes
            const content = editor.getContent();
            if (content) {
                const cleanedContent = cleanPastedContent(content);
                if (cleanedContent !== content) {
                    editor.setContent(cleanedContent);
                }
            }
            
            // Add paste event listener for real-time cleaning
            editor.on('PastePreProcess', function(e) {
                e.content = cleanPastedContent(e.content);
            });
        }
    };

    // Merge custom config with default config
    const finalConfig = { ...defaultConfig, ...customConfig };
    
    // Initialize TinyMCE
    tinymce.init(finalConfig);
}

/**
 * Clean pasted content to remove unwanted HTML tags and attributes
 * @param {string} content - Raw HTML content
 * @returns {string} - Cleaned HTML content
 */
function cleanPastedContent(content) {
    if (!content) return '';
    
    // Remove unwanted HTML tags
    content = content.replace(/<p[^>]*>/gi, '<p>');
    content = content.replace(/<div[^>]*>/gi, '<p>');
    content = content.replace(/<\/div>/gi, '</p>');
    
    // Remove empty paragraphs
    content = content.replace(/<p[^>]*>\s*<\/p>/gi, '');
    content = content.replace(/<p[^>]*>&nbsp;<\/p>/gi, '');
    content = content.replace(/<p[^>]*>\s*<br\s*\/?>\s*<\/p>/gi, '');
    
    // Remove unwanted attributes from common tags
    content = content.replace(/<([a-z][a-z0-9]*)[^>]*>/gi, function(match, tagName) {
        const allowedTags = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'strike', 'del', 'ins', 'mark', 'small', 'big', 'sub', 'sup', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'blockquote', 'pre', 'code', 'hr', 'span', 'a', 'img', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'caption', 'colgroup', 'col'];
        
        if (allowedTags.includes(tagName.toLowerCase())) {
            // Keep only essential attributes for specific tags
            if (tagName.toLowerCase() === 'a') {
                const hrefMatch = match.match(/href="([^"]*)"/i);
                return hrefMatch ? `<a href="${hrefMatch[1]}">` : '<a>';
            } else if (tagName.toLowerCase() === 'img') {
                const srcMatch = match.match(/src="([^"]*)"/i);
                const altMatch = match.match(/alt="([^"]*)"/i);
                const titleMatch = match.match(/title="([^"]*)"/i);
                let imgTag = '<img';
                if (srcMatch) imgTag += ` src="${srcMatch[1]}"`;
                if (altMatch) imgTag += ` alt="${altMatch[1]}"`;
                if (titleMatch) imgTag += ` title="${titleMatch[1]}"`;
                imgTag += '>';
                return imgTag;
            } else {
                // For other tags, remove all attributes
                return `<${tagName}>`;
            }
        }
        return match;
    });
    
    // Clean up multiple line breaks
    content = content.replace(/(<br\s*\/?>\s*){3,}/gi, '<br><br>');
    
    // Remove Microsoft Word specific content
    content = content.replace(/<!--\[if.*?\]>.*?<!\[endif\]-->/gi, '');
    content = content.replace(/<o:p[^>]*>.*?<\/o:p>/gi, '');
    content = content.replace(/<m:[^>]*>.*?<\/m:[^>]*>/gi, '');
    
    // Remove style attributes
    content = content.replace(/\s*style="[^"]*"/gi, '');
    
    // Remove class attributes
    content = content.replace(/\s*class="[^"]*"/gi, '');
    
    // Remove id attributes
    content = content.replace(/\s*id="[^"]*"/gi, '');
    
    // Remove data attributes
    content = content.replace(/\s*data-[^=]*="[^"]*"/gi, '');
    
    // Clean up extra whitespace
    content = content.replace(/\s+/g, ' ');
    content = content.replace(/>\s+</g, '><');
    
    // Remove empty tags
    content = content.replace(/<([a-z][a-z0-9]*)[^>]*>\s*<\/\1>/gi, '');
    
    return content.trim();
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
        initializeTinyMCE('#content', {
            height: 400,
            placeholder: 'Write your announcement here...',
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons'
            ],
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help | link image media | table | code preview fullscreen | emoticons',
            // Allow more HTML elements for announcements
            valid_elements: 'p,br,strong,b,em,i,u,strike,del,ins,mark,small,big,sub,sup,h1,h2,h3,h4,h5,h6,ul,ol,li,blockquote,pre,code,hr,div,span,a[href|target=_blank],img[src|alt|title|width|height],table,thead,tbody,tfoot,tr,td,th,caption,colgroup,col,address,article,aside,footer,header,nav,section,time,figure,figcaption,em,emoticon'
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
        // Check if we're on announcement creation page
        else if (document.getElementById('content') && document.querySelector('form[action*="announcements"]')) {
            TinyMCEConfigs.announcement();
        }
        // General initialization for any .tinymce elements
        else if (document.querySelector('.tinymce')) {
            TinyMCEConfigs.general();
        }
        
        // Clean any existing content in textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            if (textarea.value && textarea.value.includes('<')) {
                const cleanedValue = cleanPastedContent(textarea.value);
                if (cleanedValue !== textarea.value) {
                    textarea.value = cleanedValue;
                }
            }
        });

        // Clean content when forms are submitted
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const textareas = this.querySelectorAll('textarea');
                textareas.forEach(textarea => {
                    if (textarea.value && textarea.value.includes('<')) {
                        const cleanedValue = cleanPastedContent(textarea.value);
                        if (cleanedValue !== textarea.value) {
                            textarea.value = cleanedValue;
                        }
                    }
                });
            });
        });
    }, 100);
});

// Export for manual use
window.TinyMCEConfigs = TinyMCEConfigs;
window.initializeTinyMCE = initializeTinyMCE;
