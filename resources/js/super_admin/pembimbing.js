document.addEventListener('DOMContentLoaded', () => {
    const addPembimbingModal = document.getElementById('add-pembimbing-modal');
    const detailPembimbingModal = document.getElementById('detail-pembimbing-modal');
    const editPembimbingModal = document.getElementById('edit-pembimbing-modal');
    const resetPasswordPembimbingModal = document.getElementById('reset-password-pembimbing-modal');
    const filterPembimbingModal = document.getElementById('filter-pembimbing-modal');

    const openAddPembimbingModal = document.getElementById('open-add-pembimbing-modal');
    const closeAddPembimbingModal = document.getElementById('close-add-pembimbing-modal');
    const cancelAddPembimbingModal = document.getElementById('cancel-add-pembimbing-modal');

    const closeDetailPembimbingModal = document.getElementById('close-detail-pembimbing-modal');
    const closeDetailPembimbingAction = document.getElementById('close-detail-pembimbing-action');

    const closeEditPembimbingModal = document.getElementById('close-edit-pembimbing-modal');
    const cancelEditPembimbingModal = document.getElementById('cancel-edit-pembimbing-modal');

    const openFilterModal = document.getElementById('open-filter-modal');
    const closeFilterPembimbingModal = document.getElementById('close-filter-pembimbing-modal');
    const applyTableFilters = document.getElementById('apply-table-filters');
    const resetTableFilters = document.getElementById('reset-table-filters');

    const editPembimbingForm = document.getElementById('edit-pembimbing-form');
    const resetPasswordPembimbingForm = document.getElementById('reset-password-pembimbing-form');
    const closeResetPasswordPembimbingModal = document.getElementById('close-reset-password-pembimbing-modal');
    const cancelResetPasswordPembimbingModal = document.getElementById('cancel-reset-password-pembimbing-modal');
    const addPembimbingPassword = document.getElementById('password');
    const toggleAddPembimbingPassword = document.getElementById('toggle-add-pembimbing-password');
    const resetPembimbingPassword = document.getElementById('reset_password');
    const resetPembimbingPasswordConfirmation = document.getElementById('reset_password_confirmation');
    const toggleResetPembimbingPassword = document.getElementById('toggle-reset-pembimbing-password');

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

    openAddPembimbingModal?.addEventListener('click', () => toggleModal(addPembimbingModal, true));
    closeAddPembimbingModal?.addEventListener('click', () => toggleModal(addPembimbingModal, false));
    cancelAddPembimbingModal?.addEventListener('click', () => toggleModal(addPembimbingModal, false));
    toggleAddPembimbingPassword?.addEventListener('click', () => {
        if (! addPembimbingPassword) {
            return;
        }

        const isHidden = addPembimbingPassword.type === 'password';
        addPembimbingPassword.type = isHidden ? 'text' : 'password';
        toggleAddPembimbingPassword.textContent = isHidden ? 'Sembunyikan' : 'Tampilkan';
    });
    closeDetailPembimbingModal?.addEventListener('click', () => toggleModal(detailPembimbingModal, false));
    closeDetailPembimbingAction?.addEventListener('click', () => toggleModal(detailPembimbingModal, false));
    closeEditPembimbingModal?.addEventListener('click', () => toggleModal(editPembimbingModal, false));
    cancelEditPembimbingModal?.addEventListener('click', () => toggleModal(editPembimbingModal, false));
    closeResetPasswordPembimbingModal?.addEventListener('click', () => toggleModal(resetPasswordPembimbingModal, false));
    cancelResetPasswordPembimbingModal?.addEventListener('click', () => toggleModal(resetPasswordPembimbingModal, false));
    toggleResetPembimbingPassword?.addEventListener('click', () => {
        if (! resetPembimbingPassword || ! resetPembimbingPasswordConfirmation) {
            return;
        }

        const isHidden = resetPembimbingPassword.type === 'password';
        resetPembimbingPassword.type = isHidden ? 'text' : 'password';
        resetPembimbingPasswordConfirmation.type = isHidden ? 'text' : 'password';
        toggleResetPembimbingPassword.textContent = isHidden ? 'Sembunyikan' : 'Tampilkan';
    });

    // Filter Pembimbing Modal Events
    openFilterModal?.addEventListener('click', () => toggleModal(filterPembimbingModal, true));
    closeFilterPembimbingModal?.addEventListener('click', () => toggleModal(filterPembimbingModal, false));

    document.querySelectorAll('[data-action="view-pembimbing"]').forEach((button) => {
        button.addEventListener('click', () => {
            setText('detail-nip', button.dataset.nip);
            setText('detail-nama', button.dataset.nama);
            setText('detail-email', button.dataset.email);
            setText('detail-telepon', button.dataset.telepon);
            setText('detail-alamat', button.dataset.alamat);
            setText('detail-instansi', button.dataset.instansi);
            setText('detail-status', button.dataset.status);
            toggleModal(detailPembimbingModal, true);
        });
    });

    document.querySelectorAll('[data-action="edit-pembimbing"]').forEach((button) => {
        button.addEventListener('click', () => {
            const actionTemplate = editPembimbingForm?.dataset.actionTemplate || '';
            if (editPembimbingForm) {
                editPembimbingForm.action = actionTemplate.replace('__ID__', button.dataset.id);
            }

            setValue('edit_id', button.dataset.id);
            setValue('edit_nip', button.dataset.nip);
            setValue('edit_nama_lengkap', button.dataset.nama);
            setValue('edit_email', button.dataset.email);
            setValue('edit_no_telepon', button.dataset.telepon);
            setValue('edit_alamat', button.dataset.alamat);
            setValue('edit_instansi', button.dataset.instansi);
            setValue('edit_status_aktif', button.dataset.status);
            toggleModal(editPembimbingModal, true);
        });
    });

    document.querySelectorAll('[data-action="reset-password-pembimbing"]').forEach((button) => {
        button.addEventListener('click', () => {
            const actionTemplate = resetPasswordPembimbingForm?.dataset.actionTemplate || '';
            if (resetPasswordPembimbingForm) {
                resetPasswordPembimbingForm.action = actionTemplate.replace('__ID__', button.dataset.id);
                resetPasswordPembimbingForm.reset();
                resetPasswordPembimbingForm.dataset.nip = button.dataset.nip || '';
                resetPasswordPembimbingForm.dataset.name = button.dataset.name || '';
            }

            setValue('reset_id', button.dataset.id);
            setText('reset-password-pembimbing-name', button.dataset.name);
            if (resetPembimbingPassword && resetPembimbingPasswordConfirmation && toggleResetPembimbingPassword) {
                resetPembimbingPassword.type = 'password';
                resetPembimbingPasswordConfirmation.type = 'password';
                toggleResetPembimbingPassword.textContent = 'Tampilkan';
            }
            toggleModal(resetPasswordPembimbingModal, true);
        });
    });

    document.querySelectorAll('.delete-pembimbing-form').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const name = form.querySelector('[data-name]')?.dataset.name || 'pembimbing ini';
            const confirmed = window.confirmDangerAction
                ? await window.confirmDangerAction({
                    title: 'Hapus Pembimbing?',
                    text: `Data ${name} akan dihapus dari daftar pembimbing.`,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                })
                : window.confirm(`Hapus data ${name}?`);

            if (confirmed) {
                form.submit();
            }
        });
    });

    [addPembimbingModal, detailPembimbingModal, editPembimbingModal, resetPasswordPembimbingModal, filterPembimbingModal].forEach((modal) => {
        modal?.addEventListener('click', (event) => {
            if (event.target === modal) {
                toggleModal(modal, false);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            toggleModal(addPembimbingModal, false);
            toggleModal(detailPembimbingModal, false);
            toggleModal(editPembimbingModal, false);
            toggleModal(resetPasswordPembimbingModal, false);
            toggleModal(filterPembimbingModal, false);
        }
    });

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

        const isFilterActive = statusVal !== '' || instansiVal !== '';
        const filterBtn = document.getElementById('open-filter-modal');
        if (filterBtn) {
            filterBtn.classList.toggle('btn-active-filter', isFilterActive);
        }

        filteredRows = [];

        tableRows.forEach(row => {
            if (row.querySelector('.empty-state')) {
                return;
            }

            const viewBtn = row.querySelector('[data-action="view-pembimbing"]');
            const editBtn = row.querySelector('[data-action="edit-pembimbing"]');

            const nip = (viewBtn?.dataset.nip || '').toLowerCase();
            const nama = (viewBtn?.dataset.nama || '').toLowerCase();
            const email = (viewBtn?.dataset.email || '').toLowerCase();
            const instansi = viewBtn?.dataset.instansi || '';
            const status = editBtn?.dataset.status || '';

            const matchesSearch = query === '' || 
                nip.includes(query) || 
                nama.includes(query) || 
                email.includes(query) || 
                instansi.toLowerCase().includes(query);

            const matchesStatus = statusVal === '' || status === statusVal;
            const matchesInstansi = instansiVal === '' || instansi === instansiVal;

            if (matchesSearch && matchesStatus && matchesInstansi) {
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
        toggleModal(filterPembimbingModal, false);
    });
    resetTableFilters?.addEventListener('click', () => {
        if (filterStatus) filterStatus.value = '';
        if (filterInstansi) filterInstansi.value = '';
        currentPage = 1;
        filterTable();
        toggleModal(filterPembimbingModal, false);
    });

    // Initialize table
    filterTable();

    // ===== Password Generator Action =====
    const nipInput = document.getElementById('nip');
    const nameInput = document.getElementById('nama_lengkap');
    const btnGeneratePassword = document.getElementById('btn-generate-pembimbing-password');

    function generateRandomPassword(nipValRaw, nameValRaw) {
        const nipVal = (nipValRaw || '').replace(/[^0-9]/g, '');
        const nameVal = (nameValRaw || '').replace(/[^a-zA-Z]/g, '');
        
        const L = Math.floor(Math.random() * 3) + 8;
        
        const uppers = nameVal.replace(/[^A-Z]/g, '');
        const charUpper = uppers.length > 0 ? uppers[Math.floor(Math.random() * uppers.length)] : String.fromCharCode(65 + Math.floor(Math.random() * 26));
        
        const lowers = nameVal.replace(/[^a-z]/g, '');
        const charLower = lowers.length > 0 ? lowers[Math.floor(Math.random() * lowers.length)] : String.fromCharCode(97 + Math.floor(Math.random() * 26));
        
        const charDigit = nipVal.length > 0 ? nipVal[Math.floor(Math.random() * nipVal.length)] : String.fromCharCode(48 + Math.floor(Math.random() * 10));
        
        const charUnderscore = '_';
        
        let pwdArr = [charUpper, charLower, charDigit, charUnderscore];
        
        let pool = nameVal + nipVal + 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
        
        while (pwdArr.length < L) {
            const randChar = pool[Math.floor(Math.random() * pool.length)];
            pwdArr.push(randChar);
        }
        
        for (let i = pwdArr.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [pwdArr[i], pwdArr[j]] = [pwdArr[j], pwdArr[i]];
        }
        
        return pwdArr.join('');
    }

    btnGeneratePassword?.addEventListener('click', () => {
        if (!addPembimbingPassword) return;
        
        const pwd = generateRandomPassword(nipInput?.value || '', nameInput?.value || '');
        addPembimbingPassword.value = pwd;
        addPembimbingPassword.type = 'text';
        
        if (toggleAddPembimbingPassword) {
            toggleAddPembimbingPassword.textContent = 'Sembunyikan';
        }
        
        if (window.Swal) {
            window.Swal.fire({
                background: document.documentElement.getAttribute('data-theme') === 'light' ? '#ffffff' : '#1e293b',
                color: document.documentElement.getAttribute('data-theme') === 'light' ? '#0f172a' : '#f8fafc',
                confirmButtonColor: '#2e4085',
                icon: 'success',
                title: 'Password Dibuat!',
                html: `Password acak telah dibuat:<br><strong style="font-family: monospace; font-size: 1.2rem; color: var(--accent-primary); letter-spacing: 2px;">${pwd}</strong>`,
                timer: 4000,
                showConfirmButton: true,
                confirmButtonText: 'Oke'
            });
        }
    });

    if (addPembimbingPassword) {
        addPembimbingPassword.required = true;
    }

    // ===== Reset Password Generator Action =====
    const btnGenerateResetPassword = document.getElementById('btn-generate-pembimbing-reset-password');
    const resetPasswordFormEl = document.getElementById('reset-password-pembimbing-form');

    btnGenerateResetPassword?.addEventListener('click', () => {
        if (!resetPembimbingPassword || !resetPembimbingPasswordConfirmation) return;
        
        const pwd = generateRandomPassword(resetPasswordFormEl?.dataset.nip || '', resetPasswordFormEl?.dataset.name || '');
        resetPembimbingPassword.value = pwd;
        resetPembimbingPasswordConfirmation.value = pwd;
        
        resetPembimbingPassword.type = 'text';
        resetPembimbingPasswordConfirmation.type = 'text';
        if (toggleResetPembimbingPassword) {
            toggleResetPembimbingPassword.textContent = 'Sembunyikan';
        }
        
        if (window.Swal) {
            window.Swal.fire({
                background: document.documentElement.getAttribute('data-theme') === 'light' ? '#ffffff' : '#1e293b',
                color: document.documentElement.getAttribute('data-theme') === 'light' ? '#0f172a' : '#f8fafc',
                confirmButtonColor: '#2e4085',
                icon: 'success',
                title: 'Password Dibuat!',
                html: `Password baru acak telah dibuat:<br><strong style="font-family: monospace; font-size: 1.2rem; color: var(--accent-primary); letter-spacing: 2px;">${pwd}</strong>`,
                timer: 4000,
                showConfirmButton: true,
                confirmButtonText: 'Oke'
            });
        }
    });
});
