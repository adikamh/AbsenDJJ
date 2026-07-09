document.addEventListener('DOMContentLoaded', () => {
    const addPesertaModal = document.getElementById('add-peserta-modal');
    const detailPesertaModal = document.getElementById('detail-peserta-modal');
    const editPesertaModal = document.getElementById('edit-peserta-modal');
    const resetPasswordPesertaModal = document.getElementById('reset-password-peserta-modal');
    const filterPesertaModal = document.getElementById('filter-peserta-modal');

    const openAddPesertaModal = document.getElementById('open-add-peserta-modal');
    const closeAddPesertaModal = document.getElementById('close-add-peserta-modal');
    const cancelAddPesertaModal = document.getElementById('cancel-add-peserta-modal');

    const closeDetailPesertaModal = document.getElementById('close-detail-peserta-modal');
    const closeDetailPesertaAction = document.getElementById('close-detail-peserta-action');

    const closeEditPesertaModal = document.getElementById('close-edit-peserta-modal');
    const cancelEditPesertaModal = document.getElementById('cancel-edit-peserta-modal');

    const openFilterModal = document.getElementById('open-filter-modal');
    const closeFilterPesertaModal = document.getElementById('close-filter-peserta-modal');
    const applyTableFilters = document.getElementById('apply-table-filters');
    const resetTableFilters = document.getElementById('reset-table-filters');
    const editPesertaForm = document.getElementById('edit-peserta-form');
    const resetPasswordPesertaForm = document.getElementById('reset-password-peserta-form');
    const closeResetPasswordPesertaModal = document.getElementById('close-reset-password-peserta-modal');
    const cancelResetPasswordPesertaModal = document.getElementById('cancel-reset-password-peserta-modal');
    const addPesertaPassword = document.getElementById('password');
    const toggleAddPesertaPassword = document.getElementById('toggle-add-peserta-password');
    const resetPesertaPassword = document.getElementById('reset_password');
    const resetPesertaPasswordConfirmation = document.getElementById('reset_password_confirmation');
    const toggleResetPesertaPassword = document.getElementById('toggle-reset-peserta-password');

    function togglePesertaModal(isOpen) {
        addPesertaModal?.classList.toggle('is-open', isOpen);
        addPesertaModal?.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    }

    function toggleModal(modal, isOpen) {
        modal?.classList.toggle('is-open', isOpen);
        modal?.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    }

    function setText(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value || '-';
        }
    }

    function setValue(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.value = value || '';
            element.dispatchEvent(new Event('change'));
        }
    }

    openAddPesertaModal?.addEventListener('click', () => togglePesertaModal(true));
    closeAddPesertaModal?.addEventListener('click', () => togglePesertaModal(false));
    cancelAddPesertaModal?.addEventListener('click', () => togglePesertaModal(false));
    toggleAddPesertaPassword?.addEventListener('click', () => {
        if (! addPesertaPassword) {
            return;
        }

        const isHidden = addPesertaPassword.type === 'password';
        addPesertaPassword.type = isHidden ? 'text' : 'password';
        toggleAddPesertaPassword.textContent = isHidden ? 'Sembunyikan' : 'Tampilkan';
    });

    closeDetailPesertaModal?.addEventListener('click', () => toggleModal(detailPesertaModal, false));
    closeDetailPesertaAction?.addEventListener('click', () => toggleModal(detailPesertaModal, false));
    closeEditPesertaModal?.addEventListener('click', () => toggleModal(editPesertaModal, false));
    cancelEditPesertaModal?.addEventListener('click', () => toggleModal(editPesertaModal, false));
    closeResetPasswordPesertaModal?.addEventListener('click', () => toggleModal(resetPasswordPesertaModal, false));
    cancelResetPasswordPesertaModal?.addEventListener('click', () => toggleModal(resetPasswordPesertaModal, false));
    toggleResetPesertaPassword?.addEventListener('click', () => {
        if (! resetPesertaPassword || ! resetPesertaPasswordConfirmation) {
            return;
        }

        const isHidden = resetPesertaPassword.type === 'password';
        resetPesertaPassword.type = isHidden ? 'text' : 'password';
        resetPesertaPasswordConfirmation.type = isHidden ? 'text' : 'password';
        toggleResetPesertaPassword.textContent = isHidden ? 'Sembunyikan' : 'Tampilkan';
    });

    // Filter Peserta Modal Events
    openFilterModal?.addEventListener('click', () => toggleModal(filterPesertaModal, true));
    closeFilterPesertaModal?.addEventListener('click', () => toggleModal(filterPesertaModal, false));

    document.querySelectorAll('[data-action="view-peserta"]').forEach((button) => {
        button.addEventListener('click', () => {
            setText('detail-nip', button.dataset.nip);
            setText('detail-nama', button.dataset.nama);
            setText('detail-email', button.dataset.email);
            setText('detail-telepon', button.dataset.telepon);
            setText('detail-alamat', button.dataset.alamat);
            setText('detail-no-darurat-1', button.dataset.noDarurat1);
            setText('detail-hubungan-darurat-1', button.dataset.hubunganDarurat1);
            setText('detail-no-darurat-2', button.dataset.noDarurat2);
            setText('detail-hubungan-darurat-2', button.dataset.hubunganDarurat2);
            setText('detail-instansi', button.dataset.instansi);
            setText('detail-pembimbing', button.dataset.pembimbing);
            setText('detail-status', button.dataset.status);
            toggleModal(detailPesertaModal, true);
        });
    });

    document.querySelectorAll('[data-action="edit-peserta"]').forEach((button) => {
        button.addEventListener('click', () => {
            const actionTemplate = editPesertaForm?.dataset.actionTemplate || '';
            if (editPesertaForm) {
                editPesertaForm.action = actionTemplate.replace('__ID__', button.dataset.id);
            }

            setValue('edit_id', button.dataset.id);
            setValue('edit_nip', button.dataset.nip);
            setValue('edit_nama_lengkap', button.dataset.nama);
            setValue('edit_email', button.dataset.email);
            setValue('edit_no_telepon', button.dataset.telepon);
            setValue('edit_alamat', button.dataset.alamat);
            setValue('edit_no_darurat_1', button.dataset.noDarurat1);
            setValue('edit_hubungan_darurat_1', button.dataset.hubunganDarurat1);
            setValue('edit_no_darurat_2', button.dataset.noDarurat2);
            setValue('edit_hubungan_darurat_2', button.dataset.hubunganDarurat2);
            setValue('edit_instansi', button.dataset.instansi);
            setValue('edit_pembimbing_id', button.dataset.pembimbingId);
            setValue('edit_status_aktif', button.dataset.status);
            toggleModal(editPesertaModal, true);
        });
    });

    document.querySelectorAll('[data-action="reset-password-peserta"]').forEach((button) => {
        button.addEventListener('click', () => {
            const actionTemplate = resetPasswordPesertaForm?.dataset.actionTemplate || '';
            if (resetPasswordPesertaForm) {
                resetPasswordPesertaForm.action = actionTemplate.replace('__ID__', button.dataset.id);
                resetPasswordPesertaForm.reset();
            }

            setValue('reset_id', button.dataset.id);
            setText('reset-password-peserta-name', button.dataset.name);
            if (resetPesertaPassword && resetPesertaPasswordConfirmation && toggleResetPesertaPassword) {
                resetPesertaPassword.type = 'password';
                resetPesertaPasswordConfirmation.type = 'password';
                toggleResetPesertaPassword.textContent = 'Tampilkan';
            }
            toggleModal(resetPasswordPesertaModal, true);
        });
    });

    document.querySelectorAll('.delete-peserta-form').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const name = form.querySelector('[data-name]')?.dataset.name || 'peserta ini';
            const confirmed = window.confirmDangerAction
                ? await window.confirmDangerAction({
                    title: 'Hapus Peserta?',
                    text: `Data ${name} akan dihapus dari daftar peserta.`,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                })
                : window.confirm(`Hapus data ${name}?`);

            if (confirmed) {
                form.submit();
            }
        });
    });

    [addPesertaModal, detailPesertaModal, editPesertaModal, resetPasswordPesertaModal, filterPesertaModal].forEach((modal) => {
        modal?.addEventListener('click', (event) => {
            if (event.target === modal) {
                toggleModal(modal, false);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            togglePesertaModal(false);
            toggleModal(detailPesertaModal, false);
            toggleModal(editPesertaModal, false);
            toggleModal(resetPasswordPesertaModal, false);
            toggleModal(filterPesertaModal, false);
        }
    });

    // Searchable Dropdown Implementation
    function initSearchableSelect(select) {
        if (!select || select.dataset.searchableInit) return;
        select.dataset.searchableInit = 'true';

        select.style.display = 'none';

        const container = document.createElement('div');
        container.className = 'searchable-select-container';

        const trigger = document.createElement('input');
        trigger.type = 'text';
        trigger.className = 'searchable-select-trigger';
        trigger.placeholder = select.options[0]?.text || 'Pilih...';
        trigger.autocomplete = 'off';

        const dropdown = document.createElement('div');
        dropdown.className = 'searchable-select-dropdown';

        const noResults = document.createElement('div');
        noResults.className = 'searchable-select-no-results';
        noResults.textContent = 'Tidak ada pembimbing ditemukan';
        dropdown.appendChild(noResults);

        const options = [];

        function updateOptions() {
            const children = [...dropdown.children];
            children.forEach(child => {
                if (child !== noResults) dropdown.removeChild(child);
            });
            options.length = 0;

            [...select.options].forEach((opt) => {
                if (opt.value === "") return;
                const optDiv = document.createElement('div');
                optDiv.className = 'searchable-select-option';
                optDiv.dataset.value = opt.value;
                optDiv.textContent = opt.text;

                if (opt.selected) {
                    optDiv.classList.add('is-selected');
                    trigger.value = opt.text;
                }

                optDiv.addEventListener('click', (e) => {
                    e.stopPropagation();
                    select.value = opt.value;
                    select.dispatchEvent(new Event('change'));
                    trigger.value = opt.text;
                    closeDropdown();
                });

                dropdown.appendChild(optDiv);
                options.push(optDiv);
            });
        }

        updateOptions();

        select.parentNode.insertBefore(container, select.nextSibling);
        container.appendChild(trigger);
        container.appendChild(dropdown);

        function openDropdown() {
            dropdown.classList.add('is-open');
            filterOptions(trigger.value);
        }

        function closeDropdown() {
            dropdown.classList.remove('is-open');
            const selectedOpt = select.options[select.selectedIndex];
            trigger.value = selectedOpt && selectedOpt.value !== "" ? selectedOpt.text : '';
        }

        function filterOptions(query) {
            let hasVisible = false;
            const normalizedQuery = query.toLowerCase().trim();

            options.forEach(opt => {
                const matches = opt.textContent.toLowerCase().includes(normalizedQuery);
                opt.classList.toggle('is-hidden', !matches);
                if (matches) hasVisible = true;
                opt.classList.toggle('is-selected', opt.dataset.value === select.value);
            });

            noResults.style.display = hasVisible ? 'none' : 'block';
        }

        trigger.addEventListener('focus', () => {
            trigger.value = '';
            openDropdown();
        });

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            openDropdown();
        });

        trigger.addEventListener('input', () => {
            openDropdown();
            filterOptions(trigger.value);
        });

        select.addEventListener('change', () => {
            const selectedOpt = select.options[select.selectedIndex];
            trigger.value = selectedOpt && selectedOpt.value !== "" ? selectedOpt.text : '';
        });

        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                closeDropdown();
            }
        });
    }

    document.querySelectorAll('.searchable-select').forEach(initSearchableSelect);

    // Autocomplete Text Input Implementation
    function initAutocompleteInput(input, suggestions) {
        if (!input || input.dataset.autocompleteInit) return;
        input.dataset.autocompleteInit = 'true';

        const container = document.createElement('div');
        container.className = 'searchable-select-container';
        input.parentNode.insertBefore(container, input);
        container.appendChild(input);

        const dropdown = document.createElement('div');
        dropdown.className = 'searchable-select-dropdown';
        container.appendChild(dropdown);

        const options = [];

        suggestions.forEach((text) => {
            const optDiv = document.createElement('div');
            optDiv.className = 'searchable-select-option';
            optDiv.textContent = text;

            optDiv.addEventListener('click', (e) => {
                e.stopPropagation();
                input.value = text;
                input.dispatchEvent(new Event('input'));
                input.dispatchEvent(new Event('change'));
                closeDropdown();
            });

            dropdown.appendChild(optDiv);
            options.push(optDiv);
        });

        function openDropdown() {
            if (suggestions.length === 0) return;
            dropdown.classList.add('is-open');
            filterOptions(input.value);
        }

        function closeDropdown() {
            dropdown.classList.remove('is-open');
        }

        function filterOptions(query) {
            const normalizedQuery = query.toLowerCase().trim();
            let hasVisible = false;

            options.forEach(opt => {
                const matches = opt.textContent.toLowerCase().includes(normalizedQuery);
                opt.classList.toggle('is-hidden', !matches);
                if (matches) hasVisible = true;
            });

            if (hasVisible) {
                dropdown.classList.add('is-open');
            } else {
                dropdown.classList.remove('is-open');
            }
        }

        input.addEventListener('focus', () => {
            openDropdown();
        });

        input.addEventListener('click', (e) => {
            e.stopPropagation();
            openDropdown();
        });

        input.addEventListener('input', () => {
            filterOptions(input.value);
        });

        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                closeDropdown();
            }
        });
    }

    document.querySelectorAll('.autocomplete-instansi').forEach((input) => {
        const suggestions = JSON.parse(input.dataset.suggestions || '[]');
        initAutocompleteInput(input, suggestions);
    });

    // Client-side Table Search, Filters and Pagination
    const tableSearch = document.getElementById('table-search');
    const filterStatus = document.getElementById('filter-status');
    const filterInstansi = document.getElementById('filter-instansi');
    const filterPembimbing = document.getElementById('filter-pembimbing');
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
        const statusVal = filterStatus?.value || '';
        const instansiVal = filterInstansi?.value || '';
        const pembimbingVal = filterPembimbing?.value || '';

        const isFilterActive = statusVal !== '' || instansiVal !== '' || pembimbingVal !== '';
        const filterBtn = document.getElementById('open-filter-modal');
        if (filterBtn) {
            filterBtn.classList.toggle('btn-active-filter', isFilterActive);
        }

        filteredRows = [];

        tableRows.forEach(row => {
            if (row.querySelector('.empty-state')) {
                return;
            }

            const viewBtn = row.querySelector('[data-action="view-peserta"]');
            const editBtn = row.querySelector('[data-action="edit-peserta"]');

            const nip = (viewBtn?.dataset.nip || '').toLowerCase();
            const nama = (viewBtn?.dataset.nama || '').toLowerCase();
            const email = (viewBtn?.dataset.email || '').toLowerCase();
            const instansi = viewBtn?.dataset.instansi || '';
            const pembimbing = viewBtn?.dataset.pembimbing || '';
            const status = editBtn?.dataset.status || '';

            const matchesSearch = query === '' || 
                nip.includes(query) || 
                nama.includes(query) || 
                email.includes(query) || 
                instansi.toLowerCase().includes(query);

            const matchesStatus = statusVal === '' || status === statusVal;
            const matchesInstansi = instansiVal === '' || instansi === instansiVal;
            const matchesPembimbing = pembimbingVal === '' || pembimbing === pembimbingVal;

            if (matchesSearch && matchesStatus && matchesInstansi && matchesPembimbing) {
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
        toggleModal(filterPesertaModal, false);
    });
    resetTableFilters?.addEventListener('click', () => {
        if (filterStatus) filterStatus.value = '';
        if (filterInstansi) filterInstansi.value = '';
        if (filterPembimbing) filterPembimbing.value = '';
        currentPage = 1;
        filterTable();
        toggleModal(filterPesertaModal, false);
    });

    // Initialize table
    filterTable();
});
