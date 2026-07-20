document.addEventListener('DOMContentLoaded', () => {
    // ===== Modal Add Logbook =====
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

    // ===== Modal Edit Logbook =====
    const modalEditLogbook = document.getElementById('modal-edit-logbook');
    const btnCloseEditLogbook = document.getElementById('close-edit-logbook-modal');
    const btnCancelEditLogbook = document.getElementById('cancel-edit-logbook-modal');
    const editForm = document.getElementById('edit-logbook-form');
    const editKegiatanInput = document.getElementById('edit-kegiatan');
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
            const deskripsi = button.getAttribute('data-deskripsi');

            if (editForm) {
                editForm.action = `/peserta/logbook/${id}`;
            }
            if (editKegiatanInput) {
                editKegiatanInput.value = kegiatan;
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

    // ===== Delete Confirmation =====
    document.querySelectorAll('.delete-logbook-form .btn-delete-logbook').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const form = button.closest('form');
            if (form) {
                if (window.Swal) {
                    window.Swal.fire({
                        title: 'Hapus Logbook',
                        text: 'Apakah Anda yakin ingin menghapus entri logbook ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal',
                        background: '#1e293b',
                        color: '#f8fafc'
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
            }
        });
    });
});
