document.addEventListener('DOMContentLoaded', () => {
    // Add Logbook Modal
    const modalAddLogbook = document.getElementById('modal-add-logbook');
    const btnOpenAddLogbook = document.getElementById('open-add-logbook-modal');
    const btnCloseAddLogbook = document.getElementById('close-add-logbook-modal');
    const btnCancelAddLogbook = document.getElementById('cancel-add-logbook-modal');

    const toggleAddLogbookModal = (show) => {
        if (modalAddLogbook) {
            modalAddLogbook.classList.toggle('is-open', show);
        }
    };

    btnOpenAddLogbook?.addEventListener('click', () => toggleAddLogbookModal(true));
    btnCloseAddLogbook?.addEventListener('click', () => toggleAddLogbookModal(false));
    btnCancelAddLogbook?.addEventListener('click', () => toggleAddLogbookModal(false));

    modalAddLogbook?.addEventListener('click', (e) => {
        if (e.target === modalAddLogbook) {
            toggleAddLogbookModal(false);
        }
    });

    // Edit Logbook Modal
    const modalEditLogbook = document.getElementById('modal-edit-logbook');
    const btnCloseEditLogbook = document.getElementById('close-edit-logbook-modal');
    const btnCancelEditLogbook = document.getElementById('cancel-edit-logbook-modal');
    const editLogbookForm = document.getElementById('edit-logbook-form');
    const editKegiatanInput = document.getElementById('edit-kegiatan');
    const editTagsInput = document.getElementById('edit-tags');
    const editDeskripsiInput = document.getElementById('edit-deskripsi');

    const toggleEditLogbookModal = (show) => {
        if (modalEditLogbook) {
            modalEditLogbook.classList.toggle('is-open', show);
        }
    };

    document.querySelectorAll('.open-edit-logbook-modal').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            const kegiatan = button.getAttribute('data-kegiatan');
            const tags = button.getAttribute('data-tags') || '';
            const deskripsi = button.getAttribute('data-deskripsi');

            if (editLogbookForm) {
                editLogbookForm.action = `/peserta/logbook/${id}`;
            }
            if (editKegiatanInput) {
                editKegiatanInput.value = kegiatan;
            }
            if (editTagsInput) {
                editTagsInput.value = tags;
            }
            if (editDeskripsiInput) {
                editDeskripsiInput.value = deskripsi;
            }

            toggleEditLogbookModal(true);
        });
    });

    btnCloseEditLogbook?.addEventListener('click', () => toggleEditLogbookModal(false));
    btnCancelEditLogbook?.addEventListener('click', () => toggleEditLogbookModal(false));

    modalEditLogbook?.addEventListener('click', (e) => {
        if (e.target === modalEditLogbook) {
            toggleEditLogbookModal(false);
        }
    });

    // ===== Delete Confirmation (SweetAlert2) =====
    document.querySelectorAll('.delete-logbook-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();

            const isDark = document.documentElement.getAttribute('data-theme') === 'dark'
                || document.body.classList.contains('dark-mode');

            if (window.Swal) {
                window.Swal.fire({
                    title: 'Hapus Logbook?',
                    text: 'Apakah Anda yakin ingin menghapus entri logbook ini? Tindakan ini tidak dapat dibatalkan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    background: isDark ? '#1e293b' : '#ffffff',
                    color: isDark ? '#f8fafc' : '#0f172a'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm('Apakah Anda yakin ingin menghapus entri logbook ini?')) {
                    form.submit();
                }
            }
        });
    });
});
