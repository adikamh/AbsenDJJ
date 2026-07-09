import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

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

