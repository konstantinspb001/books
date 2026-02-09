// editor.js — Markdown editor functionality

// Initialize the editor
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('.editor-textarea');
    const saveButton = document.querySelector('.save-button');
    const toolbarButtons = document.querySelectorAll('.toolbar-btn');
    const toolbarSelect = document.querySelector('.toolbar-select');
    const statusSaved = document.querySelector('.statusbar-saved');

    // Autosave functionality
    let isSaving = false;
    let saveTimeout;

    textarea.addEventListener('input', function() {
        // Clear previous timeout
        clearTimeout(saveTimeout);

        // Show autosave indicator
        statusSaved.innerHTML = '<span class="autosave-dot"></span> Сохранение...';

        // Set new timeout
        saveTimeout = setTimeout(function() {
            saveContent();
        }, 2000);
    });

    // Save button click
    saveButton.addEventListener('click', function() {
        saveContent(true);
    });

    // Save content function
    function saveContent(showSuccess = false) {
        if (isSaving) return;

        isSaving = true;
        const content = textarea.value;
        const sectionId = textarea.id;

        // Simulate POST request
        fetch('/section_save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                content: content,
                id: sectionId,
                timestamp: new Date().toISOString()
            })
        })
        .then(response => response.json())
        .then(data => {
            isSaving = false;
            if (showSuccess) {
                statusSaved.innerHTML = '✓ Сохранено в ' + new Date().toLocaleTimeString();
                statusSaved.style.color = 'var(--accent-green)';
            } else {
                statusSaved.innerHTML = '<span class="autosave-dot"></span> Автосохранение';
            }
        })
        .catch(error => {
            isSaving = false;
            statusSaved.innerHTML = '✗ Ошибка сохранения';
            statusSaved.style.color = 'var(--accent-red)';
            console.error('Error saving content:', error);
        });
    }

    // Toolbar button functionality
    toolbarButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.getAttribute('title');
            applyMarkdown(action);
        });
    });

    // Toolbar select functionality
    toolbarSelect.addEventListener('change', function() {
        const selectedOption = this.value;
        applyHeading(selectedOption);
    });

    // Apply markdown formatting
    function applyMarkdown(action) {
        const startTag = getStartTag(action);
        const endTag = getEndTag(action);

        if (startTag && endTag) {
            insertAroundSelection(startTag, endTag);
        }
    }

    // Get start tag based on action
    function getStartTag(action) {
        switch(action) {
            case 'Жирный': return '**';
            case 'Курсив': return '*';
            case 'Подчёркнутый': return '<u>';
            case 'Зачёркнутый': return '~~';
            case 'Маркированный список': return '- ';
            case 'Нумерованный список': return '1. ';
            case 'Цитата': return '> ';
            case 'Разделитель': return '---';
            case 'Заметка': return '> [!NOTE]\n';
            case 'Комментарий': return '[//]: # ';
            default: return '';
        }
    }

    // Get end tag based on action
    function getEndTag(action) {
        switch(action) {
            case 'Жирный': return '**';
            case 'Курсив': return '*';
            case 'Подчёркнутый': return '</u>';
            case 'Зачёркнутый': return '~~';
            case 'Заметка': return '';
            case 'Комментарий': return '';
            default: return '';
        }
    }

    // Apply heading
    function applyHeading(headingType) {
        const selection = window.getSelection().toString();
        if (selection) {
            const heading = getHeadingTag(headingType);
            insertAroundSelection(heading + ' ', '');
        }
    }

    // Get heading tag
    function getHeadingTag(headingType) {
        switch(headingType) {
            case 'Заголовок 2': return '##';
            case 'Заголовок 3': return '###';
            case 'Цитата': return '>';
            default: return '';
        }
    }

    // Insert text around current selection
    function insertAroundSelection(start, end) {
        const textarea = document.querySelector('.editor-textarea');
        const selectionStart = textarea.selectionStart;
        const selectionEnd = textarea.selectionEnd;
        const selectedText = textarea.value.substring(selectionStart, selectionEnd);
        const replacement = start + selectedText + end;

        textarea.value = textarea.value.substring(0, selectionStart) + 
                        replacement + 
                        textarea.value.substring(selectionEnd);

        // Restore cursor position
        textarea.selectionStart = selectionStart + start.length;
        textarea.selectionEnd = selectionStart + start.length + selectedText.length;
        textarea.focus();
    }

    // Update word count
    function updateWordCount() {
        const text = textarea.value;
        const words = text.trim().split(/\s+/).filter(word => word.length > 0);
        const chars = text.length;
        const paragraphs = text.split('\n').filter(p => p.trim().length > 0).length;
        const readingTime = Math.ceil(words.length / 200); // 200 words per minute

        const wordCountElement = document.querySelector('.statusbar-item:nth-child(1)');
        const charCountElement = document.querySelector('.statusbar-item:nth-child(2)');
        const paragraphCountElement = document.querySelector('.statusbar-item:nth-child(3)');
        const readingTimeElement = document.querySelector('.statusbar-item:nth-child(4)');

        wordCountElement.textContent = `📊 Слов: ${words.length}`;
        charCountElement.textContent = `🔤 Символов: ${chars}`;
        paragraphCountElement.textContent = `📄 Параграфов: ${paragraphs}`;
        readingTimeElement.textContent = `⏱️ ~${readingTime} мин чтения`;
    }

    // Update word count on input
    textarea.addEventListener('input', updateWordCount);

    // Initialize word count
    updateWordCount();
});