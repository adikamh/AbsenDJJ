import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Swal = Swal;

// Page-specific scripts for dashboard/layout.blade.php.

const root = document.documentElement;
const themeToggle = document.getElementById('theme-toggle');
const sidebarToggle = document.getElementById('sidebar-toggle');
const sidebar = document.getElementById('sidebar');
const cookieModal = document.getElementById('cookie-modal-backdrop');

function getSwalTheme() {
    const isLight = root.getAttribute('data-theme') === 'light';

    return {
        background: isLight ? '#ffffff' : '#1e293b',
        color: isLight ? '#0f172a' : '#f8fafc',
        confirmButtonColor: isLight ? '#2e4085' : '#ffcc33',
    };
}

function showNotification(icon, title, text) {
    if (!text) {
        return;
    }

    Swal.fire({
        ...getSwalTheme(),
        icon,
        title,
        text,
        confirmButtonText: 'Mengerti',
    });
}

function showValidationAlert(text = 'Semua field wajib diisi.') {
    showNotification('error', 'Data Belum Lengkap', text);
}

window.showValidationAlert = showValidationAlert;

window.confirmDangerAction = async function confirmDangerAction({
    title = 'Apakah Anda yakin?',
    text = 'Data akan diproses.',
    confirmButtonText = 'Ya, lanjutkan',
    cancelButtonText = 'Batal',
} = {}) {
    const result = await Swal.fire({
        ...getSwalTheme(),
        icon: 'warning',
        title,
        text,
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
    });

    return result.isConfirmed;
};

function applyTheme(theme) {
    root.setAttribute('data-theme', theme);
    root.style.colorScheme = theme;

    if (themeToggle) {
        const icon = themeToggle.querySelector('.theme-toggle-icon');
        const text = themeToggle.querySelector('.theme-toggle-text');

        if (icon) {
            icon.textContent = theme === 'dark' ? '☀️' : '🌙';
        }

        if (text) {
            text.textContent = theme === 'dark' ? 'Light' : 'Dark';
        }
    }
}

const savedTheme = localStorage.getItem('absen-theme');
const preferredTheme = savedTheme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

applyTheme(preferredTheme);

themeToggle?.addEventListener('click', () => {
    const nextTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    applyTheme(nextTheme);
    localStorage.setItem('absen-theme', nextTheme);
});

function toggleSidebar() {
    const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
    const icon = sidebarToggle?.querySelector('.sidebar-toggle-icon');
    if (icon) {
        icon.textContent = isCollapsed ? '‹' : '›';
    }
    localStorage.setItem('absen-sidebar-collapsed', isCollapsed ? '1' : '0');
}

sidebarToggle?.addEventListener('click', toggleSidebar);

const savedSidebarState = localStorage.getItem('absen-sidebar-collapsed');
if (savedSidebarState === '1') {
    document.body.classList.add('sidebar-collapsed');
}

if (sidebarToggle) {
    const icon = sidebarToggle.querySelector('.sidebar-toggle-icon');
    if (icon) {
        icon.textContent = document.body.classList.contains('sidebar-collapsed') ? '‹' : '›';
    }
}

if (cookieModal) {
    cookieModal.querySelectorAll('[data-consent]').forEach((button) => {
        button.addEventListener('click', async () => {
            const consent = button.getAttribute('data-consent');

            await fetch('/cookie-consent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ consent }),
            });

            cookieModal.remove();
        });
    });
}

document.querySelectorAll('input[inputmode="numeric"]').forEach((input) => {
    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '');
    });
});

