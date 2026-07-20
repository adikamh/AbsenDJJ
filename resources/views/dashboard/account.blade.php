@extends('dashboard.layout')

@section('title', 'Kelola Akun')
@section('header_title', 'Kelola Akun')

@push('styles')
<style>
    .account-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .form-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 24px 0 16px 0;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--glass-border);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-section-title svg {
        color: var(--accent-primary);
    }
    .readonly-notice {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-top: 4px;
        display: block;
        font-style: italic;
    }
    .input-disabled {
        background-color: rgba(148, 163, 184, 0.08) !important;
        cursor: not-allowed;
        color: var(--text-secondary) !important;
        border-color: var(--glass-border) !important;
    }
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr !important;
            gap: 16px !important;
        }
        .form-grid .form-group {
            grid-column: span 1 !important;
        }
        .account-container {
            padding: 0 4px !important;
        }
        .content-card {
            padding: 16px 12px !important;
        }
        .form-section-title {
            font-size: 1rem !important;
            margin: 20px 0 12px 0 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="account-container">
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Kelola Akun & Keamanan</h2>
        </div>

        <form action="{{ route('account.update') }}" method="POST" class="modal-form" id="manage-account-form" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- SECTION 1: Informasi Profil -->
            <div class="form-section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Informasi Profil</span>
            </div>

            <div class="form-grid">
                <!-- NIP Field (Only Admin & Peserta) -->
                @if(!$user->isSuperAdmin())
                    <div class="form-group">
                        <label for="nip">NIP <span style="color: #f87171;">*</span></label>
                        <input type="text" id="nip" name="nip" value="{{ old('nip', $user->nip) }}" required maxlength="24" placeholder="Hanya angka, maks 24 karakter" autocomplete="off">
                        @error('nip')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

                <!-- Nama Lengkap -->
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap <span style="color: #f87171;">*</span></label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required maxlength="170" placeholder="Nama lengkap Anda" autocomplete="off">
                    @error('nama_lengkap')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Jabatan (Only Admin & Peserta) -->
                @if(!$user->isSuperAdmin())
                    <div class="form-group">
                        <label for="jabatan">Jabatan <span style="font-size: 0.8rem; color: #9ca3af;">(Opsional)</span></label>
                        <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}" maxlength="170" placeholder="Contoh: Pengelola Jurnal, Team Leader, dll." autocomplete="off">
                        @error('jabatan')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

                <!-- Email (Editable for Super Admin, Read-Only for Others) -->
                <div class="form-group">
                    <label for="email">Email <span style="color: #f87171;">*</span></label>
                    @if($user->isSuperAdmin())
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="254" placeholder="contoh@domain.com" autocomplete="off">
                    @else
                        <input type="email" id="email" class="input-disabled" value="{{ $user->email }}" readonly tabindex="-1">
                        <span class="readonly-notice">🔒 Email tidak dapat diubah secara mandiri.</span>
                    @endif
                    @error('email')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- No Telepon -->
                <div class="form-group">
                    <label for="no_telepon">No Telepon <span style="color: #f87171;">*</span></label>
                    <input type="text" id="no_telepon" name="no_telepon" value="{{ old('no_telepon', $user->no_telepon) }}" inputmode="numeric" required maxlength="15" placeholder="Hanya angka, maks 15 karakter" autocomplete="off">
                    @error('no_telepon')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Instansi (Editable for Admin, Read-Only for Peserta) -->
                @if($user->isAdmin())
                    <div class="form-group" style="grid-column: span 2;">
                        <label for="instansi">Instansi <span style="color: #f87171;">*</span></label>
                        <input type="text" id="instansi" name="instansi" class="autocomplete-instansi" data-suggestions="{{ json_encode($instansi->pluck('nama_instansi')) }}" value="{{ old('instansi', $user->instansi?->nama_instansi) }}" required maxlength="170" placeholder="Nama instansi (maks 170 karakter)" autocomplete="off">
                        @error('instansi')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                @elseif($user->isPeserta())
                    <div class="form-group">
                        <label for="instansi">Instansi</label>
                        <input type="text" id="instansi" class="input-disabled" value="{{ $user->instansi?->nama_instansi ?? '-' }}" readonly tabindex="-1">
                        <span class="readonly-notice">🔒 Instansi hanya dapat diubah oleh Super Admin.</span>
                    </div>

                    <!-- Assigned Pembimbing (Read-only, spans 2 columns) -->
                    <div class="form-group" style="grid-column: span 2;">
                        <label for="pembimbing">Pembimbing Lapangan</label>
                        <input type="text" id="pembimbing" class="input-disabled" value="{{ $user->pembimbing?->nama_lengkap ?? 'Belum Ditugaskan' }}" readonly tabindex="-1">
                        <span class="readonly-notice">🔒 Pembimbing hanya dapat diubah oleh Super Admin.</span>
                    </div>
                @endif

                <!-- Alamat (Textarea, spans 2 columns) -->
                <div class="form-group" style="grid-column: span 2;">
                    <label for="alamat">Alamat <span style="color: #f87171;">*</span></label>
                    <textarea id="alamat" name="alamat" rows="3" required maxlength="224" placeholder="Alamat lengkap Anda" autocomplete="off">{{ old('alamat', $user->alamat) }}</textarea>
                    @error('alamat')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- SECTION 2: Kontak Darurat (Khusus Peserta) -->
            @if($user->isPeserta())
                <div class="form-section-title" style="margin-top: 32px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    <span>Kontak Darurat</span>
                </div>

                <!-- Kontak Darurat Utama -->
                <h4 style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary); margin: 0 0 12px 0;">Kontak Darurat Utama (Wajib)</h4>
                <div class="form-grid" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="nama_darurat_1">Nama Kontak 1 <span style="color: #f87171;">*</span></label>
                        <input type="text" id="nama_darurat_1" name="nama_darurat_1" value="{{ old('nama_darurat_1', $user->nama_darurat_1) }}" required maxlength="170" placeholder="Nama kontak darurat" autocomplete="off">
                        @error('nama_darurat_1')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="no_darurat_1">No Telepon 1 <span style="color: #f87171;">*</span></label>
                        <input type="text" id="no_darurat_1" name="no_darurat_1" value="{{ old('no_darurat_1', $user->no_darurat_1) }}" inputmode="numeric" required maxlength="15" placeholder="Hanya angka, maks 15 karakter" autocomplete="off">
                        @error('no_darurat_1')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="hubungan_darurat_1">Hubungan 1 <span style="color: #f87171;">*</span></label>
                        <select id="hubungan_darurat_1" name="hubungan_darurat_1" required>
                            <option value="">-- Pilih Hubungan --</option>
                            @foreach(['Orang Tua', 'Wali', 'Saudara', 'Suami/Istri', 'Kerabat', 'Teman', 'Lainnya'] as $hub)
                                <option value="{{ $hub }}" @selected(old('hubungan_darurat_1', $user->hubungan_darurat_1) === $hub)>{{ $hub }}</option>
                            @endforeach
                        </select>
                        @error('hubungan_darurat_1')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Kontak Darurat Tambahan -->
                <h4 style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary); margin: 20px 0 12px 0;">
                    Kontak Darurat Tambahan (Opsional) <span id="asterisk-darurat-2" style="color: #f87171; display: none;">*</span>
                </h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nama_darurat_2">Nama Kontak 2</label>
                        <input type="text" id="nama_darurat_2" name="nama_darurat_2" value="{{ old('nama_darurat_2', $user->nama_darurat_2) }}" maxlength="170" placeholder="Nama kontak darurat" autocomplete="off">
                        @error('nama_darurat_2')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="no_darurat_2">No Telepon 2</label>
                        <input type="text" id="no_darurat_2" name="no_darurat_2" value="{{ old('no_darurat_2', $user->no_darurat_2) }}" inputmode="numeric" maxlength="15" placeholder="Hanya angka, maks 15 karakter" autocomplete="off">
                        @error('no_darurat_2')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="hubungan_darurat_2">Hubungan 2</label>
                        <select id="hubungan_darurat_2" name="hubungan_darurat_2">
                            <option value="">-- Pilih Hubungan --</option>
                            @foreach(['Orang Tua', 'Wali', 'Saudara', 'Suami/Istri', 'Kerabat', 'Teman', 'Lainnya'] as $hub)
                                <option value="{{ $hub }}" @selected(old('hubungan_darurat_2', $user->hubungan_darurat_2) === $hub)>{{ $hub }}</option>
                            @endforeach
                        </select>
                        @error('hubungan_darurat_2')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            @endif

            <!-- SECTION 2.5: Tanda Tangan Digital (Khusus Peserta) -->
            @if($user->isPeserta())
                <div class="form-section-title" style="margin-top: 32px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                    </svg>
                    <span>Tanda Tangan Digital</span>
                </div>

                <div class="form-grid">
                    <div class="form-group" style="grid-column: span 2;">
                        <label for="signature">Unggah Tanda Tangan (PNG Transparan / JPG / WEBP, Maks. 1MB)</label>
                        <input type="file" id="signature" name="signature" accept="image/png, image/jpeg, image/jpg, image/webp" style="padding: 6px; border: 1px dashed var(--glass-border); border-radius: 8px; width: 100%;">
                        @error('signature')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        <span class="readonly-notice">Direkomendasikan menggunakan file gambar PNG dengan latar belakang transparan. Tanda tangan ini akan muncul otomatis di formulir cetak absensi.</span>

                        @if($user->signature_path)
                            <div style="margin-top: 15px; padding: 10px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--glass-border); border-radius: 8px; display: inline-block;">
                                <span style="font-size: 0.8rem; color: var(--text-secondary); display: block; margin-bottom: 5px;">Tanda Tangan Aktif:</span>
                                <img src="{{ asset($user->signature_path) }}" alt="Tanda Tangan" style="max-height: 80px; width: auto; display: block; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- SECTION 3: Ubah Password -->
            <div class="form-section-title" style="margin-top: 32px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <span>Ubah Password</span>
            </div>

            <div class="form-grid">
                <!-- Current Password -->
                <div class="form-group" style="grid-column: span 2;">
                    <label for="current_password">Password Lama</label>
                    <input type="password" id="current_password" name="current_password" style="width: 100%; padding: 10px 14px; border-radius: 8px;" placeholder="Wajib diisi jika Anda mengganti password">
                    @error('current_password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- New Password -->
                <div class="form-group">
                    <label for="password">Password Baru</label>
                    <input type="password" id="password" name="password" style="width: 100%; padding: 10px 14px; border-radius: 8px;" placeholder="Minimal 8 karakter">
                    @error('password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm New Password -->
                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" style="width: 100%; padding: 10px 14px; border-radius: 8px;" placeholder="Minimal 8 karakter">
                    @error('password_confirmation')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="grid-column: span 2; display: flex; justify-content: flex-end;">
                    <button type="button" id="toggle-account-passwords" class="password-toggle" style="padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 600; border: 1px solid var(--glass-border); background: rgba(148, 163, 184, 0.14); color: var(--text-primary);">
                        Tampilkan Password
                    </button>
                </div>
            </div>

            <div class="modal-actions" style="margin-top: 36px; padding-top: 20px; border-top: 1px solid var(--glass-border);">
                <button type="submit" class="btn-primary" style="padding: 12px 28px; font-size: 0.95rem;">Simpan Perubahan Akun</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const DIGITS_ONLY_PATTERN = /[^0-9]/g;
        const CHARS_PATTERN = /[^a-zA-Z0-9\s\-\/\(\)\.,\'\"\\&]/g;
        const EMAIL_ALLOWED_PATTERN = /[^a-zA-Z0-9\s\-\/\(\)\.,\'\"\\&@_\+]/g;
        const ADDRESS_ALLOWED_PATTERN = /[^a-zA-Z0-9\s\-\/\(\)\.,\'\"\\&#@:\n\r]/g;

        function applySanitizer(id, pattern, maxLength) {
            const input = document.getElementById(id);
            if (!input) return;

            const container = input.closest('.form-group');
            let counterEl = null;

            if (maxLength) {
                counterEl = document.createElement('span');
                counterEl.style.fontSize = '0.75rem';
                counterEl.style.color = 'var(--text-secondary)';
                counterEl.style.marginTop = '4px';
                counterEl.style.display = 'block';
                counterEl.style.textAlign = 'right';
                container?.appendChild(counterEl);
            }

            function updateCounter() {
                if (counterEl && maxLength) {
                    const currentLength = input.value.length;
                    counterEl.textContent = `${currentLength} / ${maxLength} karakter`;
                    if (currentLength >= maxLength) {
                        counterEl.style.color = '#ef4444';
                    } else {
                        counterEl.style.color = 'var(--text-secondary)';
                    }
                }
            }

            input.addEventListener('input', () => {
                let cleaned = input.value.replace(pattern, '');
                if (maxLength && cleaned.length > maxLength) {
                    cleaned = cleaned.substring(0, maxLength);
                }
                input.value = cleaned;
                updateCounter();
            });

            input.addEventListener('paste', () => {
                setTimeout(() => {
                    let cleaned = input.value.replace(pattern, '');
                    if (maxLength && cleaned.length > maxLength) {
                        cleaned = cleaned.substring(0, maxLength);
                    }
                    input.value = cleaned;
                    updateCounter();
                }, 0);
            });

            if (maxLength) {
                updateCounter();
            }
        }

        // Apply Sanitizers
        applySanitizer('nip', DIGITS_ONLY_PATTERN, 24);
        applySanitizer('nama_lengkap', CHARS_PATTERN, 170);
        applySanitizer('email', EMAIL_ALLOWED_PATTERN, 254);
        applySanitizer('no_telepon', DIGITS_ONLY_PATTERN, 15);
        applySanitizer('alamat', ADDRESS_ALLOWED_PATTERN, 224);
        applySanitizer('instansi', CHARS_PATTERN, 170);

        // Emergency Contacts (Only if Peserta)
        const isPeserta = @json($user->isPeserta());
        if (isPeserta) {
            applySanitizer('nama_darurat_1', CHARS_PATTERN, 170);
            applySanitizer('no_darurat_1', DIGITS_ONLY_PATTERN, 15);
            applySanitizer('nama_darurat_2', CHARS_PATTERN, 170);
            applySanitizer('no_darurat_2', DIGITS_ONLY_PATTERN, 15);

            // Dynamic asterisk for emergency contact 2
            const name2 = document.getElementById('nama_darurat_2');
            const no2 = document.getElementById('no_darurat_2');
            const hubungan2 = document.getElementById('hubungan_darurat_2');
            const asterisk = document.getElementById('asterisk-darurat-2');

            function toggleAsterisk() {
                const isAnyFilled = !!(name2?.value.trim() || no2?.value.trim() || hubungan2?.value);
                if (asterisk) {
                    asterisk.style.display = isAnyFilled ? 'inline' : 'none';
                }
            }

            [name2, no2, hubungan2].forEach(el => {
                el?.addEventListener('input', toggleAsterisk);
                el?.addEventListener('change', toggleAsterisk);
            });

            // Run initial check
            toggleAsterisk();
        }

        // Password Visibility Toggle
        const toggleBtn = document.getElementById('toggle-account-passwords');
        const oldPass = document.getElementById('current_password');
        const newPass = document.getElementById('password');
        const confirmPass = document.getElementById('password_confirmation');

        toggleBtn?.addEventListener('click', () => {
            const isHidden = newPass.type === 'password';
            const targetType = isHidden ? 'text' : 'password';

            if (oldPass) oldPass.type = targetType;
            if (newPass) newPass.type = targetType;
            if (confirmPass) confirmPass.type = targetType;

            toggleBtn.textContent = isHidden ? 'Sembunyikan Password' : 'Tampilkan Password';
        });

        // Submit form validations
        const form = document.getElementById('manage-account-form');
        form.noValidate = true;

        function showErrorAlert(title, message) {
            if (window.Swal) {
                window.Swal.fire({
                    background: document.documentElement.getAttribute('data-theme') === 'light' ? '#ffffff' : '#1e293b',
                    color: document.documentElement.getAttribute('data-theme') === 'light' ? '#0f172a' : '#f8fafc',
                    confirmButtonColor: '#2e4085',
                    icon: 'warning',
                    title: title,
                    text: message,
                    confirmButtonText: 'Mengerti'
                });
            } else {
                alert(title + ': ' + message);
            }
        }

        form.addEventListener('submit', (event) => {
            const requiredFields = [...form.querySelectorAll('[required]')];
            const emptyFields = requiredFields.filter((field) => !String(field.value || '').trim());

            if (emptyFields.length > 0) {
                event.preventDefault();
                emptyFields.forEach(el => {
                    el.style.borderColor = '#f87171';
                    el.style.boxShadow = '0 0 0 3px rgba(248, 113, 113, 0.2)';
                    el.addEventListener('input', function clearError() {
                        el.style.borderColor = '';
                        el.style.boxShadow = '';
                        el.removeEventListener('input', clearError);
                    });
                });
                emptyFields[0].focus();
                showErrorAlert('Data Belum Lengkap', 'Semua field wajib diisi. Kolom yang kosong telah ditandai dengan warna merah.');
                return;
            }

            const nip = document.getElementById('nip')?.value || '';
            const email = document.getElementById('email')?.value || '';
            const noTelepon = document.getElementById('no_telepon')?.value || '';

            // NIP validation
            if (document.getElementById('nip') && !/^[0-9]+$/.test(nip)) {
                event.preventDefault();
                const el = document.getElementById('nip');
                el.style.borderColor = '#f87171';
                el.style.boxShadow = '0 0 0 3px rgba(248, 113, 113, 0.2)';
                el.focus();
                showErrorAlert('NIP Tidak Valid', 'NIP hanya boleh diisi angka tanpa spasi.');
                return;
            }

            // Email validation (Only if Super Admin)
            if (document.getElementById('email') && !document.getElementById('email').readOnly) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    event.preventDefault();
                    const el = document.getElementById('email');
                    el.style.borderColor = '#f87171';
                    el.style.boxShadow = '0 0 0 3px rgba(248, 113, 113, 0.2)';
                    el.focus();
                    showErrorAlert('Format Email Tidak Valid', 'Format email tidak valid (harus mengandung @ dan domain yang memiliki titik).');
                    return;
                }
            }

            // No telepon validation
            if (!/^[0-9]+$/.test(noTelepon)) {
                event.preventDefault();
                const el = document.getElementById('no_telepon');
                el.style.borderColor = '#f87171';
                el.style.boxShadow = '0 0 0 3px rgba(248, 113, 113, 0.2)';
                el.focus();
                showErrorAlert('No Telepon Tidak Valid', 'No telepon hanya boleh diisi angka.');
                return;
            }

            // Emergency Contacts Validation (Only if Peserta)
            if (isPeserta) {
                const namaDarurat1 = document.getElementById('nama_darurat_1')?.value || '';
                const noDarurat1 = document.getElementById('no_darurat_1')?.value || '';
                const hubunganDarurat1 = document.getElementById('hubungan_darurat_1')?.value || '';

                const namaDarurat2 = document.getElementById('nama_darurat_2')?.value || '';
                const noDarurat2 = document.getElementById('no_darurat_2')?.value || '';
                const hubunganDarurat2 = document.getElementById('hubungan_darurat_2')?.value || '';

                // No Darurat 1 validation
                if (noDarurat1 === noTelepon) {
                    event.preventDefault();
                    const el = document.getElementById('no_darurat_1');
                    el.style.borderColor = '#f87171';
                    el.style.boxShadow = '0 0 0 3px rgba(248, 113, 113, 0.2)';
                    el.focus();
                    showErrorAlert('Kontak Darurat Bentrok', 'No Darurat 1 tidak boleh sama dengan No Telepon Anda.');
                    return;
                }

                // Conditional validation for Emergency Contact 2
                const hasNama2 = !!namaDarurat2.trim();
                const hasNo2 = !!noDarurat2.trim();
                const hasHubungan2 = !!hubunganDarurat2.trim();

                if (hasNama2 || hasNo2 || hasHubungan2) {
                    if (!hasNama2 || !hasNo2 || !hasHubungan2) {
                        event.preventDefault();
                        const fieldsToHighlight = [];
                        if (!hasNama2) fieldsToHighlight.push(document.getElementById('nama_darurat_2'));
                        if (!hasNo2) fieldsToHighlight.push(document.getElementById('no_darurat_2'));
                        if (!hasHubungan2) fieldsToHighlight.push(document.getElementById('hubungan_darurat_2'));

                        fieldsToHighlight.forEach(el => {
                            if (el) {
                                el.style.borderColor = '#f87171';
                                el.style.boxShadow = '0 0 0 3px rgba(248, 113, 113, 0.2)';
                            }
                        });
                        fieldsToHighlight[0]?.focus();
                        showErrorAlert('Data Belum Lengkap', 'Jika Kontak Darurat 2 diisi, maka Nama, Nomor Telepon, dan Hubungan wajib diisi semuanya.');
                        return;
                    }
                    if (noDarurat2 === noTelepon) {
                        event.preventDefault();
                        const el = document.getElementById('no_darurat_2');
                        el.style.borderColor = '#f87171';
                        el.style.boxShadow = '0 0 0 3px rgba(248, 113, 113, 0.2)';
                        el.focus();
                        showErrorAlert('Kontak Darurat Bentrok', 'No Darurat 2 tidak boleh sama dengan No Telepon Anda.');
                        return;
                    }
                }
            }

            // Change password validation
            if (newPass?.value.length > 0) {
                if (!oldPass?.value.trim()) {
                    event.preventDefault();
                    oldPass.style.borderColor = '#f87171';
                    oldPass.style.boxShadow = '0 0 0 3px rgba(248, 113, 113, 0.2)';
                    oldPass.focus();
                    showErrorAlert('Password Lama Wajib Diisi', 'Password lama wajib diisi untuk mengubah password.');
                    return;
                }
                if (newPass.value.length < 8) {
                    event.preventDefault();
                    newPass.style.borderColor = '#f87171';
                    newPass.style.boxShadow = '0 0 0 3px rgba(248, 113, 113, 0.2)';
                    newPass.focus();
                    showErrorAlert('Password Terlalu Pendek', 'Password baru minimal 8 karakter.');
                    return;
                }
                if (newPass.value !== confirmPass?.value) {
                    event.preventDefault();
                    confirmPass.style.borderColor = '#f87171';
                    confirmPass.style.boxShadow = '0 0 0 3px rgba(248, 113, 113, 0.2)';
                    confirmPass.focus();
                    showErrorAlert('Konfirmasi Password Tidak Cocok', 'Konfirmasi password baru harus sama dengan password baru.');
                    return;
                }
            }
        });
    });
</script>
@endpush
