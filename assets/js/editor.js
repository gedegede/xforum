/**
 * Markdown Editor Toolbar
 * Initializes toolbar buttons for textareas
 */
(function() {
    'use strict';

    /**
     * Initialize markdown editor on a textarea
     * @param {string} textareaId - The ID of the textarea element
     */
    function initEditor(textareaId) {
        var textarea = document.getElementById(textareaId);
        var toolbar = textarea ? textarea.closest('.form-group') : null;

        if (!textarea || !toolbar) {
            return;
        }

        // Check if toolbar already exists
        if (toolbar.querySelector('.editor-toolbar')) {
            return;
        }

        // Create toolbar
        var toolbarEl = document.createElement('div');
        toolbarEl.className = 'editor-toolbar';
        toolbarEl.innerHTML = getToolbarButtons();

        // Insert toolbar before textarea
        textarea.parentNode.insertBefore(toolbarEl, textarea);

        // Bind click events
        toolbarEl.querySelectorAll('.toolbar-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var action = btn.getAttribute('data-action');
                insertMarkdown(textarea, action);
            });
        });
    }

    /**
     * Insert markdown syntax at cursor position
     * @param {HTMLTextAreaElement} textarea
     * @param {string} action
     */
    function insertMarkdown(textarea, action) {
        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;
        var value = textarea.value;
        var selectedText = value.substring(start, end);

        var before = '';
        var after = '';
        var placeholder = '';
        var defaultText = '';

        switch (action) {
            case 'bold':
                before = '**';
                after = '**';
                placeholder = '粗体文字';
                break;
            case 'italic':
                before = '*';
                after = '*';
                placeholder = '斜体文字';
                break;
            case 'strikethrough':
                before = '~~';
                after = '~~';
                placeholder = '删除线文字';
                break;
            case 'code':
                if (selectedText.includes('\n')) {
                    before = '```\n';
                    after = '\n```';
                    placeholder = '代码块内容';
                } else {
                    before = '`';
                    after = '`';
                    placeholder = '代码';
                }
                break;
            case 'link':
                before = '[';
                after = '](url)';
                placeholder = '链接文字';
                break;
            case 'image':
                before = '![';
                after = '](image-url)';
                placeholder = '图片描述';
                break;
            case 'list':
                before = '- ';
                after = '';
                placeholder = '列表项';
                break;
            case 'quote':
                before = '> ';
                after = '';
                placeholder = '引用文字';
                break;
            case 'heading':
                before = '## ';
                after = '';
                placeholder = '标题';
                break;
            default:
                return;
        }

        var insertText;
        if (selectedText.length > 0) {
            insertText = before + selectedText + after;
        } else {
            insertText = before + placeholder + after;
            // Select placeholder text for easy replacement
            setTimeout(function() {
                var newStart = start + before.length;
                var newEnd = newStart + placeholder.length;
                textarea.setSelectionRange(newStart, newEnd);
                textarea.focus();
            }, 0);
        }

        textarea.value = value.substring(0, start) + insertText + value.substring(end);

        // Move cursor after the inserted text
        var newPos = start + insertText.length;
        textarea.setSelectionRange(newPos, newPos);
        textarea.focus();

        // Trigger input event for any listeners
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    /**
     * Get toolbar buttons HTML
     * @returns {string}
     */
    function getToolbarButtons() {
        return '<button type="button" class="toolbar-btn" data-action="bold" title="粗体 (Ctrl+B)"><b>B</b></button>' +
               '<button type="button" class="toolbar-btn" data-action="italic" title="斜体 (Ctrl+I)"><em>I</em></button>' +
               '<button type="button" class="toolbar-btn" data-action="strikethrough" title="删除线"><s>S</s></button>' +
               '<span class="toolbar-separator"></span>' +
               '<button type="button" class="toolbar-btn" data-action="code" title="代码"><code>&lt;/&gt;</code></button>' +
               '<button type="button" class="toolbar-btn" data-action="link" title="链接">🔗</button>' +
               '<button type="button" class="toolbar-btn" data-action="image" title="图片">🖼️</button>' +
               '<span class="toolbar-separator"></span>' +
               '<button type="button" class="toolbar-btn" data-action="heading" title="标题">H</button>' +
               '<button type="button" class="toolbar-btn" data-action="quote" title="引用">❝</button>' +
               '<button type="button" class="toolbar-btn" data-action="list" title="列表">☰</button>';
    }

    // Expose to global scope
    window.initEditor = initEditor;

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        var textarea = document.activeElement;
        if (!textarea || textarea.tagName !== 'TEXTAREA') return;

        // Skip if in create/reply textarea
        var isEditorTextarea = textarea.classList.contains('message-editor');
        if (!isEditorTextarea) return;

        if (e.ctrlKey || e.metaKey) {
            var action = null;
            switch (e.key.toLowerCase()) {
                case 'b': action = 'bold'; break;
                case 'i': action = 'italic'; break;
            }
            if (action) {
                e.preventDefault();
                insertMarkdown(textarea, action);
            }
        }
    });
})();