document.querySelectorAll('.modal-form').forEach((form) => {
    form.noValidate = true;

    form.addEventListener('submit', (event) => {
        const requiredFields = [...form.querySelectorAll('[required]')];
        const hasEmptyField = requiredFields.some((field) => !String(field.value || '').trim());

        if (hasEmptyField) {
            event.preventDefault();
            showValidationAlert('Semua field wajib diisi.');
            return;
        }

        const phoneInput = form.querySelector('input[name="no_telepon"]');
        if (phoneInput && !/^[0-9]+$/.test(phoneInput.value)) {
            event.preventDefault();
            showValidationAlert('No telepon hanya boleh berisi angka.');
            return;
        }

        const emergencyPhoneInputs = [...form.querySelectorAll('input[name^="no_darurat_"]')];
        const hasInvalidEmergencyPhone = emergencyPhoneInputs.some((input) => !/^[0-9]+$/.test(input.value));
        if (hasInvalidEmergencyPhone) {
            event.preventDefault();
            showValidationAlert('No darurat hanya boleh berisi angka.');
            return;
        }

        const emailInput = form.querySelector('input[type="email"]');
        if (emailInput && !emailInput.validity.valid) {
            event.preventDefault();
            showValidationAlert('Format email tidak valid.');
            return;
        }

        const passwordInput = form.querySelector('input[name="password"][minlength]');
        if (passwordInput && passwordInput.value.length < Number(passwordInput.minLength)) {
            event.preventDefault();
            showValidationAlert(`Password minimal ${passwordInput.minLength} karakter.`);
            return;
        }

        const passwordConfirmationInput = form.querySelector('input[name="password_confirmation"]');
        if (passwordInput && passwordConfirmationInput && passwordInput.value !== passwordConfirmationInput.value) {
            event.preventDefault();
            showValidationAlert('Konfirmasi password harus sama dengan password baru.');
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const notifications = window.absenNotifications || {};

    if (notifications.success) {
        showNotification('success', 'Berhasil', notifications.success);
        return;
    }

    if (notifications.error) {
        showNotification('error', 'Terjadi Kesalahan', notifications.error);
        return;
    }

    if (notifications.validationError) {
        showNotification('error', 'Data Belum Lengkap', 'Periksa kembali isian form yang tersedia.');
    }
});

// Notifications & Reminders feature
document.addEventListener('DOMContentLoaded', () => {
    const status = window.userAttendanceStatus || {};
    
    if (status.isPeserta || status.isAdmin) {
        // --- 1. Notification Dropdown Panel ---
        const bellBtn = document.getElementById('notification-bell-btn');
        const menu = document.getElementById('notification-menu');
        const list = document.getElementById('notification-list');
        const badge = document.getElementById('notification-badge');
        const markAllRead = document.getElementById('mark-all-read-btn');

        if (bellBtn && menu) {
            bellBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isVisible = menu.style.display === 'block';
                menu.style.display = isVisible ? 'none' : 'block';
            });

            document.addEventListener('click', (e) => {
                if (!menu.contains(e.target) && !bellBtn.contains(e.target)) {
                    menu.style.display = 'none';
                }
            });
        }

        async function fetchNotifications() {
            try {
                const response = await fetch('/peserta/notifications');
                if (!response.ok) return;
                const data = await response.json();
                
                if (data.length > 0) {
                    badge.textContent = data.length;
                    badge.style.display = 'block';

                    list.innerHTML = '';
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'notification-item notification-item-unread';
                        div.innerHTML = `
                            <div class="notification-item-title">${item.title}</div>
                            <div class="notification-item-desc">${item.message}</div>
                            <div class="notification-item-time">${item.created_at}</div>
                        `;
                        list.appendChild(div);
                    });
                } else {
                    badge.style.display = 'none';
                    list.innerHTML = '<div style="color: var(--text-secondary); text-align: center; padding: 20px 0; font-size: 0.85rem;">Tidak ada notifikasi baru</div>';
                }
            } catch (err) {
                console.error('Error fetching notifications:', err);
            }
        }

        if (markAllRead) {
            markAllRead.addEventListener('click', async () => {
                try {
                    const response = await fetch('/peserta/notifications/mark-read', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        }
                    });
                    if (response.ok) {
                        badge.style.display = 'none';
                        badge.textContent = '0';
                        list.querySelectorAll('.notification-item-unread').forEach(item => {
                            item.classList.remove('notification-item-unread');
                        });
                        fetchNotifications();
                    }
                } catch (err) {
                    console.error('Error marking notifications as read:', err);
                }
            });
        }

        // Fetch initially and poll every 30 seconds
        fetchNotifications();
        setInterval(fetchNotifications, 30000);
    }

    if (status.isPeserta) {
        // --- 2. Push Notifications & Reminders ---
        if ('Notification' in window) {
            // Request permission if not already granted or denied
            if (Notification.permission === 'default') {
                // Request politely on first user interaction or on load
                setTimeout(() => {
                    Notification.requestPermission();
                }, 2000);
            }

            const todayStr = new Date().toISOString().slice(0, 10);
            
            // Check if it's not a holiday
            if (!status.isHolidayToday) {
                const now = new Date();
                const currentHour = now.getHours();
                const currentMinutes = now.getMinutes();

                // A. Check-in Reminder (past 07:30 AM)
                if (!status.hasCheckedInToday) {
                    if (currentHour > 7 || (currentHour === 7 && currentMinutes >= 30)) {
                        const checkinReminded = localStorage.getItem('absen_checkin_reminded_date');
                        if (checkinReminded !== todayStr) {
                            if (Notification.permission === 'granted') {
                                new Notification('Pengingat Absen Masuk', {
                                    body: 'Halo! Anda belum melakukan absen masuk hari ini. Silakan segera absen masuk.',
                                    icon: '/favicon.ico'
                                });
                                localStorage.setItem('absen_checkin_reminded_date', todayStr);
                            }
                        }
                    }
                }

                // B. Check-out Reminder (past 15:30 PM)
                if (status.hasCheckedInToday && !status.hasCheckedOutToday) {
                    if (currentHour > 15 || (currentHour === 15 && currentMinutes >= 30)) {
                        const checkoutReminded = localStorage.getItem('absen_checkout_reminded_date');
                        if (checkoutReminded !== todayStr) {
                            if (Notification.permission === 'granted') {
                                new Notification('Pengingat Absen Pulang', {
                                    body: 'Halo! Jam kerja Anda akan segera berakhir. Jangan lupa mengisi logbook kegiatan dan melakukan absen pulang.',
                                    icon: '/favicon.ico'
                                });
                                localStorage.setItem('absen_checkout_reminded_date', todayStr);
                            }
                        }
                    }
                }
            }
        }
    }
});

