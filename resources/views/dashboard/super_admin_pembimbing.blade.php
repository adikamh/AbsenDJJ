@extends('dashboard.layout')

@section('title', 'Kelola Pembimbing')
@section('header_title', 'Kelola Pembimbing')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Daftar Pembimbing</h2>
            <button type="button" class="btn-primary" id="open-add-pembimbing-modal">
                Tambahkan Pembimbing
            </button>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembimbing as $user)
                        <tr>
                            <td>{{ $user->nip ?? '-' }}</td>
                            <td><strong>{{ $user->nama_lengkap }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->status_aktif ? 'badge-success' : 'badge-warning' }}">
                                    {{ $user->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button
                                        type="button"
                                        class="btn-action btn-action-view"
                                        data-action="view-pembimbing"
                                        data-nip="{{ $user->nip ?? '-' }}"
                                        data-nama="{{ $user->nama_lengkap }}"
                                        data-email="{{ $user->email }}"
                                        data-telepon="{{ $user->no_telepon ?? '-' }}"
                                        data-alamat="{{ $user->alamat ?? '-' }}"
                                        data-instansi="{{ $user->instansi?->nama_instansi ?? '-' }}"
                                        data-status="{{ $user->status_aktif ? 'Aktif' : 'Tidak Aktif' }}"
                                        aria-label="Detail {{ $user->nama_lengkap }}"
                                        title="Detail"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2.06 12.35a1 1 0 0 1 0-.7C3.7 7.1 7.46 4 12 4s8.3 3.1 9.94 7.65a1 1 0 0 1 0 .7C20.3 16.9 16.54 20 12 20s-8.3-3.1-9.94-7.65Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>

                                    <button
                                        type="button"
                                        class="btn-action btn-action-edit"
                                        data-action="edit-pembimbing"
                                        data-id="{{ $user->id }}"
                                        data-nip="{{ $user->nip }}"
                                        data-nama="{{ $user->nama_lengkap }}"
                                        data-email="{{ $user->email }}"
                                        data-telepon="{{ $user->no_telepon }}"
                                        data-alamat="{{ $user->alamat }}"
                                        data-instansi="{{ $user->instansi?->nama_instansi }}"
                                        data-status="{{ $user->status_aktif ? '1' : '0' }}"
                                        aria-label="Edit {{ $user->nama_lengkap }}"
                                        title="Edit"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                        </svg>
                                    </button>

                                    <button
                                        type="button"
                                        class="btn-action btn-action-reset"
                                        data-action="reset-password-pembimbing"
                                        data-id="{{ $user->id }}"
                                        data-name="{{ $user->nama_lengkap }}"
                                        aria-label="Reset password {{ $user->nama_lengkap }}"
                                        title="Reset Password"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2 12a10 10 0 1 0 3-7.12"></path>
                                            <path d="M2 4v6h6"></path>
                                            <rect x="9" y="11" width="6" height="5" rx="1"></rect>
                                            <path d="M10 11V9a2 2 0 1 1 4 0v2"></path>
                                        </svg>
                                    </button>

                                    <form action="{{ route('super-admin.pembimbing.destroy', $user) }}" method="POST" class="delete-pembimbing-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-action-delete" data-name="{{ $user->nama_lengkap }}" aria-label="Hapus {{ $user->nama_lengkap }}" title="Delete">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M3 6h18"></path>
                                                <path d="M8 6V4h8v2"></path>
                                                <path d="M19 6l-1 14H6L5 6"></path>
                                                <path d="M10 11v5"></path>
                                                <path d="M14 11v5"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">Belum ada pembimbing terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="form-modal-backdrop {{ $errors->storePembimbing->any() ? 'is-open' : '' }}" id="add-pembimbing-modal" aria-hidden="{{ $errors->storePembimbing->any() ? 'false' : 'true' }}">
        <div class="form-modal" role="dialog" aria-modal="true" aria-labelledby="add-pembimbing-title">
            <div class="form-modal-header">
                <h3 id="add-pembimbing-title">Tambah Pembimbing</h3>
                <button type="button" class="modal-close" id="close-add-pembimbing-modal" aria-label="Tutup modal">&times;</button>
            </div>

            <form action="{{ route('super-admin.pembimbing.store') }}" method="POST" class="modal-form">
                @csrf

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nip">NIP</label>
                        <input type="text" id="nip" name="nip" value="{{ old('nip') }}" required>
                        @error('nip', 'storePembimbing')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="nama_lengkap">Nama</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required>
                        @error('nama_lengkap', 'storePembimbing')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                        @error('email', 'storePembimbing')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="no_telepon">No Telepon</label>
                        <input type="text" id="no_telepon" name="no_telepon" value="{{ old('no_telepon') }}" inputmode="numeric" pattern="[0-9]+" required>
                        @error('no_telepon', 'storePembimbing')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="alamat">Alamat</label>
                        <input type="text" id="alamat" name="alamat" value="{{ old('alamat') }}" required>
                        @error('alamat', 'storePembimbing')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="instansi">Instansi</label>
                        <input type="text" id="instansi" name="instansi" value="{{ old('instansi') }}" required>
                        @error('instansi', 'storePembimbing')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" minlength="8" required>
                            <button type="button" class="password-toggle" id="toggle-add-pembimbing-password">
                                Tampilkan
                            </button>
                        </div>
                        @error('password', 'storePembimbing')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status_aktif">Status</label>
                        <select id="status_aktif" name="status_aktif" required>
                            <option value="1" @selected(old('status_aktif', '1') === '1')>Aktif</option>
                            <option value="0" @selected(old('status_aktif') === '0')>Tidak Aktif</option>
                        </select>
                        @error('status_aktif', 'storePembimbing')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancel-add-pembimbing-modal">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Pembimbing</button>
                </div>
            </form>
        </div>
    </div>

    <div class="form-modal-backdrop" id="detail-pembimbing-modal" aria-hidden="true">
        <div class="form-modal detail-modal" role="dialog" aria-modal="true" aria-labelledby="detail-pembimbing-title">
            <div class="form-modal-header">
                <h3 id="detail-pembimbing-title">Detail Pembimbing</h3>
                <button type="button" class="modal-close" id="close-detail-pembimbing-modal" aria-label="Tutup modal">&times;</button>
            </div>

            <div class="detail-list">
                <div class="detail-item">
                    <span>NIP</span>
                    <strong id="detail-nip">-</strong>
                </div>
                <div class="detail-item">
                    <span>Nama</span>
                    <strong id="detail-nama">-</strong>
                </div>
                <div class="detail-item">
                    <span>Email</span>
                    <strong id="detail-email">-</strong>
                </div>
                <div class="detail-item">
                    <span>No Telepon</span>
                    <strong id="detail-telepon">-</strong>
                </div>
                <div class="detail-item">
                    <span>Alamat</span>
                    <strong id="detail-alamat">-</strong>
                </div>
                <div class="detail-item">
                    <span>Instansi</span>
                    <strong id="detail-instansi">-</strong>
                </div>
                <div class="detail-item">
                    <span>Status</span>
                    <strong id="detail-status">-</strong>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="close-detail-pembimbing-action">Tutup</button>
            </div>
        </div>
    </div>

    <div class="form-modal-backdrop {{ $errors->updatePembimbing->any() ? 'is-open' : '' }}" id="edit-pembimbing-modal" aria-hidden="{{ $errors->updatePembimbing->any() ? 'false' : 'true' }}">
        <div class="form-modal" role="dialog" aria-modal="true" aria-labelledby="edit-pembimbing-title">
            <div class="form-modal-header">
                <h3 id="edit-pembimbing-title">Edit Pembimbing</h3>
                <button type="button" class="modal-close" id="close-edit-pembimbing-modal" aria-label="Tutup modal">&times;</button>
            </div>

            <form
                action="{{ old('edit_id') ? url('/super-admin/pembimbing/' . old('edit_id')) : url('/super-admin/pembimbing/__ID__') }}"
                method="POST"
                class="modal-form"
                id="edit-pembimbing-form"
                data-action-template="{{ url('/super-admin/pembimbing/__ID__') }}"
            >
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id" name="edit_id" value="{{ old('edit_id') }}">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_nip">NIP</label>
                        <input type="text" id="edit_nip" name="nip" value="{{ old('nip') }}" required>
                        @error('nip', 'updatePembimbing')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_nama_lengkap">Nama</label>
                        <input type="text" id="edit_nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required>
                        @error('nama_lengkap', 'updatePembimbing')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email" value="{{ old('email') }}" required>
                        @error('email', 'updatePembimbing')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_no_telepon">No Telepon</label>
                        <input type="text" id="edit_no_telepon" name="no_telepon" value="{{ old('no_telepon') }}" inputmode="numeric" pattern="[0-9]+" required>
                        @error('no_telepon', 'updatePembimbing')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_alamat">Alamat</label>
                        <input type="text" id="edit_alamat" name="alamat" value="{{ old('alamat') }}" required>
                        @error('alamat', 'updatePembimbing')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_instansi">Instansi</label>
                        <input type="text" id="edit_instansi" name="instansi" value="{{ old('instansi') }}" required>
                        @error('instansi', 'updatePembimbing')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_status_aktif">Status</label>
                        <select id="edit_status_aktif" name="status_aktif" required>
                            <option value="1" @selected(old('status_aktif', '1') === '1')>Aktif</option>
                            <option value="0" @selected(old('status_aktif') === '0')>Tidak Aktif</option>
                        </select>
                        @error('status_aktif', 'updatePembimbing')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancel-edit-pembimbing-modal">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="form-modal-backdrop {{ $errors->resetPembimbingPassword->any() ? 'is-open' : '' }}" id="reset-password-pembimbing-modal" aria-hidden="{{ $errors->resetPembimbingPassword->any() ? 'false' : 'true' }}">
        <div class="form-modal" role="dialog" aria-modal="true" aria-labelledby="reset-password-pembimbing-title">
            <div class="form-modal-header">
                <h3 id="reset-password-pembimbing-title">Reset Password Pembimbing</h3>
                <button type="button" class="modal-close" id="close-reset-password-pembimbing-modal" aria-label="Tutup modal">&times;</button>
            </div>

            <form
                action="{{ old('reset_id') ? url('/super-admin/pembimbing/' . old('reset_id') . '/reset-password') : url('/super-admin/pembimbing/__ID__/reset-password') }}"
                method="POST"
                class="modal-form"
                id="reset-password-pembimbing-form"
                data-action-template="{{ url('/super-admin/pembimbing/__ID__/reset-password') }}"
            >
                @csrf
                @method('PUT')
                <input type="hidden" id="reset_id" name="reset_id" value="{{ old('reset_id') }}">

                <div class="detail-list">
                    <div class="detail-item">
                        <span>Pembimbing</span>
                        <strong id="reset-password-pembimbing-name">-</strong>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="reset_password">Password Baru</label>
                        <div class="password-field">
                            <input type="password" id="reset_password" name="password" minlength="8" required>
                            <button type="button" class="password-toggle" id="toggle-reset-pembimbing-password">
                                Tampilkan
                            </button>
                        </div>
                        @error('password', 'resetPembimbingPassword')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="reset_password_confirmation">Konfirmasi Password</label>
                        <input type="password" id="reset_password_confirmation" name="password_confirmation" minlength="8" required>
                        @error('password_confirmation', 'resetPembimbingPassword')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancel-reset-password-pembimbing-modal">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Password</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const addPembimbingModal = document.getElementById('add-pembimbing-modal');
        const detailPembimbingModal = document.getElementById('detail-pembimbing-modal');
        const editPembimbingModal = document.getElementById('edit-pembimbing-modal');
        const resetPasswordPembimbingModal = document.getElementById('reset-password-pembimbing-modal');
        const openAddPembimbingModal = document.getElementById('open-add-pembimbing-modal');
        const closeAddPembimbingModal = document.getElementById('close-add-pembimbing-modal');
        const cancelAddPembimbingModal = document.getElementById('cancel-add-pembimbing-modal');
        const closeDetailPembimbingModal = document.getElementById('close-detail-pembimbing-modal');
        const closeDetailPembimbingAction = document.getElementById('close-detail-pembimbing-action');
        const closeEditPembimbingModal = document.getElementById('close-edit-pembimbing-modal');
        const cancelEditPembimbingModal = document.getElementById('cancel-edit-pembimbing-modal');
        const editPembimbingForm = document.getElementById('edit-pembimbing-form');
        const resetPasswordPembimbingForm = document.getElementById('reset-password-pembimbing-form');
        const closeResetPasswordPembimbingModal = document.getElementById('close-reset-password-pembimbing-modal');
        const cancelResetPasswordPembimbingModal = document.getElementById('cancel-reset-password-pembimbing-modal');
        const addPembimbingPassword = document.getElementById('password');
        const toggleAddPembimbingPassword = document.getElementById('toggle-add-pembimbing-password');
        const resetPembimbingPassword = document.getElementById('reset_password');
        const resetPembimbingPasswordConfirmation = document.getElementById('reset_password_confirmation');
        const toggleResetPembimbingPassword = document.getElementById('toggle-reset-pembimbing-password');

        function toggleModal(modal, isOpen) {
            modal?.classList.toggle('is-open', isOpen);
            modal?.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        }

        function setText(id, value) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value || '-';
            }
        }

        function setValue(id, value) {
            const element = document.getElementById(id);
            if (element) {
                element.value = value || '';
            }
        }

        openAddPembimbingModal?.addEventListener('click', () => toggleModal(addPembimbingModal, true));
        closeAddPembimbingModal?.addEventListener('click', () => toggleModal(addPembimbingModal, false));
        cancelAddPembimbingModal?.addEventListener('click', () => toggleModal(addPembimbingModal, false));
        toggleAddPembimbingPassword?.addEventListener('click', () => {
            if (! addPembimbingPassword) {
                return;
            }

            const isHidden = addPembimbingPassword.type === 'password';
            addPembimbingPassword.type = isHidden ? 'text' : 'password';
            toggleAddPembimbingPassword.textContent = isHidden ? 'Sembunyikan' : 'Tampilkan';
        });
        closeDetailPembimbingModal?.addEventListener('click', () => toggleModal(detailPembimbingModal, false));
        closeDetailPembimbingAction?.addEventListener('click', () => toggleModal(detailPembimbingModal, false));
        closeEditPembimbingModal?.addEventListener('click', () => toggleModal(editPembimbingModal, false));
        cancelEditPembimbingModal?.addEventListener('click', () => toggleModal(editPembimbingModal, false));
        closeResetPasswordPembimbingModal?.addEventListener('click', () => toggleModal(resetPasswordPembimbingModal, false));
        cancelResetPasswordPembimbingModal?.addEventListener('click', () => toggleModal(resetPasswordPembimbingModal, false));
        toggleResetPembimbingPassword?.addEventListener('click', () => {
            if (! resetPembimbingPassword || ! resetPembimbingPasswordConfirmation) {
                return;
            }

            const isHidden = resetPembimbingPassword.type === 'password';
            resetPembimbingPassword.type = isHidden ? 'text' : 'password';
            resetPembimbingPasswordConfirmation.type = isHidden ? 'text' : 'password';
            toggleResetPembimbingPassword.textContent = isHidden ? 'Sembunyikan' : 'Tampilkan';
        });

        document.querySelectorAll('[data-action="view-pembimbing"]').forEach((button) => {
            button.addEventListener('click', () => {
                setText('detail-nip', button.dataset.nip);
                setText('detail-nama', button.dataset.nama);
                setText('detail-email', button.dataset.email);
                setText('detail-telepon', button.dataset.telepon);
                setText('detail-alamat', button.dataset.alamat);
                setText('detail-instansi', button.dataset.instansi);
                setText('detail-status', button.dataset.status);
                toggleModal(detailPembimbingModal, true);
            });
        });

        document.querySelectorAll('[data-action="edit-pembimbing"]').forEach((button) => {
            button.addEventListener('click', () => {
                const actionTemplate = editPembimbingForm?.dataset.actionTemplate || '';
                if (editPembimbingForm) {
                    editPembimbingForm.action = actionTemplate.replace('__ID__', button.dataset.id);
                }

                setValue('edit_id', button.dataset.id);
                setValue('edit_nip', button.dataset.nip);
                setValue('edit_nama_lengkap', button.dataset.nama);
                setValue('edit_email', button.dataset.email);
                setValue('edit_no_telepon', button.dataset.telepon);
                setValue('edit_alamat', button.dataset.alamat);
                setValue('edit_instansi', button.dataset.instansi);
                setValue('edit_status_aktif', button.dataset.status);
                toggleModal(editPembimbingModal, true);
            });
        });

        document.querySelectorAll('[data-action="reset-password-pembimbing"]').forEach((button) => {
            button.addEventListener('click', () => {
                const actionTemplate = resetPasswordPembimbingForm?.dataset.actionTemplate || '';
                if (resetPasswordPembimbingForm) {
                    resetPasswordPembimbingForm.action = actionTemplate.replace('__ID__', button.dataset.id);
                    resetPasswordPembimbingForm.reset();
                }

                setValue('reset_id', button.dataset.id);
                setText('reset-password-pembimbing-name', button.dataset.name);
                if (resetPembimbingPassword && resetPembimbingPasswordConfirmation && toggleResetPembimbingPassword) {
                    resetPembimbingPassword.type = 'password';
                    resetPembimbingPasswordConfirmation.type = 'password';
                    toggleResetPembimbingPassword.textContent = 'Tampilkan';
                }
                toggleModal(resetPasswordPembimbingModal, true);
            });
        });

        document.querySelectorAll('.delete-pembimbing-form').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const name = form.querySelector('[data-name]')?.dataset.name || 'pembimbing ini';
                const confirmed = window.confirmDangerAction
                    ? await window.confirmDangerAction({
                        title: 'Hapus Pembimbing?',
                        text: `Data ${name} akan dihapus dari daftar pembimbing.`,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal',
                    })
                    : window.confirm(`Hapus data ${name}?`);

                if (confirmed) {
                    form.submit();
                }
            });
        });

        [addPembimbingModal, detailPembimbingModal, editPembimbingModal, resetPasswordPembimbingModal].forEach((modal) => {
            modal?.addEventListener('click', (event) => {
                if (event.target === modal) {
                    toggleModal(modal, false);
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                toggleModal(addPembimbingModal, false);
                toggleModal(detailPembimbingModal, false);
                toggleModal(editPembimbingModal, false);
                toggleModal(resetPasswordPembimbingModal, false);
            }
        });
    </script>
@endpush
