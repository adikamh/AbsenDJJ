@extends('dashboard.layout')

@section('title', 'Kelola Pembimbing')
@section('header_title', 'Kelola Pembimbing')

@push('styles')
    @vite('resources/css/super_admin_pembimbing.css')
@endpush

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Daftar Pembimbing</h2>
            <button type="button" class="btn-primary" id="open-add-pembimbing-modal">
                Tambahkan Pembimbing
            </button>
        </div>
        <div class="table-filter-bar">
            <div class="search-box">
                <input type="text" id="table-search" placeholder="Cari NIP, nama, email, atau instansi...">
            </div>
            <button type="button" class="btn-secondary" id="open-filter-modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="filter-icon">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                <span>Filter</span>
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
                    <tr id="table-no-results" style="display: none;">
                        <td colspan="5" class="empty-state">Tidak ada pembimbing yang cocok dengan kriteria pencarian / filter.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination Section -->
        <div class="table-pagination" id="table-pagination" style="display: none;">
            <div class="pagination-info">
                Menampilkan <span id="pagination-start">0</span>-<span id="pagination-end">0</span> dari <span id="pagination-total">0</span> data
            </div>
            <div class="pagination-controls" id="pagination-controls">
                <!-- Page buttons will be generated by JS -->
            </div>
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
                        <input type="text" id="instansi" name="instansi" class="autocomplete-instansi" data-suggestions="{{ json_encode($instansi->pluck('nama_instansi')) }}" value="{{ old('instansi') }}" required>
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
                        <input type="text" id="edit_instansi" name="instansi" class="autocomplete-instansi" data-suggestions="{{ json_encode($instansi->pluck('nama_instansi')) }}" value="{{ old('instansi') }}" required>
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

    <!-- Filter Pembimbing Modal -->
    <div class="form-modal-backdrop" id="filter-pembimbing-modal" aria-hidden="true">
        <div class="form-modal" role="dialog" aria-modal="true" aria-labelledby="filter-pembimbing-title">
            <div class="form-modal-header">
                <h3 id="filter-pembimbing-title">Filter Pembimbing</h3>
                <button type="button" class="modal-close" id="close-filter-pembimbing-modal" aria-label="Tutup modal">&times;</button>
            </div>

            <div class="modal-form">
                <div class="form-group">
                    <label for="filter-status">Status</label>
                    <select id="filter-status">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filter-instansi">Instansi</label>
                    <select id="filter-instansi">
                        <option value="">Semua Instansi</option>
                        @foreach($instansi as $inst)
                            <option value="{{ $inst->nama_instansi }}">{{ $inst->nama_instansi }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="reset-table-filters">Reset Filter</button>
                    <button type="button" class="btn-primary" id="apply-table-filters">Terapkan Filter</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/super_admin_pembimbing.js')
@endpush
