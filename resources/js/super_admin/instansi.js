document.addEventListener('DOMContentLoaded', () => {
    const addInstansiModal = document.getElementById('add-instansi-modal');
    const editInstansiModal = document.getElementById('edit-instansi-modal');

    // ===== Security: Sanitize nama_instansi inputs =====
    // Allowed: letters, numbers, spaces, and: - / \ " ' & ( ) . ,
    // Blocked: < > ; = { } | ^ ` ~ # % etc.
    const NAMA_PATTERN = /[^a-zA-Z0-9\s\-\/\\().,'"&]/g;
    const MAX_NAMA_LENGTH = 170;

    function initNamaInput(inputEl) {
        if (!inputEl) return;
        // Create char counter
        const counter = document.createElement('div');
        counter.className = 'instansi-char-counter';
        inputEl.parentNode.insertBefore(counter, inputEl.nextSibling);

        function updateCounter() {
            const len = inputEl.value.length;
            const rem = MAX_NAMA_LENGTH - len;
            counter.textContent = `${len} / ${MAX_NAMA_LENGTH} karakter`;
            counter.style.color = rem <= 20
                ? (rem <= 0 ? '#f87171' : '#fbbf24')
                : 'var(--text-secondary)';
        }

        inputEl.addEventListener('input', () => {
            const cleaned = inputEl.value.replace(NAMA_PATTERN, '');
            if (cleaned !== inputEl.value) {
                const pos = inputEl.selectionStart - (inputEl.value.length - cleaned.length);
                inputEl.value = cleaned;
                inputEl.setSelectionRange(pos, pos);
            }
            updateCounter();
        });

        inputEl.addEventListener('paste', () => {
            setTimeout(() => {
                inputEl.value = inputEl.value.replace(NAMA_PATTERN, '').substring(0, MAX_NAMA_LENGTH);
                updateCounter();
            }, 0);
        });

        updateCounter();
    }

    initNamaInput(document.getElementById('nama_instansi'));
    initNamaInput(document.getElementById('edit_nama_instansi'));

    // ===== Custom Select Dropdown Logic =====
    function initCustomSelect(wrapperId, hiddenInputId, valueLabelSelector) {
        const wrapper = document.getElementById(wrapperId);
        if (!wrapper) return;
        const trigger = wrapper.querySelector('.custom-select-trigger');
        const valueLabel = wrapper.querySelector('.custom-select-value');
        const options = wrapper.querySelectorAll('.custom-select-option');
        const hiddenInput = document.getElementById(hiddenInputId);

        function openSelect() {
            wrapper.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
        }

        function closeSelect() {
            wrapper.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        function selectOption(li) {
            const val = li.dataset.value;
            const text = li.textContent.trim();
            if (hiddenInput) hiddenInput.value = val;
            if (valueLabel) valueLabel.textContent = text;
            options.forEach(o => o.classList.remove('is-selected'));
            li.classList.add('is-selected');
            closeSelect();
        }

        trigger?.addEventListener('click', (e) => {
            e.stopPropagation();
            wrapper.classList.contains('is-open') ? closeSelect() : openSelect();
        });

        options.forEach(li => {
            li.addEventListener('click', () => selectOption(li));
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) closeSelect();
        });

        // Expose a setter for JS-driven value changes (e.g. when Edit modal opens)
        wrapper._setSelectValue = function(val) {
            const match = [...options].find(o => o.dataset.value === val);
            if (match) {
                selectOption(match);
            } else {
                if (hiddenInput) hiddenInput.value = '';
                if (valueLabel) valueLabel.textContent = 'Pilih Jenis';
                options.forEach(o => o.classList.remove('is-selected'));
            }
        };
    }

    initCustomSelect('add-jenis-wrapper', 'jenis');
    initCustomSelect('edit-jenis-wrapper', 'edit_jenis');

    const filterInstansiModal = document.getElementById('filter-instansi-modal');


    const openAddInstansiModal = document.getElementById('open-add-instansi-modal');
    const closeAddInstansiModal = document.getElementById('close-add-instansi-modal');
    const cancelAddInstansiModal = document.getElementById('cancel-add-instansi-modal');

    const closeEditInstansiModal = document.getElementById('close-edit-instansi-modal');
    const cancelEditInstansiModal = document.getElementById('cancel-edit-instansi-modal');

    const openFilterModal = document.getElementById('open-filter-modal');
    const closeFilterInstansiModal = document.getElementById('close-filter-instansi-modal');
    const applyTableFilters = document.getElementById('apply-table-filters');
    const resetTableFilters = document.getElementById('reset-table-filters');

    const editInstansiForm = document.getElementById('edit-instansi-form');

    function toggleModal(modal, isOpen) {
        modal?.classList.toggle('is-open', isOpen);
        modal?.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    }

    function setValue(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.value = value || '';
            element.dispatchEvent(new Event('change'));
        }
    }

    openAddInstansiModal?.addEventListener('click', () => toggleModal(addInstansiModal, true));
    closeAddInstansiModal?.addEventListener('click', () => toggleModal(addInstansiModal, false));
    cancelAddInstansiModal?.addEventListener('click', () => toggleModal(addInstansiModal, false));

    closeEditInstansiModal?.addEventListener('click', () => toggleModal(editInstansiModal, false));
    cancelEditInstansiModal?.addEventListener('click', () => toggleModal(editInstansiModal, false));

    openFilterModal?.addEventListener('click', () => toggleModal(filterInstansiModal, true));
    closeFilterInstansiModal?.addEventListener('click', () => toggleModal(filterInstansiModal, false));

    // When edit button is clicked, also update the custom select
    document.querySelectorAll('[data-action="edit-instansi"]').forEach((button) => {
        button.addEventListener('click', () => {
            const actionTemplate = editInstansiForm?.dataset.actionTemplate || '';
            if (editInstansiForm) {
                editInstansiForm.action = actionTemplate.replace('__ID__', button.dataset.id);
            }

            setValue('edit_id', button.dataset.id);
            setValue('edit_nama_instansi', button.dataset.nama);

            // Update custom select for jenis
            const editWrapper = document.getElementById('edit-jenis-wrapper');
            if (editWrapper?._setSelectValue) {
                editWrapper._setSelectValue(button.dataset.jenis);
            } else {
                setValue('edit_jenis', button.dataset.jenis);
            }

            // Update char counter for nama
            document.getElementById('edit_nama_instansi')?.dispatchEvent(new Event('input'));

            toggleModal(editInstansiModal, true);
        });
    });

    document.querySelectorAll('.delete-instansi-form').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const name = form.querySelector('[data-name]')?.dataset.name || 'instansi ini';
            const confirmed = window.confirmDangerAction
                ? await window.confirmDangerAction({
                    title: 'Hapus Instansi?',
                    text: `Data ${name} akan dihapus secara permanen.`,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                })
                : window.confirm(`Hapus data ${name}?`);

            if (confirmed) {
                form.submit();
            }
        });
    });

    [addInstansiModal, editInstansiModal, filterInstansiModal].forEach((modal) => {
        modal?.addEventListener('click', (event) => {
            if (event.target === modal) {
                toggleModal(modal, false);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            toggleModal(addInstansiModal, false);
            toggleModal(editInstansiModal, false);
            toggleModal(filterInstansiModal, false);
        }
    });

    // Client-side Table Search, Filters and Pagination
    const tableSearch = document.getElementById('table-search');
    const filterJenis = document.getElementById('filter-jenis');
    const tableRows = document.querySelectorAll('.custom-table tbody tr:not(#table-no-results)');
    const noResultsRow = document.getElementById('table-no-results');

    const paginationContainer = document.getElementById('table-pagination');
    const paginationStart = document.getElementById('pagination-start');
    const paginationEnd = document.getElementById('pagination-end');
    const paginationTotal = document.getElementById('pagination-total');
    const paginationControls = document.getElementById('pagination-controls');

    let currentPage = 1;
    const itemsPerPage = 5;
    let filteredRows = [];

    function renderPagination() {
        const totalItems = filteredRows.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        if (totalItems === 0) {
            if (paginationContainer) paginationContainer.style.display = 'none';
            return;
        }

        if (paginationContainer) paginationContainer.style.display = 'flex';

        if (currentPage > totalPages) {
            currentPage = 1;
        }

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

        tableRows.forEach(row => {
            if (!row.querySelector('.empty-state')) {
                row.style.display = 'none';
            }
        });

        for (let i = startIndex; i < endIndex; i++) {
            filteredRows[i].style.display = '';
        }

        if (paginationStart) paginationStart.textContent = startIndex + 1;
        if (paginationEnd) paginationEnd.textContent = endIndex;
        if (paginationTotal) paginationTotal.textContent = totalItems;

        if (paginationControls) {
            paginationControls.innerHTML = '';

            const prevBtn = document.createElement('button');
            prevBtn.type = 'button';
            prevBtn.className = 'pagination-btn';
            prevBtn.disabled = currentPage === 1;
            prevBtn.innerHTML = '&larr;';
            prevBtn.addEventListener('click', () => {
                currentPage--;
                renderPagination();
            });
            paginationControls.appendChild(prevBtn);

            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.type = 'button';
                pageBtn.className = 'pagination-btn';
                if (i === currentPage) pageBtn.classList.add('is-active');
                pageBtn.textContent = i;
                pageBtn.addEventListener('click', () => {
                    currentPage = i;
                    renderPagination();
                });
                paginationControls.appendChild(pageBtn);
            }

            const nextBtn = document.createElement('button');
            nextBtn.type = 'button';
            nextBtn.className = 'pagination-btn';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.innerHTML = '&rarr;';
            nextBtn.addEventListener('click', () => {
                currentPage++;
                renderPagination();
            });
            paginationControls.appendChild(nextBtn);
        }
    }

    function filterTable() {
        const query = tableSearch?.value.toLowerCase().trim() || '';
        const jenisVal = filterJenis?.value || '';

        const isFilterActive = jenisVal !== '';
        const filterBtn = document.getElementById('open-filter-modal');
        if (filterBtn) {
            filterBtn.classList.toggle('btn-active-filter', isFilterActive);
        }

        filteredRows = [];

        tableRows.forEach(row => {
            if (row.querySelector('.empty-state')) {
                return;
            }

            const editBtn = row.querySelector('[data-action="edit-instansi"]');

            const nama = (editBtn?.dataset.nama || '').toLowerCase();
            const jenis = editBtn?.dataset.jenis || '';

            const matchesSearch = query === '' || 
                nama.includes(query) || 
                jenis.toLowerCase().includes(query);

            const matchesJenis = jenisVal === '' || jenis === jenisVal;

            if (matchesSearch && matchesJenis) {
                filteredRows.push(row);
            } else {
                row.style.display = 'none';
            }
        });

        const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
        if (currentPage > totalPages) {
            currentPage = 1;
        }

        if (noResultsRow) {
            const hasActualData = tableRows.length > 0 && !tableRows[0].querySelector('.empty-state');
            noResultsRow.style.display = (hasActualData && filteredRows.length === 0) ? '' : 'none';
        }

        renderPagination();
    }

    tableSearch?.addEventListener('input', () => {
        currentPage = 1;
        filterTable();
    });
    applyTableFilters?.addEventListener('click', () => {
        currentPage = 1;
        filterTable();
        toggleModal(filterInstansiModal, false);
    });
    resetTableFilters?.addEventListener('click', () => {
        if (filterJenis) filterJenis.value = '';
        currentPage = 1;
        filterTable();
        toggleModal(filterInstansiModal, false);
    });

    // Initialize table
    filterTable();
});
