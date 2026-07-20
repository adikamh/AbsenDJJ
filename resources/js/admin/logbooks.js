document.addEventListener('DOMContentLoaded', function () {
    // Logbook Detail Modal
    const detailModal = document.getElementById('logbook-detail-modal');
    const closeDetailBtn = document.getElementById('close-modal-btn');

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
    const modalRevisionBtn = document.getElementById('modal-revision-btn');

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

    // Event delegation for opening Detail Modal
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.logbook-detail-trigger');
        if (btn) {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            const instansi = btn.getAttribute('data-instansi');
            const date = btn.getAttribute('data-date');
            const kegiatan = btn.getAttribute('data-kegiatan');
            const description = btn.getAttribute('data-description');
            const tags = btn.getAttribute('data-tags');
            const status = btn.getAttribute('data-status');
            const comment = btn.getAttribute('data-comment');

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
            } else if (status === 'Revisi') {
                modalStatus.style.backgroundColor = '#fbbf24';
                modalStatus.style.borderColor = '#fbbf24';
                modalStatus.style.color = '#1e1b4b';
            } else {
                modalStatus.classList.add('badge-warning');
            }

            // Configure actions visibility based on status (hide Approve & Reject if in Revisi)
            if (status === 'Revisi') {
                modalActions.style.display = 'none';
            } else {
                modalActions.style.display = 'flex';
                
                if (status === 'Approved') {
                    modalApproveBtn.style.display = 'none';
                    modalRejectBtn.style.display = 'block';
                    modalRevisionBtn.style.display = 'block';
                } else if (status === 'Rejected') {
                    modalApproveBtn.style.display = 'block';
                    modalRejectBtn.style.display = 'none';
                    modalRevisionBtn.style.display = 'block';
                } else { // Pending
                    modalApproveBtn.style.display = 'block';
                    modalRejectBtn.style.display = 'block';
                    modalRevisionBtn.style.display = 'block';
                }
            }

            detailModal.style.display = 'flex';
        }
    });

    // Close Detail Modal
    function closeDetailModal() {
        detailModal.style.display = 'none';
    }
    closeDetailBtn?.addEventListener('click', closeDetailModal);

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
            confirmSubmitBtn.style.color = '#fff';
        } else if (actionType === 'revision') {
            confirmTitle.textContent = 'Minta Revisi Logbook';
            confirmTitle.style.color = '#fbbf24';
            confirmText.textContent = 'Apakah Anda yakin ingin meminta peserta merevisi logbook ini? Silakan tulis instruksi atau catatan perbaikan pada input di bawah.';
            confirmCommentLabel.textContent = 'Catatan/Instruksi Perbaikan (Wajib)';
            confirmCatatan.placeholder = 'Tulis catatan instruksi revisi...';
            confirmCatatan.required = true;
            confirmSubmitBtn.textContent = 'Kirim Permintaan Revisi';
            confirmSubmitBtn.style.background = '#fbbf24';
            confirmSubmitBtn.style.color = '#1e1b4b';
        } else {
            confirmTitle.textContent = 'Tolak Logbook';
            confirmTitle.style.color = '#ef4444';
            confirmText.textContent = 'Apakah Anda yakin ingin menolak logbook kegiatan ini? Silakan berikan alasan penolakan pada input catatan di bawah.';
            confirmCommentLabel.textContent = 'Catatan Pembimbing (Wajib)';
            confirmCatatan.placeholder = 'Tulis alasan penolakan...';
            confirmCatatan.required = true;
            confirmSubmitBtn.textContent = 'Tolak & Kirim';
            confirmSubmitBtn.style.background = '#ef4444';
            confirmSubmitBtn.style.color = '#fff';
        }

        confirmModal.style.display = 'flex';
    }

    // Close Confirmation Modal
    function closeConfirmationModal() {
        confirmModal.style.display = 'none';
    }
    confirmCancelBtn?.addEventListener('click', closeConfirmationModal);

    // Event delegation for approve/reject/revision table buttons
    document.addEventListener('click', function (e) {
        const approveBtn = e.target.closest('.logbook-approve-btn');
        if (approveBtn) {
            const actionUrl = approveBtn.getAttribute('data-action-url');
            showConfirmationModal('approve', actionUrl);
            return;
        }

        const rejectBtn = e.target.closest('.logbook-reject-btn');
        if (rejectBtn) {
            const actionUrl = rejectBtn.getAttribute('data-action-url');
            showConfirmationModal('reject', actionUrl);
            return;
        }

        const revisionBtn = e.target.closest('.logbook-revision-btn');
        if (revisionBtn) {
            const actionUrl = revisionBtn.getAttribute('data-action-url');
            showConfirmationModal('revision', actionUrl);
            return;
        }
    });

    // Trigger approve/reject/revision from inside Detail Modal
    modalApproveBtn?.addEventListener('click', function () {
        if (currentActiveLogbookId) {
            closeDetailModal();
            showConfirmationModal('approve', `/admin/logbook/${currentActiveLogbookId}/approve`);
        }
    });

    modalRejectBtn?.addEventListener('click', function () {
        if (currentActiveLogbookId) {
            closeDetailModal();
            showConfirmationModal('reject', `/admin/logbook/${currentActiveLogbookId}/reject`);
        }
    });

    modalRevisionBtn?.addEventListener('click', function () {
        if (currentActiveLogbookId) {
            closeDetailModal();
            showConfirmationModal('revision', `/admin/logbook/${currentActiveLogbookId}/revision`);
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
