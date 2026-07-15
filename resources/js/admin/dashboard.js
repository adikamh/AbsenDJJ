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

    // ===== Chart Rendering =====
    const todayChartEl = document.getElementById('todayAttendanceChart');
    if (todayChartEl && typeof Chart !== 'undefined') {
        const hadir = parseInt(todayChartEl.getAttribute('data-hadir') || '0', 10);
        const terlambat = parseInt(todayChartEl.getAttribute('data-terlambat') || '0', 10);
        const izin = parseInt(todayChartEl.getAttribute('data-izin') || '0', 10);
        const alfa = parseInt(todayChartEl.getAttribute('data-alfa') || '0', 10);
        const totalActivity = hadir + terlambat + izin + alfa;
        const hasData = totalActivity > 0;

        new Chart(todayChartEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Tepat Waktu', 'Terlambat', 'Izin / Sakit', 'Belum Absen (Alfa)'],
                datasets: [{
                    data: hasData 
                        ? [hadir, terlambat, izin, alfa]
                        : [0, 0, 0, 1],
                    backgroundColor: hasData
                        ? ['#10b981', '#fbbf24', '#3b82f6', '#ef4444']
                        : ['rgba(255,255,255,0.05)'],
                    borderWidth: 1,
                    borderColor: 'rgba(255,255,255,0.1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#a0aec0',
                            font: {
                                family: 'Outfit, sans-serif',
                                size: 11
                            },
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (!hasData) return ' Tidak ada aktivitas bimbingan hari ini';
                                return ` ${context.label}: ${context.raw} Orang`;
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }

    const complianceChartEl = document.getElementById('complianceChart');
    if (complianceChartEl && typeof Chart !== 'undefined') {
        try {
            const rawData = complianceChartEl.getAttribute('data-compliance');
            const complianceData = JSON.parse(rawData || '[]');
            const labels = complianceData.map(item => item.name);
            const rates = complianceData.map(item => item.rate);

            new Chart(complianceChartEl.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels.length > 0 ? labels : ['Belum ada data'],
                    datasets: [{
                        label: 'Persentase Kehadiran (%)',
                        data: rates.length > 0 ? rates : [0],
                        backgroundColor: 'rgba(124, 58, 237, 0.45)',
                        borderColor: '#7c3aed',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        barPercentage: 0.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ` Kepatuhan: ${context.raw}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            max: 100,
                            ticks: {
                                color: '#a0aec0',
                                font: {
                                    family: 'Outfit, sans-serif'
                                }
                            },
                            grid: {
                                color: 'rgba(255,255,255,0.05)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#a0aec0',
                                font: {
                                    family: 'Outfit, sans-serif',
                                    size: 10
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        } catch (e) {
            console.error('Failed to parse compliance chart data:', e);
        }
    }
});
