@extends('dashboard.layout')

@section('title', 'Kelola Peserta')
@section('header_title', 'Kelola Peserta')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Daftar Peserta</h2>
            <button type="button" class="btn-primary" id="open-add-peserta-modal">
                Tambahkan Peserta
            </button>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Instansi</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peserta as $user)
                        <tr>
                            <td>{{ $user->nip ?? '-' }}</td>
                            <td><strong>{{ $user->nama_lengkap }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->instansi?->nama_instansi ?? '-' }}</td>
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
                                        data-action="view-peserta"
                                        data-nip="{{ $user->nip ?? '-' }}"
                                        data-nama="{{ $user->nama_lengkap }}"
                                        data-email="{{ $user->email }}"
                                        data-telepon="{{ $user->no_telepon ?? '-' }}"
                                        data-alamat="{{ $user->alamat ?? '-' }}"
                                        data-no-darurat-1="{{ $user->no_darurat_1 ?? '-' }}"
                                        data-hubungan-darurat-1="{{ $user->hubungan_darurat_1 ?? '-' }}"
                                        data-no-darurat-2="{{ $user->no_darurat_2 ?? '-' }}"
                                        data-hubungan-darurat-2="{{ $user->hubungan_darurat_2 ?? '-' }}"
                                        data-pembimbing="{{ $user->pembimbing?->nama_lengkap ?? '-' }}"
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
                                        data-action="edit-peserta"
                                        data-id="{{ $user->id }}"
                                        data-nip="{{ $user->nip }}"
                                        data-nama="{{ $user->nama_lengkap }}"
                                        data-email="{{ $user->email }}"
                                        data-telepon="{{ $user->no_telepon }}"
                                        data-alamat="{{ $user->alamat }}"
                                        data-no-darurat-1="{{ $user->no_darurat_1 }}"
                                        data-hubungan-darurat-1="{{ $user->hubungan_darurat_1 }}"
                                        data-no-darurat-2="{{ $user->no_darurat_2 }}"
                                        data-hubungan-darurat-2="{{ $user->hubungan_darurat_2 }}"
                                        data-pembimbing-id="{{ $user->pembimbing_id }}"
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
                                        data-action="reset-password-peserta"
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

                                    <form action="{{ route('super-admin.peserta.destroy', $user) }}" method="POST" class="delete-peserta-form">
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
                            <td colspan="6" class="empty-state">Belum ada peserta terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="form-modal-backdrop {{ $errors->storePeserta->any() ? 'is-open' : '' }}" id="add-peserta-modal" aria-hidden="{{ $errors->storePeserta->any() ? 'false' : 'true' }}">
        <div class="form-modal" role="dialog" aria-modal="true" aria-labelledby="add-peserta-title">
            <div class="form-modal-header">
                <h3 id="add-peserta-title">Tambah Peserta</h3>
                <button type="button" class="modal-close" id="close-add-peserta-modal" aria-label="Tutup modal">&times;</button>
            </div>

            <form action="{{ route('super-admin.peserta.store') }}" method="POST" class="modal-form">
                @csrf

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nip">NIP</label>
                        <input type="text" id="nip" name="nip" value="{{ old('nip') }}" required>
                        @error('nip', 'storePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="nama_lengkap">Nama</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required>
                        @error('nama_lengkap', 'storePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                        @error('email', 'storePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="no_telepon">No Telepon</label>
                        <input type="text" id="no_telepon" name="no_telepon" value="{{ old('no_telepon') }}" inputmode="numeric" pattern="[0-9]+" required>
                        @error('no_telepon', 'storePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="alamat">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="3" required>{{ old('alamat') }}</textarea>
                        @error('alamat', 'storePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" minlength="8" required>
                            <button type="button" class="password-toggle" id="toggle-add-peserta-password">
                                Tampilkan
                            </button>
                        </div>
                        @error('password', 'storePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="no_darurat_1">No Darurat 1</label>
                        <input type="text" id="no_darurat_1" name="no_darurat_1" value="{{ old('no_darurat_1') }}" inputmode="numeric" pattern="[0-9]+" required>
                        @error('no_darurat_1', 'storePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="hubungan_darurat_1">Hubungan Darurat 1</label>
                        <input type="text" id="hubungan_darurat_1" name="hubungan_darurat_1" value="{{ old('hubungan_darurat_1') }}" required>
                        @error('hubungan_darurat_1', 'storePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="no_darurat_2">No Darurat 2</label>
                        <input type="text" id="no_darurat_2" name="no_darurat_2" value="{{ old('no_darurat_2') }}" inputmode="numeric" pattern="[0-9]+" required>
                        @error('no_darurat_2', 'storePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="hubungan_darurat_2">Hubungan Darurat 2</label>
                        <input type="text" id="hubungan_darurat_2" name="hubungan_darurat_2" value="{{ old('hubungan_darurat_2') }}" required>
                        @error('hubungan_darurat_2', 'storePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="instansi">Instansi</label>
                        <input type="text" id="instansi" name="instansi" value="{{ old('instansi') }}" required>
                        @error('instansi', 'storePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="pembimbing_id">Pembimbing</label>
                        <select id="pembimbing_id" name="pembimbing_id" required>
                            <option value="">Pilih Pembimbing</option>
                            @foreach($pembimbing as $user)
                                <option value="{{ $user->id }}" @selected((string) old('pembimbing_id') === (string) $user->id)>
                                    {{ $user->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                        @error('pembimbing_id', 'storePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status_aktif">Status</label>
                        <select id="status_aktif" name="status_aktif" required>
                            <option value="1" @selected(old('status_aktif', '1') === '1')>Aktif</option>
                            <option value="0" @selected(old('status_aktif') === '0')>Tidak Aktif</option>
                        </select>
                        @error('status_aktif', 'storePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancel-add-peserta-modal">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Peserta</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const addPesertaModal = document.getElementById('add-peserta-modal');
        const openAddPesertaModal = document.getElementById('open-add-peserta-modal');
        const closeAddPesertaModal = document.getElementById('close-add-peserta-modal');
        const cancelAddPesertaModal = document.getElementById('cancel-add-peserta-modal');
        const addPesertaPassword = document.getElementById('password');
        const toggleAddPesertaPassword = document.getElementById('toggle-add-peserta-password');

        function togglePesertaModal(isOpen) {
            addPesertaModal?.classList.toggle('is-open', isOpen);
            addPesertaModal?.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        }

        openAddPesertaModal?.addEventListener('click', () => togglePesertaModal(true));
        closeAddPesertaModal?.addEventListener('click', () => togglePesertaModal(false));
        cancelAddPesertaModal?.addEventListener('click', () => togglePesertaModal(false));
        toggleAddPesertaPassword?.addEventListener('click', () => {
            if (! addPesertaPassword) {
                return;
            }

            const isHidden = addPesertaPassword.type === 'password';
            addPesertaPassword.type = isHidden ? 'text' : 'password';
            toggleAddPesertaPassword.textContent = isHidden ? 'Sembunyikan' : 'Tampilkan';
        });

        addPesertaModal?.addEventListener('click', (event) => {
            if (event.target === addPesertaModal) {
                togglePesertaModal(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                togglePesertaModal(false);
            }
        });
    </script>
@endpush
