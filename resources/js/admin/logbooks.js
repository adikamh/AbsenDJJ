document.addEventListener('DOMContentLoaded', function () {
    // Logbook Detail Modal
    const detailModal = document.getElementById('logbook-detail-modal');
    const closeDetailBtn = document.getElementById('close-modal-btn');
    const triggerBtns = document.querySelectorAll('.logbook-detail-trigger');

    const modalInternInfo = document.getElementById('modal-intern-info');
    const modalDate = document.getElementById('modal-date');
    const modalKegiatan = document.getElementById('modal-kegiatan');
    const modalDescription = document.getElementById('modal-description');
    const modalTags = document.getElementById('modal-tags');
    const modalStatus = document.getElementById('modal-status');
    const modalComment = document.getElementById('modal-comment');
    
    const modalActions = document.getElementById('modal-actions');
    const modalApproveBtn = document.getElementById('modal-approve-btn');
    const modalRejectBtn = document.getElementById('modal-reject-btn');

    // Global Action Confirmation Modal
    const confirmModal = document.getElementById('action-confirm-modal');
    const confirmTitle = document.getElementById('confirm-modal-title');
    const confirmText = document.getElementById('confirm-modal-text');
    const confirmForm = document.getElementById('confirm-action-form');
    const confirmCatatan = document.getElementById('confirm-catatan');
    const confirmCommentLabel = document.getElementById('confirm-comment-label');
    const confirmCancelBtn = document.getElementById('confirm-modal-cancel');
    const confirmSubmitBtn = document.getElementById('confirm-modal-submit');

    let currentActiveLogbookId = null;

    // Open Detail Modal
    triggerBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const instansi = this.getAttribute('data-instansi');
            const date = this.getAttribute('data-date');
            const kegiatan = this.getAttribute('data-kegiatan');
            const description = this.getAttribute('data-description');
            const tags = this.getAttribute('data-tags');
            const status = this.getAttribute('data-status');
            const comment = this.getAttribute('data-comment');

            currentActiveLogbookId = id;

            // Populate modal
            modalInternInfo.textContent = `${name} (${instansi})`;
            modalDate.textContent = date;
            modalKegiatan.textContent = kegiatan;
            modalDescription.textContent = description;
            modalComment.textContent = comment || '-';

            // Set tags
            modalTags.innerHTML = '';
            if (tags && tags.trim().length > 0) {
                tags.split(',').forEach(tag => {
                    const span = document.createElement('span');
                    span.className = 'badge badge-info';
                    span.style.fontSize = '0.7rem';
                    span.style.padding = '2px 6px';
                    span.textContent = `#${tag.trim()}`;
                    modalTags.appendChild(span);
                });
            } else {
                modalTags.textContent = '-';
            }

            // Set status badge class
            modalStatus.textContent = status;
            modalStatus.className = 'badge';
            if (status === 'Approved') {
                modalStatus.classList.add('badge-success');
            } else if (status === 'Rejected') {
                modalStatus.classList.add('badge-danger');
            } else {
                modalStatus.classList.add('badge-warning');
            }

            // Show/hide approval actions based on status
            if (status === 'Pending') {
                modalActions.style.display = 'flex';
            } else {
                modalActions.style.display = 'none';
            }

            detailModal.style.display = 'flex';
        });
    });

    // Close Detail Modal
    function closeDetailModal() {
        detailModal.style.display = 'none';
    }
    closeDetailBtn.addEventListener('click', closeDetailModal);

    // Helper to trigger confirmation modal
    function showConfirmationModal(actionType, actionUrl) {
        confirmForm.action = actionUrl;
        confirmCatatan.value = '';

        if (actionType === 'approve') {
            confirmTitle.textContent = 'Setujui Logbook';
            confirmTitle.style.color = '#10b981';
            confirmText.textContent = 'Apakah Anda yakin ingin menyetujui logbook kegiatan ini? Anda dapat menambahkan catatan opsional di bawah.';
            confirmCommentLabel.textContent = 'Catatan Pembimbing (Opsional)';
            confirmCatatan.placeholder = 'Tulis catatan persetujuan...';
            confirmCatatan.required = false;
            confirmSubmitBtn.textContent = 'Setujui';
            confirmSubmitBtn.style.background = '#10b981';
        } else {
            confirmTitle.textContent = 'Tolak Logbook';
            confirmTitle.style.color = '#ef4444';
            confirmText.textContent = 'Apakah Anda yakin ingin menolak logbook kegiatan ini? Silakan berikan alasan penolakan pada input catatan di bawah.';
            confirmCommentLabel.textContent = 'Catatan Pembimbing (Wajib)';
            confirmCatatan.placeholder = 'Tulis alasan penolakan...';
            confirmCatatan.required = true;
            confirmSubmitBtn.textContent = 'Tolak & Kirim';
            confirmSubmitBtn.style.background = '#ef4444';
        }

        confirmModal.style.display = 'flex';
    }

    // Close Confirmation Modal
    function closeConfirmationModal() {
        confirmModal.style.display = 'none';
    }
    confirmCancelBtn.addEventListener('click', closeConfirmationModal);

    // Trigger approve/reject from table buttons
    document.querySelectorAll('.logbook-approve-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const actionUrl = this.getAttribute('data-action-url');
            showConfirmationModal('approve', actionUrl);
        });
    });

    document.querySelectorAll('.logbook-reject-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const actionUrl = this.getAttribute('data-action-url');
            showConfirmationModal('reject', actionUrl);
        });
    });

    // Trigger approve/reject from inside Detail Modal
    modalApproveBtn.addEventListener('click', function () {
        if (currentActiveLogbookId) {
            closeDetailModal();
            showConfirmationModal('approve', `/admin/logbook/${currentActiveLogbookId}/approve`);
        }
    });

    modalRejectBtn.addEventListener('click', function () {
        if (currentActiveLogbookId) {
            closeDetailModal();
            showConfirmationModal('reject', `/admin/logbook/${currentActiveLogbookId}/reject`);
        }
    });

    // Click outside to close modals
    window.addEventListener('click', function (e) {
        if (e.target === detailModal) {
            closeDetailModal();
        }
        if (e.target === confirmModal) {
            closeConfirmationModal();
        }
    });
});
