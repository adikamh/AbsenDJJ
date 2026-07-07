// Page-specific scripts for dashboard/layout.blade.php.

const root = document.documentElement;
const themeToggle = document.getElementById('theme-toggle');
const sidebarToggle = document.getElementById('sidebar-toggle');
const sidebar = document.getElementById('sidebar');
const cookieModal = document.getElementById('cookie-modal-backdrop');

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

