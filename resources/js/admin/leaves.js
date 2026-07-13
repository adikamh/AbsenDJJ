document.addEventListener('DOMContentLoaded', function () {
    // Leave Detail Modal
    const detailModal = document.getElementById('leave-detail-modal');
    const closeDetailBtn = document.getElementById('close-detail-btn');
    const triggerBtns = document.querySelectorAll('.leave-detail-trigger');

    const modalInternInfo = document.getElementById('modal-intern-info');
    const modalStartDate = document.getElementById('modal-start-date');
    const modalEndDate = document.getElementById('modal-end-date');
    const modalJenis = document.getElementById('modal-jenis');
    const modalAlasan = document.getElementById('modal-alasan');
    const modalStatus = document.getElementById('modal-status');
    const modalComment = document.getElementById('modal-comment');
    const modalBuktiLink = document.getElementById('modal-bukti-link');
    const modalNoBukti = document.getElementById('modal-no-bukti');
    
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

    let currentActiveLeaveId = null;

    // Open Detail Modal
    triggerBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const instansi = this.getAttribute('data-instansi');
            const start = this.getAttribute('data-start');
            const end = this.getAttribute('data-end');
            const jenis = this.getAttribute('data-jenis');
            const alasan = this.getAttribute('data-alasan');
            const bukti = this.getAttribute('data-bukti');
            const status = this.getAttribute('data-status');
            const comment = this.getAttribute('data-comment');

            currentActiveLeaveId = id;

            // Populate modal
            modalInternInfo.textContent = `${name} (${instansi})`;
            modalStartDate.textContent = start;
            modalEndDate.textContent = end;
            modalAlasan.textContent = alasan;
            modalComment.textContent = comment || '-';

            // Set jenis badge
            modalJenis.textContent = jenis;
            modalJenis.className = 'badge';
            if (jenis === 'Sakit') {
                modalJenis.classList.add('badge-danger');
            } else {
                modalJenis.classList.add('badge-warning');
            }

            // Set bukti link
            if (bukti && bukti.trim().length > 0) {
                modalBuktiLink.href = bukti;
                modalBuktiLink.style.display = 'inline-block';
                modalNoBukti.style.display = 'none';
            } else {
                modalBuktiLink.style.display = 'none';
                modalNoBukti.style.display = 'inline';
            }

            // Set status badge
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
            confirmTitle.textContent = 'Setujui Permohonan';
            confirmTitle.style.color = '#10b981';
            confirmText.textContent = 'Apakah Anda yakin ingin menyetujui permohonan izin/sakit ini? Anda dapat menambahkan catatan opsional di bawah.';
            confirmCommentLabel.textContent = 'Catatan Pembimbing (Opsional)';
            confirmCatatan.placeholder = 'Tulis catatan persetujuan...';
            confirmCatatan.required = false;
            confirmSubmitBtn.textContent = 'Setujui';
            confirmSubmitBtn.style.background = '#10b981';
        } else {
            confirmTitle.textContent = 'Tolak Permohonan';
            confirmTitle.style.color = '#ef4444';
            confirmText.textContent = 'Apakah Anda yakin ingin menolak permohonan izin/sakit ini? Silakan berikan alasan penolakan pada input catatan di bawah.';
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
    document.querySelectorAll('.leave-approve-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const actionUrl = this.getAttribute('data-action-url');
            showConfirmationModal('approve', actionUrl);
        });
    });

    document.querySelectorAll('.leave-reject-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const actionUrl = this.getAttribute('data-action-url');
            showConfirmationModal('reject', actionUrl);
        });
    });

    // Trigger approve/reject from inside Detail Modal
    modalApproveBtn.addEventListener('click', function () {
        if (currentActiveLeaveId) {
            closeDetailModal();
            showConfirmationModal('approve', `/admin/leave/${currentActiveLeaveId}/approve`);
        }
    });

    modalRejectBtn.addEventListener('click', function () {
        if (currentActiveLeaveId) {
            closeDetailModal();
            showConfirmationModal('reject', `/admin/leave/${currentActiveLeaveId}/reject`);
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
