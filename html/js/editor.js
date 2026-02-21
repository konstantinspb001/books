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
            const message = (data && data.message) || (data && data.status === 'success' ? 'Сохранено' : 'Ошибка сохранения');

            if (data && data.status === 'success') {
                // показываем в статус-баре текст из ответа + время
                const time = new Date().toLocaleTimeString();
                statusSaved.innerHTML = `✓ ${message} в ${time}`;
                statusSaved.style.color = 'var(--accent-green)';

                showToast(message, 'success');
            } else {
                statusSaved.innerHTML = `✗ ${message}`;
                statusSaved.style.color = 'var(--accent-red)';
                showToast(message, 'error');
            }
        })
        .catch(error => {
            isSaving = false;
            statusSaved.innerHTML = '✗ Ошибка сохранения';
            statusSaved.style.color = 'var(--accent-red)';
            console.error('Error saving content:', error);
            showToast('Ошибка сохранения', 'error');
        });
    }

    // Simple toast notification (under the bottom "Сохранить" button)
    function showToast(message, type = 'success') {
        const bottomSaveButton = document.querySelector('.save-button');
        if (!bottomSaveButton) {
            // fallback — если по какой-то причине нет нижней кнопки, показываем как тост сверху экрана
            const fallback = document.createElement('div');
            fallback.textContent = message;
            fallback.style.position = 'fixed';
            fallback.style.top = '20px';
            fallback.style.right = '20px';
            fallback.style.zIndex = '9999';
            fallback.style.padding = '10px 16px';
            fallback.style.borderRadius = '6px';
            fallback.style.fontSize = '14px';
            fallback.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            fallback.style.backgroundColor = type === 'success' ? 'var(--accent-green, #16a34a)' : 'var(--accent-red, #dc2626)';
            fallback.style.color = '#fff';
            fallback.style.opacity = '0';
            fallback.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
            fallback.style.transform = 'translateY(-10px)';
            document.body.appendChild(fallback);
            requestAnimationFrame(() => {
                fallback.style.opacity = '1';
                fallback.style.transform = 'translateY(0)';
            });
            setTimeout(() => {
                fallback.style.opacity = '0';
                fallback.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    if (fallback.parentNode) {
                        fallback.parentNode.removeChild(fallback);
                    }
                }, 200);
            }, 3000);
            return;
        }

        const container = document.createElement('div');
        container.style.width = '100%';
        container.style.marginTop = '8px';

        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.maxWidth = '960px';
        toast.style.width = '100%';
        toast.style.padding = '12px 18px';
        toast.style.borderRadius = '8px';
        toast.style.fontSize = '14px';
        toast.style.boxShadow = '0 8px 24px rgba(0,0,0,0.18)';
        toast.style.backgroundColor = type === 'success' ? 'var(--accent-green, #16a34a)' : 'var(--accent-red, #dc2626)';
        toast.style.color = '#fff';
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
        toast.style.transform = 'translateY(-10px)';

        container.appendChild(toast);
        bottomSaveButton.parentNode.parentNode.insertBefore(container, bottomSaveButton.parentNode.nextSibling);

        // Trigger animation
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });

        // Hide after 3 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                if (container.parentNode) {
                    container.parentNode.removeChild(container);
                }
            }, 200);
        }, 3000);
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