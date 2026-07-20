// Top-level Global function bindings (defined immediately when script is evaluated)
window.showImageModal = function(src, title) {
    const selfieModal = document.getElementById('selfie-modal');
    const modalSelfieImg = document.getElementById('modal-selfie-img');
    const modalSelfieTitle = document.getElementById('modal-selfie-title');
    if (!selfieModal || !modalSelfieImg || !modalSelfieTitle) return;
    
    modalSelfieImg.src = src;
    modalSelfieTitle.textContent = title;
    selfieModal.classList.add('is-open');
};

window.showSelfiePopup = function(fotoMasuk, fotoPulang, formattedDate) {
    const photosModal = document.getElementById('daily-photos-modal');
    const imgMasuk = document.getElementById('modal-foto-masuk');
    const imgPulang = document.getElementById('modal-foto-pulang');
    const containerMasuk = document.getElementById('modal-masuk-container');
    const containerPulang = document.getElementById('modal-pulang-container');
    const modalPhotosTitle = document.getElementById('modal-photos-title');
    
    if (!photosModal || !modalPhotosTitle || !imgMasuk || !imgPulang) return;

    modalPhotosTitle.textContent = `Foto Absensi - ${formattedDate}`;

    // Set check-in photo
    if (fotoMasuk) {
        imgMasuk.src = fotoMasuk;
        if (containerMasuk) containerMasuk.style.display = 'flex';
    } else {
        imgMasuk.src = '';
        if (containerMasuk) containerMasuk.style.display = 'none';
    }

    // Set check-out photo
    if (fotoPulang) {
        imgPulang.src = fotoPulang;
        if (containerPulang) containerPulang.style.display = 'flex';
    } else {
        imgPulang.src = '';
        if (containerPulang) containerPulang.style.display = 'none';
    }

    photosModal.classList.add('is-open');
};

