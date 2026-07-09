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