// Mobile menu toggle logic
const mobileToggleBtn = document.getElementById('mobile-toggle-btn');
const sidebarBackdrop = document.getElementById('sidebar-backdrop');

if (mobileToggleBtn) {
    mobileToggleBtn.addEventListener('click', () => {
        document.body.classList.add('sidebar-mobile-open');
    });
}

if (sidebarBackdrop) {
    sidebarBackdrop.addEventListener('click', () => {
        document.body.classList.remove('sidebar-mobile-open');
    });
}

// Close mobile sidebar on nav link click
sidebar?.querySelectorAll('.nav-item a').forEach((link) => {
    link.addEventListener('click', () => {
        document.body.classList.remove('sidebar-mobile-open');
    });
});


// ==========================================================
// Global Loading Interceptors for Slow Connections (SweetAlert2)
// ==========================================================

// Helper to filter out downloads/exports
function shouldIgnoreUrl(url) {
    if (!url) return true;
    const urlStr = url.toLowerCase();
    const keywords = [
        'download', 'export', 'print', 'cetak', 'pdf', 'csv', 'xlsx', 
        'rekap', 'chart', 'graphic', 'selfie', 'foto', 'bukti', 'logout'
    ];
    return keywords.some(kw => urlStr.includes(kw));
}

// Global Form Submit Loader
document.addEventListener('submit', (e) => {
    const form = e.target;
    if (e.defaultPrevented) return;
    
    // Ignore target="_blank"
    if (form.getAttribute('target') === '_blank') return;
    
    // Ignore downloads/exports
    const action = form.getAttribute('action') || '';
    if (shouldIgnoreUrl(action)) return;

    Swal.fire({
        ...getSwalTheme(),
        title: 'Memproses Data...',
        text: 'Mohon tunggu sebentar.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
});

// Global Link Click Navigation Loader
document.addEventListener('click', (e) => {
    const link = e.target.closest('a');
    if (!link) return;

    // Ignore links that don't navigate
    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.target === '_blank') return;
    
    // Ignore download, prints, or exports
    if (link.hasAttribute('download') || shouldIgnoreUrl(link.href) || shouldIgnoreUrl(href)) return;

    // Verify it is an internal link
    const currentHost = window.location.host;
    try {
        const linkUrl = new URL(link.href);
        if (linkUrl.host !== currentHost) return;
    } catch (err) {
        if (!href.startsWith('/') && !href.startsWith('.') && href.indexOf(':') > -1) return;
    }

    // Show loading spinner
    Swal.fire({
        ...getSwalTheme(),
        title: 'Memuat Halaman...',
        text: 'Menghubungkan ke server...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
});

// Global Fetch/AJAX Loader Interceptor
const originalFetch = window.fetch;
window.fetch = async function (url, options) {
    let urlStr = '';
    if (typeof url === 'string') {
        urlStr = url;
    } else if (url instanceof URL) {
        urlStr = url.href;
    } else if (url && url.url) {
        urlStr = url.url;
    }
    
    const method = (options && options.method || 'GET').toUpperCase();
    const isBackground = urlStr.includes('notifications') || urlStr.includes('cookie-consent') || urlStr.includes('dev-reload-check');
    
    let loadingTimer = null;
    let swalShown = false;

    if (['POST', 'PUT', 'DELETE'].includes(method) && !isBackground) {
        loadingTimer = setTimeout(() => {
            swalShown = true;
            Swal.fire({
                ...getSwalTheme(),
                title: 'Menghubungkan...',
                text: 'Sedang memproses permintaan Anda, mohon tunggu.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }, 500); // 500ms delay to avoid flashing on fast connections
    }

    try {
        const response = await originalFetch(url, options);
        return response;
    } finally {
        if (loadingTimer) {
            clearTimeout(loadingTimer);
        }
        if (swalShown) {
            Swal.close();
        }
    }
};


