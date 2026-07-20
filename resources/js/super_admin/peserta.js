document.addEventListener('DOMContentLoaded', () => {
    const addPesertaModal = document.getElementById('add-peserta-modal');
    const detailPesertaModal = document.getElementById('detail-peserta-modal');
    const editPesertaModal = document.getElementById('edit-peserta-modal');
    const resetPasswordPesertaModal = document.getElementById('reset-password-peserta-modal');
    const filterPesertaModal = document.getElementById('filter-peserta-modal');

    // ===== Security Sanitizers =====
    const CHARS_PATTERN = /[^a-zA-Z0-9\s\-\/\\().,'"&]/g;
    const DIGITS_ONLY_PATTERN = /[^0-9]/g;
    const EMAIL_ALLOWED_PATTERN = /[^\w\d\.\-\+@_]/g;
    const ADDRESS_ALLOWED_PATTERN = /[^a-zA-Z0-9\s\-\/\\().,'"&#@:\n\r]/g;

    function applySanitizer(inputId, pattern, maxLength) {
        const input = document.getElementById(inputId);
        if (!input) return;

        // Create character counter for fields with max length
        let counter = null;
        if (maxLength) {
            counter = document.createElement('div');
            counter.className = 'char-counter';
            counter.style.cssText = 'font-size:0.73rem;text-align:right;margin-top:4px;color:var(--text-secondary);transition:color .2s;';
            input.parentNode.insertBefore(counter, input.nextSibling);
        }

        function updateCounter() {
            if (!counter) return;
            const len = input.value.length;
            const remaining = maxLength - len;
            counter.textContent = `${len} / ${maxLength} karakter`;
            counter.style.color = remaining <= 10
                ? (remaining <= 0 ? '#f87171' : '#fbbf24')
                : 'var(--text-secondary)';
        }

        input.addEventListener('input', () => {
            const cleaned = input.value.replace(pattern, '');
            if (cleaned !== input.value) {
                const pos = input.selectionStart - (input.value.length - cleaned.length);
                input.value = cleaned;
                input.setSelectionRange(pos, pos);
            }
            if (maxLength && input.value.length > maxLength) {
                input.value = input.value.substring(0, maxLength);
            }
            updateCounter();
        });

        input.addEventListener('paste', () => {
            setTimeout(() => {
                let cleaned = input.value.replace(pattern, '');
                if (maxLength && cleaned.length > maxLength) {
                    cleaned = cleaned.substring(0, maxLength);
                }
                input.value = cleaned;
                updateCounter();
            }, 0);
        });

        if (maxLength) {
            updateCounter();
        }
    }

    // Apply to Add Modal
    applySanitizer('nip', DIGITS_ONLY_PATTERN, 24);
    applySanitizer('nama_lengkap', CHARS_PATTERN, 170);
    applySanitizer('email', EMAIL_ALLOWED_PATTERN, 254);
    applySanitizer('no_telepon', DIGITS_ONLY_PATTERN, 15);
    applySanitizer('alamat', ADDRESS_ALLOWED_PATTERN, 224);
    applySanitizer('nama_darurat_1', CHARS_PATTERN, 170);
    applySanitizer('no_darurat_1', DIGITS_ONLY_PATTERN, 15);
    applySanitizer('nama_darurat_2', CHARS_PATTERN, 170);
    applySanitizer('no_darurat_2', DIGITS_ONLY_PATTERN, 15);
    applySanitizer('instansi', CHARS_PATTERN, 170);
    applySanitizer('jabatan', CHARS_PATTERN, 170);

    // Apply to Edit Modal
    applySanitizer('edit_nip', DIGITS_ONLY_PATTERN, 24);
    applySanitizer('edit_nama_lengkap', CHARS_PATTERN, 170);
    applySanitizer('edit_email', EMAIL_ALLOWED_PATTERN, 254);
    applySanitizer('edit_no_telepon', DIGITS_ONLY_PATTERN, 15);
    applySanitizer('edit_alamat', ADDRESS_ALLOWED_PATTERN, 224);
    applySanitizer('edit_nama_darurat_1', CHARS_PATTERN, 170);
    applySanitizer('edit_no_darurat_1', DIGITS_ONLY_PATTERN, 15);
    applySanitizer('edit_nama_darurat_2', CHARS_PATTERN, 170);
    applySanitizer('edit_no_darurat_2', DIGITS_ONLY_PATTERN, 15);
    applySanitizer('edit_instansi', CHARS_PATTERN, 170);
    applySanitizer('edit_jabatan', CHARS_PATTERN, 170);

    function showErrorAlert(title, message) {
        if (window.Swal) {
            window.Swal.fire({
                background: document.documentElement.getAttribute('data-theme') === 'light' ? '#ffffff' : '#1e293b',
                color: document.documentElement.getAttribute('data-theme') === 'light' ? '#0f172a' : '#f8fafc',
                confirmButtonColor: '#2e4085',
                icon: 'warning',
                title: title,
                text: message,
                confirmButtonText: 'Mengerti'
            });
        } else {
            alert(title + ': ' + message);
        }
    }

    function validatePesertaForm(form, isEdit) {
        const prefix = isEdit ? 'edit_' : '';
        const nip = document.getElementById(prefix + 'nip')?.value || '';
        const email = document.getElementById(prefix + 'email')?.value || '';
        const noTelepon = document.getElementById(prefix + 'no_telepon')?.value || '';
        const noDarurat1 = document.getElementById(prefix + 'no_darurat_1')?.value || '';
        const namaDarurat1 = document.getElementById(prefix + 'nama_darurat_1')?.value || '';
        const hubunganDarurat1 = document.getElementById(prefix + 'hubungan_darurat_1')?.value || '';

        const namaDarurat2 = document.getElementById(prefix + 'nama_darurat_2')?.value || '';
        const noDarurat2 = document.getElementById(prefix + 'no_darurat_2')?.value || '';
        const hubunganDarurat2 = document.getElementById(prefix + 'hubungan_darurat_2')?.value || '';

        // NIP digits validation
        if (!/^[0-9]+$/.test(nip)) {
            showErrorAlert('NIP Tidak Valid', 'NIP hanya boleh diisi angka tanpa spasi.');
            return false;
        }

        // Email validation (must contain @ and .)
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showErrorAlert('Format Email Tidak Valid', 'Format email tidak valid (harus mengandung @ dan domain yang memiliki titik).');
            return false;
        }

        // No telepon validation
        if (!/^[0-9]+$/.test(noTelepon)) {
            showErrorAlert('No Telepon Tidak Valid', 'No telepon hanya boleh diisi angka.');
            return false;
        }

        // No darurat validation
        if (noDarurat1 === noTelepon) {
            showErrorAlert('Kontak Darurat Bentrok', 'No Darurat 1 tidak boleh sama dengan No Telepon peserta.');
            return false;
        }

        // Conditional validation for Emergency Contact 2:
        // If any of the three fields is filled, then all three are required
        const hasNama2 = !!namaDarurat2.trim();
        const hasNo2 = !!noDarurat2.trim();
        const hasHubungan2 = !!hubunganDarurat2.trim();

        if (hasNama2 || hasNo2 || hasHubungan2) {
            if (!hasNama2 || !hasNo2 || !hasHubungan2) {
                showErrorAlert('Data Belum Lengkap', 'Jika Kontak Darurat 2 diisi, maka Nama, Nomor Telepon, dan Hubungan wajib diisi semuanya.');
                return false;
            }
            if (noDarurat2 === noTelepon) {
                showErrorAlert('Kontak Darurat Bentrok', 'No Darurat 2 tidak boleh sama dengan No Telepon peserta.');
                return false;
            }
        }

        return true;
    }

    const addForm = addPesertaModal?.querySelector('form');
    addForm?.addEventListener('submit', (event) => {
        if (!validatePesertaForm(addForm, false)) {
            event.preventDefault();
        }
    });

    const editPesertaForm = document.getElementById('edit-peserta-form');
    editPesertaForm?.addEventListener('submit', (event) => {
        if (!validatePesertaForm(editPesertaForm, true)) {
            event.preventDefault();
        }
    });


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
            // Helper function to format WhatsApp phone number
            const formatWa = (phone) => {
                if (!phone) return '';
                let clean = phone.replace(/[^0-9]/g, '');
                if (clean.startsWith('0')) {
                    clean = '62' + clean.slice(1);
                } else if (!clean.startsWith('62')) {
                    clean = '62' + clean;
                }
                return clean;
            };

            const isEmptyVal = (val) => !val || val.trim() === '' || val.trim() === '-';

            setText('detail-nip', button.dataset.nip);
            setText('detail-nama', button.dataset.nama);
            setText('detail-email', button.dataset.email);
            setText('detail-alamat', button.dataset.alamat);
            setText('detail-jabatan', button.dataset.jabatan);
            setText('detail-instansi', button.dataset.instansi);
            setText('detail-pembimbing', button.dataset.pembimbing);
            setText('detail-status', button.dataset.status);

            // No Telepon
            const phoneVal = button.dataset.telepon || '';
            const detailTelepon = document.getElementById('detail-telepon');
            const waLinkTelepon = document.getElementById('wa-link-telepon');
            if (detailTelepon) detailTelepon.textContent = phoneVal || '-';
            if (waLinkTelepon) {
                if (!isEmptyVal(phoneVal)) {
                    waLinkTelepon.href = `https://api.whatsapp.com/send/?phone=${formatWa(phoneVal)}`;
                    waLinkTelepon.style.display = 'inline-flex';
                } else {
                    waLinkTelepon.style.display = 'none';
                }
            }

            // Kontak Darurat 1
            const namaDar1 = button.dataset.nama_darurat_1 || '';
            const hubDar1 = button.dataset.hubungan_darurat_1 || '';
            const noDar1 = button.dataset.no_darurat_1 || '';

            const detailNamaDar1 = document.getElementById('detail-nama-darurat-1');
            const detailHubDar1 = document.getElementById('detail-hubungan-darurat-1');
            const detailNoDar1 = document.getElementById('detail-no-darurat-1');
            const waLinkDar1 = document.getElementById('wa-link-darurat-1');

            if (detailNamaDar1) detailNamaDar1.textContent = isEmptyVal(namaDar1) ? 'Nama tidak diisi' : namaDar1;
            if (detailHubDar1) detailHubDar1.textContent = isEmptyVal(hubDar1) ? '-' : hubDar1;
            if (detailNoDar1) detailNoDar1.textContent = isEmptyVal(noDar1) ? '-' : noDar1;
            if (waLinkDar1) {
                if (!isEmptyVal(noDar1)) {
                    waLinkDar1.href = `https://api.whatsapp.com/send/?phone=${formatWa(noDar1)}`;
                    waLinkDar1.style.display = 'inline-flex';
                } else {
                    waLinkDar1.style.display = 'none';
                }
            }

            // Kontak Darurat 2
            const namaDar2 = button.dataset.nama_darurat_2 || '';
            const hubDar2 = button.dataset.hubungan_darurat_2 || '';
            const noDar2 = button.dataset.no_darurat_2 || '';

            const groupDar2 = document.getElementById('detail-group-darurat-2');
            const detailNamaDar2 = document.getElementById('detail-nama-darurat-2');
            const detailHubDar2 = document.getElementById('detail-hubungan-darurat-2');
            const detailNoDar2 = document.getElementById('detail-no-darurat-2');
            const waLinkDar2 = document.getElementById('wa-link-darurat-2');

            if (!isEmptyVal(namaDar2) || !isEmptyVal(hubDar2) || !isEmptyVal(noDar2)) {
                if (groupDar2) groupDar2.style.display = 'block';
                if (detailNamaDar2) detailNamaDar2.textContent = isEmptyVal(namaDar2) ? 'Nama tidak diisi' : namaDar2;
                if (detailHubDar2) detailHubDar2.textContent = isEmptyVal(hubDar2) ? '-' : hubDar2;
                if (detailNoDar2) detailNoDar2.textContent = isEmptyVal(noDar2) ? '-' : noDar2;
                if (waLinkDar2) {
                    if (!isEmptyVal(noDar2)) {
                        waLinkDar2.href = `https://api.whatsapp.com/send/?phone=${formatWa(noDar2)}`;
                        waLinkDar2.style.display = 'inline-flex';
                    } else {
                        waLinkDar2.style.display = 'none';
                    }
                }
            } else {
                if (groupDar2) groupDar2.style.display = 'none';
            }

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
            setValue('edit_nama_darurat_1', button.dataset.nama_darurat_1);
            setValue('edit_no_darurat_1', button.dataset.no_darurat_1);
            setValue('edit_hubungan_darurat_1', button.dataset.hubungan_darurat_1);
            setValue('edit_nama_darurat_2', button.dataset.nama_darurat_2);
            setValue('edit_no_darurat_2', button.dataset.no_darurat_2);
            setValue('edit_hubungan_darurat_2', button.dataset.hubungan_darurat_2);
            setValue('edit_instansi', button.dataset.instansi);
            setValue('edit_jabatan', button.dataset.jabatan);
            setValue('edit_pembimbing_id', button.dataset.pembimbingId);
            setValue('edit_status_aktif', button.dataset.status);

            // Update char counters
            document.getElementById('edit_nip')?.dispatchEvent(new Event('input'));
            document.getElementById('edit_nama_lengkap')?.dispatchEvent(new Event('input'));
            document.getElementById('edit_email')?.dispatchEvent(new Event('input'));
            document.getElementById('edit_no_telepon')?.dispatchEvent(new Event('input'));
            document.getElementById('edit_alamat')?.dispatchEvent(new Event('input'));
            document.getElementById('edit_nama_darurat_1')?.dispatchEvent(new Event('input'));
            document.getElementById('edit_no_darurat_1')?.dispatchEvent(new Event('input'));
            document.getElementById('edit_nama_darurat_2')?.dispatchEvent(new Event('input'));
            document.getElementById('edit_no_darurat_2')?.dispatchEvent(new Event('input'));
            document.getElementById('edit_instansi')?.dispatchEvent(new Event('input'));
            document.getElementById('edit_jabatan')?.dispatchEvent(new Event('input'));

            toggleModal(editPesertaModal, true);
        });
    });

    document.querySelectorAll('[data-action="reset-password-peserta"]').forEach((button) => {
        button.addEventListener('click', () => {
            const actionTemplate = resetPasswordPesertaForm?.dataset.actionTemplate || '';
            if (resetPasswordPesertaForm) {
                resetPasswordPesertaForm.action = actionTemplate.replace('__ID__', button.dataset.id);
                resetPasswordPesertaForm.reset();
                resetPasswordPesertaForm.dataset.nip = button.dataset.nip || '';
                resetPasswordPesertaForm.dataset.name = button.dataset.name || '';
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

    // ===== Password Generator Action =====
    const nipInput = document.getElementById('nip');
    const nameInput = document.getElementById('nama_lengkap');
    const passwordInput = document.getElementById('password');
    const btnGeneratePassword = document.getElementById('btn-generate-password');

    function generateRandomPassword() {
        const nipVal = (nipInput ? nipInput.value.trim() : '').replace(/[^0-9]/g, '');
        const nameVal = (nameInput ? nameInput.value.trim() : '').replace(/[^a-zA-Z]/g, '');
        
        // 1. Determine target length L (8, 9, or 10)
        const L = Math.floor(Math.random() * 3) + 8;
        
        // 2. Select uppercase char
        const uppers = nameVal.replace(/[^A-Z]/g, '');
        const charUpper = uppers.length > 0 ? uppers[Math.floor(Math.random() * uppers.length)] : String.fromCharCode(65 + Math.floor(Math.random() * 26));
        
        // 3. Select lowercase char
        const lowers = nameVal.replace(/[^a-z]/g, '');
        const charLower = lowers.length > 0 ? lowers[Math.floor(Math.random() * lowers.length)] : String.fromCharCode(97 + Math.floor(Math.random() * 26));
        
        // 4. Select digit
        const charDigit = nipVal.length > 0 ? nipVal[Math.floor(Math.random() * nipVal.length)] : String.fromCharCode(48 + Math.floor(Math.random() * 10));
        
        // 5. Underscore
        const charUnderscore = '_';
        
        // 6. Build initial array
        let pwdArr = [charUpper, charLower, charDigit, charUnderscore];
        
        // 7. Pool of characters to draw from (Name, NIP, and basic alphanumeric pool)
        let pool = nameVal + nipVal + 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
        
        while (pwdArr.length < L) {
            const randChar = pool[Math.floor(Math.random() * pool.length)];
            pwdArr.push(randChar);
        }
        
        // 8. Shuffle
        for (let i = pwdArr.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [pwdArr[i], pwdArr[j]] = [pwdArr[j], pwdArr[i]];
        }
        
        return pwdArr.join('');
    }

    btnGeneratePassword?.addEventListener('click', () => {
        if (!passwordInput) return;
        
        // Generate and set password
        const pwd = generateRandomPassword();
        passwordInput.value = pwd;
        
        // Switch to text view so user can read the password
        passwordInput.type = 'text';
        if (toggleAddPesertaPassword) {
            toggleAddPesertaPassword.textContent = 'Sembunyikan';
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

    // Make sure password input is required
    if (passwordInput) {
        passwordInput.required = true;
    }

    // ===== Reset Password Generator Action =====
    const btnGenerateResetPassword = document.getElementById('btn-generate-reset-password');
    const resetPasswordInput = document.getElementById('reset_password');
    const resetPasswordConfirmInput = document.getElementById('reset_password_confirmation');
    const toggleResetPasswordBtn = document.getElementById('toggle-reset-peserta-password');
    const resetPasswordFormEl = document.getElementById('reset-password-peserta-form');

    btnGenerateResetPassword?.addEventListener('click', () => {
        if (!resetPasswordInput || !resetPasswordConfirmInput) return;
        
        const nipVal = (resetPasswordFormEl?.dataset.nip || '').replace(/[^0-9]/g, '');
        const nameVal = (resetPasswordFormEl?.dataset.name || '').replace(/[^a-zA-Z]/g, '');
        
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
        
        const pwd = pwdArr.join('');
        resetPasswordInput.value = pwd;
        resetPasswordConfirmInput.value = pwd;
        
        resetPasswordInput.type = 'text';
        resetPasswordConfirmInput.type = 'text';
        if (toggleResetPasswordBtn) {
            toggleResetPasswordBtn.textContent = 'Sembunyikan';
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

    // ===== Dynamic Asterisks for Emergency Contact 2 =====
    function updateEmergency2Asterisks(prefix = '') {
        const nama = document.getElementById(prefix + 'nama_darurat_2');
        const no = document.getElementById(prefix + 'no_darurat_2');
        const hubungan = document.getElementById(prefix + 'hubungan_darurat_2');
        
        const hasVal = (nama?.value || '').trim() || (no?.value || '').trim() || (hubungan?.value || '').trim();
        
        const asterisks = [
            nama?.closest('.form-group')?.querySelector('.required-asterisk'),
            no?.closest('.form-group')?.querySelector('.required-asterisk'),
            hubungan?.closest('.form-group')?.querySelector('.required-asterisk')
        ];
        
        asterisks.forEach(asterisk => {
            if (asterisk) {
                asterisk.style.display = hasVal ? 'inline' : 'none';
            }
        });
    }

    ['input', 'change'].forEach(eventType => {
        ['nama_darurat_2', 'no_darurat_2', 'hubungan_darurat_2'].forEach(id => {
            const addEl = document.getElementById(id);
            const editEl = document.getElementById('edit_' + id);
            
            addEl?.addEventListener(eventType, () => updateEmergency2Asterisks(''));
            editEl?.addEventListener(eventType, () => updateEmergency2Asterisks('edit_'));
        });
    });

    // Run once on edit modal initialization when clicked
    document.querySelectorAll('[data-action="edit-peserta"]').forEach((button) => {
        button.addEventListener('click', () => {
            setTimeout(() => {
                updateEmergency2Asterisks('edit_');
            }, 50);
        });
    });
});
