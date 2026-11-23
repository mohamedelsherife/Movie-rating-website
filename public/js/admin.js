/* CineRate admin — small progressive enhancements for the Blade components.
   Everything still submits as a normal HTML form if this script fails. */
(function () {
    'use strict';

    const squish = (text) => text.trim().replace(/\s+/g, ' ');
    const normalize = (text) => squish(text).toLowerCase();
    const escapeHtml = (text) => text.replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));

    function submitForm(form) {
        if (!form) return;
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    }

    /* ---------------------------------------------------------------
       Combobox  (<x-combobox>)
       --------------------------------------------------------------- */
    function initCombobox(root) {
        const input = root.querySelector('.combo-input');
        const hidden = root.querySelector('.combo-value');
        const list = root.querySelector('.combo-list');
        const options = Array.from(list.querySelectorAll('.combo-option'));
        const createRow = list.querySelector('.combo-create');
        const emptyRow = list.querySelector('.combo-empty');
        const clearBtn = root.querySelector('.combo-clear');
        const note = root.querySelector('.combo-note');
        const creatable = root.hasAttribute('data-creatable');
        const autosubmit = root.hasAttribute('data-autosubmit');

        let committed = { value: hidden.value, label: input.value };
        let activeIndex = -1;

        const visibleItems = () => [...options, createRow].filter((el) => el && !el.hidden);

        function highlight(option, query) {
            const label = option.dataset.label;
            const target = option.querySelector('.combo-label');
            const index = query ? label.toLowerCase().indexOf(query) : -1;
            if (index < 0) {
                target.textContent = label;
                return;
            }
            target.innerHTML = escapeHtml(label.slice(0, index))
                + '<mark>' + escapeHtml(label.slice(index, index + query.length)) + '</mark>'
                + escapeHtml(label.slice(index + query.length));
        }

        function filter(rawQuery) {
            const query = normalize(rawQuery);
            let matches = 0;
            let exact = null;

            options.forEach((option) => {
                const label = option.dataset.label.toLowerCase();
                const match = !query || label.includes(query);
                option.hidden = !match;
                if (match) matches++;
                if (query && label === query) exact = option;
                highlight(option, query);
                option.setAttribute('aria-selected', String(option.dataset.value === hidden.value && hidden.value !== ''));
            });

            if (createRow) {
                const showCreate = creatable && query !== '' && !exact;
                createRow.hidden = !showCreate;
                if (showCreate) createRow.querySelector('[data-create-text]').textContent = squish(rawQuery);
            }

            emptyRow.hidden = matches > 0 || (createRow && !createRow.hidden);
            setActive(-1);
            return exact;
        }

        function setActive(index) {
            const items = visibleItems();
            items.forEach((el) => el.classList.remove('is-active'));
            activeIndex = index;
            if (index >= 0 && items[index]) {
                items[index].classList.add('is-active');
                items[index].scrollIntoView({ block: 'nearest' });
                input.setAttribute('aria-activedescendant', items[index].id);
            } else {
                input.removeAttribute('aria-activedescendant');
            }
        }

        function open() {
            if (!list.hidden) return;
            list.hidden = false;
            root.classList.add('is-open');
            input.setAttribute('aria-expanded', 'true');
            // Show the full list when the box still shows the committed choice.
            filter(input.value === committed.label ? '' : input.value);
        }

        function close() {
            list.hidden = true;
            root.classList.remove('is-open');
            input.setAttribute('aria-expanded', 'false');
            setActive(-1);
        }

        function refreshUi() {
            clearBtn.hidden = input.value === '';
            if (!creatable) return;
            const isNew = hidden.value === '' && squish(input.value) !== '';
            root.classList.toggle('is-new', isNew);
            if (note) {
                note.hidden = !isNew;
                note.querySelector('[data-note-text]').textContent = squish(input.value);
            }
        }

        function commit(value, label) {
            const changed = value !== committed.value;
            hidden.value = value;
            input.value = label;
            committed = { value, label };
            close();
            refreshUi();
            if (autosubmit && changed) submitForm(root.closest('form'));
        }

        function choose(item) {
            if (!item) return;
            if (item === createRow) {
                commit('', squish(input.value));
            } else {
                commit(item.dataset.value, item.dataset.label);
            }
        }

        input.addEventListener('focus', open);
        input.addEventListener('click', open);

        input.addEventListener('input', () => {
            open();
            const exact = filter(input.value);
            // Typing the exact name of an existing entry selects it (prevents duplicates).
            hidden.value = exact ? exact.dataset.value : '';
            refreshUi();
        });

        input.addEventListener('keydown', (event) => {
            const items = visibleItems();
            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    open();
                    setActive(Math.min(activeIndex + 1, visibleItems().length - 1));
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    setActive(Math.max(activeIndex - 1, 0));
                    break;
                case 'Enter':
                    if (list.hidden) return;
                    event.preventDefault();
                    if (activeIndex >= 0) {
                        choose(items[activeIndex]);
                    } else if (items.length === 1 || (items.length && hidden.value)) {
                        choose(hidden.value ? options.find((o) => o.dataset.value === hidden.value) : items[0]);
                    } else if (creatable && squish(input.value)) {
                        choose(createRow);
                    }
                    break;
                case 'Escape':
                    if (!list.hidden) {
                        event.preventDefault();
                        input.value = committed.label;
                        hidden.value = committed.value;
                        close();
                        refreshUi();
                    }
                    break;
                case 'Tab':
                    close();
                    break;
            }
        });

        // mousedown (not click) so the input keeps focus while choosing.
        list.addEventListener('mousedown', (event) => {
            const item = event.target.closest('.combo-option, .combo-create');
            event.preventDefault();
            if (item) choose(item);
        });

        clearBtn.addEventListener('mousedown', (event) => event.preventDefault());
        clearBtn.addEventListener('click', () => {
            const hadValue = committed.value !== '' || hidden.value !== '';
            hidden.value = '';
            input.value = '';
            committed = { value: '', label: '' };
            refreshUi();
            if (autosubmit && hadValue) {
                submitForm(root.closest('form'));
            } else {
                input.focus();
            }
        });

        root.addEventListener('focusout', (event) => {
            if (root.contains(event.relatedTarget)) return;
            close();

            if (squish(input.value) === '') {
                if (committed.value !== '') {
                    commit('', '');
                } else {
                    hidden.value = '';
                    input.value = '';
                    refreshUi();
                }
                return;
            }

            if (hidden.value) {
                // Exact match typed: lock it in with the canonical spelling.
                const option = options.find((o) => o.dataset.value === hidden.value);
                commit(option.dataset.value, option.dataset.label);
            } else if (creatable) {
                input.value = squish(input.value);
                committed = { value: '', label: input.value };
                refreshUi();
            } else {
                // Filters only accept existing values: restore the last valid choice.
                input.value = committed.label;
                hidden.value = committed.value;
                refreshUi();
            }
        });

        refreshUi();
    }

    /* ---------------------------------------------------------------
       Genre picker  (<x-genre-picker>)
       --------------------------------------------------------------- */
    function initGenrePicker(picker) {
        const list = picker.querySelector('[data-genre-list]');
        const input = picker.querySelector('[data-genre-new]');
        const button = picker.querySelector('[data-genre-add]');
        const template = picker.querySelector('[data-genre-template]');

        function pulse(el) {
            el.classList.remove('is-pulsing');
            void el.offsetWidth;
            el.classList.add('is-pulsing');
        }

        function add() {
            const name = squish(input.value);
            if (!name) {
                input.focus();
                return;
            }

            const existing = Array.from(list.querySelectorAll('.genre-toggle'))
                .find((el) => el.dataset.name.toLowerCase() === name.toLowerCase());

            if (existing) {
                existing.querySelector('input').checked = true;
                pulse(existing);
            } else {
                const chip = template.content.firstElementChild.cloneNode(true);
                chip.dataset.name = name;
                chip.querySelector('input').value = name;
                chip.querySelector('span').textContent = name;
                list.appendChild(chip);
                pulse(chip);
            }

            input.value = '';
            input.focus();
        }

        button.addEventListener('click', add);
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                add();
            }
        });
    }

    /* ---------------------------------------------------------------
       Poster upload preview  (<x-poster-upload>)
       --------------------------------------------------------------- */
    function initPosterUpload(box) {
        const fileInput = box.querySelector('[data-poster-input]');
        const preview = box.querySelector('[data-poster-preview]');
        const filename = box.querySelector('[data-poster-filename]');

        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (!file) return;
            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
            preview.closest('.poster-drop').classList.add('has-image');
            filename.textContent = file.name;
            filename.hidden = false;
        });
    }

    /* ---------------------------------------------------------------
       Confirmation dialog for forms with data-confirm  (<x-modal>)
       --------------------------------------------------------------- */
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!form.dataset.confirm) return;
        event.preventDefault();

        const dialog = document.getElementById('confirm-dialog');
        if (!dialog || typeof dialog.showModal !== 'function') {
            if (window.confirm(form.dataset.confirm)) form.submit();
            return;
        }

        dialog.querySelector('[data-confirm-message]').textContent = form.dataset.confirm;
        if (form.dataset.confirmTitle) {
            dialog.querySelector('[data-modal-title]').textContent = form.dataset.confirmTitle;
        }
        dialog.returnValue = '';
        dialog.showModal();
        dialog.addEventListener('close', () => {
            if (dialog.returnValue === 'confirm') {
                form.querySelector('button[type="submit"]')?.classList.add('is-loading');
                form.submit();
            }
        }, { once: true });
    });

    /* ---------------------------------------------------------------
       Loading states for forms with data-loading
       --------------------------------------------------------------- */
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!form.hasAttribute('data-loading') || event.defaultPrevented) return;
        form.querySelectorAll('button[type="submit"]').forEach((btn) => btn.classList.add('is-loading'));
        if (form.method.toLowerCase() === 'get') {
            document.querySelector('[data-results]')?.classList.add('is-loading');
        }
    });

    // Back/forward cache: undo loading state when the page is shown again.
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('.is-loading').forEach((el) => el.classList.remove('is-loading'));
    });

    /* ---------------------------------------------------------------
       Alerts / toasts  (<x-alert>)
       --------------------------------------------------------------- */
    function dismiss(alert) {
        alert.classList.add('is-leaving');
        setTimeout(() => alert.remove(), 250);
    }

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-dismiss]');
        if (button) dismiss(button.closest('.alert'));
    });

    /* ---------------------------------------------------------------
       Init
       --------------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-combobox]').forEach(initCombobox);
        document.querySelectorAll('[data-genre-picker]').forEach(initGenrePicker);
        document.querySelectorAll('[data-poster-upload]').forEach(initPosterUpload);
        document.querySelectorAll('[data-autodismiss]').forEach((alert) => {
            setTimeout(() => dismiss(alert), Number(alert.dataset.autodismiss));
        });

        // Press "/" anywhere to jump to the search box.
        document.addEventListener('keydown', (event) => {
            const tag = document.activeElement?.tagName;
            if (event.key !== '/' || ['INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) return;
            const search = document.querySelector('[data-search-shortcut]');
            if (search) {
                event.preventDefault();
                search.focus();
            }
        });
    });
})();
