document.addEventListener('DOMContentLoaded', function () {
    // Global Action Confirmation Modal
    const confirmModal = document.getElementById('action-confirm-modal');
    const confirmTitle = document.getElementById('confirm-modal-title');
    const confirmText = document.getElementById('confirm-modal-text');
    const confirmForm = document.getElementById('confirm-action-form');
    const confirmCatatan = document.getElementById('confirm-catatan');
    const confirmCommentLabel = document.getElementById('confirm-comment-label');
    const confirmCancelBtn = document.getElementById('confirm-modal-cancel');
    const confirmSubmitBtn = document.getElementById('confirm-modal-submit');

    // Helper to trigger confirmation modal
    function showConfirmationModal(actionType, dataType, actionUrl) {
        confirmForm.action = actionUrl;
        confirmCatatan.value = '';

        if (actionType === 'approve') {
            confirmTitle.textContent = `Setujui ${dataType}`;
            confirmTitle.style.color = '#10b981';
            confirmText.textContent = `Apakah Anda yakin ingin menyetujui ${dataType.toLowerCase()} ini? Anda dapat menambahkan catatan opsional di bawah.`;
            confirmCommentLabel.textContent = 'Catatan Pembimbing (Opsional)';
            confirmCatatan.placeholder = 'Tulis catatan persetujuan...';
            confirmCatatan.required = false;
            confirmSubmitBtn.textContent = 'Setujui';
            confirmSubmitBtn.style.background = '#10b981';
        } else {
            confirmTitle.textContent = `Tolak ${dataType}`;
            confirmTitle.style.color = '#ef4444';
            confirmText.textContent = `Apakah Anda yakin ingin menolak ${dataType.toLowerCase()} ini? Silakan berikan alasan penolakan pada input catatan di bawah.`;
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

    // Trigger approve/reject from dashboard buttons
    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const dataType = this.getAttribute('data-type');
            const actionUrl = this.getAttribute('data-action-url');
            showConfirmationModal('approve', dataType, actionUrl);
        });
    });

    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const dataType = this.getAttribute('data-type');
            const actionUrl = this.getAttribute('data-action-url');
            showConfirmationModal('reject', dataType, actionUrl);
        });
    });

    // Click outside to close modal
    window.addEventListener('click', function (e) {
        if (e.target === confirmModal) {
            closeConfirmationModal();
        }
    });
});