window.openActivityDetail = function(dateStr, formattedDate, attendanceStatus, jamMasuk, jamPulang, fotoMasuk, fotoPulang, logbooks, isSupervisor) {
    const activityModal = document.getElementById('activity-detail-modal');
    if (!activityModal) return;
    
    document.getElementById('modal-activity-title').textContent = 'Detail Aktivitas - ' + formattedDate;
    
    const statusEl = document.getElementById('modal-attendance-status');
    statusEl.textContent = attendanceStatus;
    
    // Coloring status
    statusEl.className = '';
    if (attendanceStatus === 'Hadir') {
        statusEl.style.color = '#34d399';
    } else if (attendanceStatus === 'Terlambat') {
        statusEl.style.color = '#fbbf24';
    } else if (['Izin', 'Sakit'].includes(attendanceStatus)) {
        statusEl.style.color = '#60a5fa';
    } else {
        statusEl.style.color = '#f87171';
    }
    
    document.getElementById('modal-attendance-masuk-time').textContent = jamMasuk;
    document.getElementById('modal-attendance-pulang-time').textContent = jamPulang;
    
    // Photos
    const imgM = document.getElementById('modal-selfie-masuk-img');
    const wrapM = document.getElementById('modal-selfie-masuk-wrap');
    if (fotoMasuk) {
        imgM.src = fotoMasuk;
        if (wrapM) wrapM.style.display = 'block';
    } else {
        if (wrapM) wrapM.style.display = 'none';
    }
    
    const imgP = document.getElementById('modal-selfie-pulang-img');
    const wrapP = document.getElementById('modal-selfie-pulang-wrap');
    if (fotoPulang) {
        imgP.src = fotoPulang;
        if (wrapP) wrapP.style.display = 'block';
    } else {
        if (wrapP) wrapP.style.display = 'none';
    }
    
    // Logbooks
    const logbookList = document.getElementById('modal-logbook-list');
    logbookList.innerHTML = '';
    
    if (logbooks && logbooks.length > 0) {
        logbooks.forEach(lb => {
            const lbCard = document.createElement('div');
            lbCard.style.background = 'rgba(255,255,255,0.02)';
            lbCard.style.border = '1px solid var(--glass-border)';
            lbCard.style.borderRadius = '8px';
            lbCard.style.padding = '12px';
            lbCard.style.marginBottom = '12px';
            
            let badgeClass = 'badge-warning';
            let badgeText = 'Pending';
            if (lb.status_approval === 'Approved') {
                badgeClass = 'badge-success';
                badgeText = 'Disetujui';
            } else if (lb.status_approval === 'Rejected') {
                badgeClass = 'badge-danger';
                badgeText = 'Ditolak';
            } else if (lb.status_approval === 'Draft') {
                badgeClass = 'draft-badge';
                badgeText = 'Draft';
            }
            
            let tagsHtml = '';
            if (lb.tags) {
                const tagsList = lb.tags.split(',');
                tagsList.forEach(t => {
                    tagsHtml += `<span class="badge" style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border); font-size: 0.75rem; margin-right: 4px;">${t.trim()}</span>`;
                });
            }
            
            let actionButtonsHtml = '';
            if (isSupervisor && lb.status_approval === 'Pending') {
                actionButtonsHtml = `
                    <div style="display: flex; gap: 8px; margin-top: 12px; justify-content: flex-end;">
                        <button type="button" class="btn-camera btn-camera-danger" onclick="handleLogbookAction(${lb.id}, 'reject')" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 6px;">Tolak</button>
                        <button type="button" class="btn-camera btn-camera-success" onclick="handleLogbookAction(${lb.id}, 'approve')" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 6px; background: #10b981; border: none; color: #fff;">Setujui</button>
                    </div>
                `;
            }
            
            let catatanHtml = '';
            if (lb.catatan_pembimbing) {
                catatanHtml = `
                    <div style="margin-top: 8px; font-size: 0.8rem; font-style: italic; color: #9ca3af; border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 6px;">
                        Catatan Pembimbing: ${lb.catatan_pembimbing}
                    </div>
                `;
            }
            
            lbCard.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                    <strong style="color: var(--accent-primary); font-size: 0.95rem;">${lb.kegiatan}</strong>
                    <span class="badge ${badgeClass}">${badgeText}</span>
                </div>
                <p style="margin: 0 0 8px 0; font-size: 0.85rem; line-height: 1.5; color: var(--text-secondary); white-space: pre-wrap;">${lb.deskripsi}</p>
                <div style="margin-bottom: 8px;">${tagsHtml}</div>
                ${catatanHtml}
                ${actionButtonsHtml}
            `;
            logbookList.appendChild(lbCard);
        });
        document.getElementById('modal-logbook-section').style.display = 'block';
    } else {
        document.getElementById('modal-logbook-section').style.display = 'none';
    }
    
    activityModal.classList.add('is-open');
};

window.closeActivityDetailModal = function() {
    const activityModal = document.getElementById('activity-detail-modal');
    activityModal?.classList.remove('is-open');
};

window.handleLogbookAction = async function (logbookId, action) {
    if (!window.Swal) {
        alert('SweetAlert2 tidak aktif.');
        return;
    }
    
    const actionText = action === 'approve' ? 'menyetujui' : 'menolak';
    const confirmButtonColor = action === 'approve' ? '#10b981' : '#ef4444';
    
    const { value: catatan } = await window.Swal.fire({
        title: `Konfirmasi ${action === 'approve' ? 'Persetujuan' : 'Penolakan'}`,
        text: `Apakah Anda yakin ingin ${actionText} logbook ini? Tambahkan catatan (opsional):`,
        input: 'textarea',
        inputPlaceholder: 'Tulis catatan pembimbing di sini...',
        showCancelButton: true,
        confirmButtonText: action === 'approve' ? 'Setujui' : 'Tolak',
        cancelButtonText: 'Batal',
        confirmButtonColor: confirmButtonColor,
        inputAttributes: {
            'maxlength': 255
        }
    });
    
    if (catatan !== undefined) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/logbook/${logbookId}/${action}`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfInput);
        
        const catatanInput = document.createElement('input');
        catatanInput.type = 'hidden';
        catatanInput.name = 'catatan_pembimbing';
        catatanInput.value = catatan;
        form.appendChild(catatanInput);
        
        document.body.appendChild(form);
        
        window.Swal.fire({
            title: 'Memproses...',
            text: 'Sedang menyimpan status verifikasi logbook.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                window.Swal.showLoading();
            }
        });
        
        form.submit();
    }
};

// Event Listeners (registered when DOM is fully parsed)
document.addEventListener('DOMContentLoaded', () => {
    // Selfie Modal backdrop click
    const selfieModal = document.getElementById('selfie-modal');
    const closeSelfieModal = document.getElementById('close-selfie-modal');
    closeSelfieModal?.addEventListener('click', () => {
        selfieModal?.classList.remove('is-open');
    });
    selfieModal?.addEventListener('click', (e) => {
        if (e.target === selfieModal) {
            selfieModal.classList.remove('is-open');
        }
    });

    // Daily Photos Modal backdrop click
    const photosModal = document.getElementById('daily-photos-modal');
    const closePhotosModal = document.getElementById('close-photos-modal');
    closePhotosModal?.addEventListener('click', () => {
        photosModal?.classList.remove('is-open');
    });
    photosModal?.addEventListener('click', (e) => {
        if (e.target === photosModal) {
            photosModal.classList.remove('is-open');
        }
    });

    // Activity Detail Modal backdrop click
    const activityModal = document.getElementById('activity-detail-modal');
    activityModal?.addEventListener('click', (e) => {
        if (e.target === activityModal) {
            activityModal.classList.remove('is-open');
        }
    });
});
