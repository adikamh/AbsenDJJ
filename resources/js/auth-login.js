// Page-specific scripts for auth/login.blade.php.

const passwordInput = document.getElementById('password');
const passwordToggle = document.getElementById('password-toggle');

if (passwordInput && passwordToggle) {
    passwordToggle.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        passwordToggle.setAttribute('aria-pressed', String(isPassword));
        passwordToggle.setAttribute('aria-label', isPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');

        const icon = passwordToggle.querySelector('.password-toggle-icon');
        if (icon) {
            icon.innerHTML = isPassword
                ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"></path><path d="M10.6 10.6A3 3 0 0 0 13.4 13.4"></path><path d="M9.9 5.1A10.9 10.9 0 0 1 12 5c6.5 0 10 7 10 7a16.8 16.8 0 0 1-3.3 4.1"></path><path d="M6.6 6.6A16.8 16.8 0 0 0 2 12s3.5 7 10 7a10.9 10.9 0 0 0 2.1-.2"></path></svg>'
                : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        }
    });
}

