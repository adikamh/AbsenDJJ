@extends('dashboard.layout')

@section('title', 'Kelola Peserta')
@section('header_title', 'Kelola Peserta')

@push('styles')
    @vite('resources/css/super_admin/peserta.css')
@endpush

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Daftar Peserta</h2>
            <button type="button" class="btn-primary" id="open-add-peserta-modal">
                Tambahkan Peserta
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
                                        data-no-darurat1="{{ $user->no_darurat_1 ?? '-' }}"
                                        data-hubungan-darurat1="{{ $user->hubungan_darurat_1 ?? '-' }}"
                                        data-no-darurat2="{{ $user->no_darurat_2 ?? '-' }}"
                                        data-hubungan-darurat2="{{ $user->hubungan_darurat_2 ?? '-' }}"
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
                                        data-no-darurat1="{{ $user->no_darurat_1 }}"
                                        data-hubungan-darurat1="{{ $user->hubungan_darurat_1 }}"
                                        data-no-darurat2="{{ $user->no_darurat_2 }}"
                                        data-hubungan-darurat2="{{ $user->hubungan_darurat_2 }}"
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
                                        data-nip="{{ $user->nip }}"
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
                    <tr id="table-no-results" style="display: none;">
                        <td colspan="6" class="empty-state">Tidak ada peserta yang cocok dengan kriteria pencarian / filter.</td>
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
                        <input type="password" id="password" name="password" minlength="8" required style="width: 100%; padding: 10px 14px; border-radius: 8px;">
                        <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 6px;">
                            <button type="button" id="btn-generate-password" class="btn-secondary" style="padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 600;" title="Buat Password Otomatis">
                                Auto
                            </button>
                            <button type="button" id="toggle-add-peserta-password" class="password-toggle" style="padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 600; white-space: nowrap; font-family: inherit; font-style: normal; border: 1px solid var(--glass-border); background: rgba(148, 163, 184, 0.14); color: var(--text-primary);">
                                Tampilkan
                            </button>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px; display: block;">Masukkan minimal 8 karakter (bebas) atau klik tombol **Auto** untuk membuat password otomatis acak (8-10 karakter dari NIP & Nama).</span>
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
                        <input type="text" id="instansi" name="instansi" class="autocomplete-instansi" data-suggestions="{{ json_encode($instansi->pluck('nama_instansi')) }}" value="{{ old('instansi') }}" required>
                        @error('instansi', 'storePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="pembimbing_id">Pembimbing</label>
                        <select id="pembimbing_id" name="pembimbing_id" class="searchable-select" required>
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

    <!-- Detail Peserta Modal -->
    <div class="form-modal-backdrop" id="detail-peserta-modal" aria-hidden="true">
        <div class="form-modal detail-modal" role="dialog" aria-modal="true" aria-labelledby="detail-peserta-title">
            <div class="form-modal-header">
                <h3 id="detail-peserta-title">Detail Peserta</h3>
                <button type="button" class="modal-close" id="close-detail-peserta-modal" aria-label="Tutup modal">&times;</button>
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
                    <span>No Darurat 1</span>
                    <strong id="detail-no-darurat-1">-</strong>
                </div>
                <div class="detail-item">
                    <span>Hubungan Darurat 1</span>
                    <strong id="detail-hubungan-darurat-1">-</strong>
                </div>
                <div class="detail-item">
                    <span>No Darurat 2</span>
                    <strong id="detail-no-darurat-2">-</strong>
                </div>
                <div class="detail-item">
                    <span>Hubungan Darurat 2</span>
                    <strong id="detail-hubungan-darurat-2">-</strong>
                </div>
                <div class="detail-item">
                    <span>Instansi</span>
                    <strong id="detail-instansi">-</strong>
                </div>
                <div class="detail-item">
                    <span>Pembimbing</span>
                    <strong id="detail-pembimbing">-</strong>
                </div>
                <div class="detail-item">
                    <span>Status</span>
                    <strong id="detail-status">-</strong>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="close-detail-peserta-action">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Edit Peserta Modal -->
    <div class="form-modal-backdrop {{ $errors->updatePeserta->any() ? 'is-open' : '' }}" id="edit-peserta-modal" aria-hidden="{{ $errors->updatePeserta->any() ? 'false' : 'true' }}">
        <div class="form-modal" role="dialog" aria-modal="true" aria-labelledby="edit-peserta-title">
            <div class="form-modal-header">
                <h3 id="edit-peserta-title">Edit Peserta</h3>
                <button type="button" class="modal-close" id="close-edit-peserta-modal" aria-label="Tutup modal">&times;</button>
            </div>

            <form
                action="{{ old('edit_id') ? url('/super-admin/peserta/' . old('edit_id')) : url('/super-admin/peserta/__ID__') }}"
                method="POST"
                class="modal-form"
                id="edit-peserta-form"
                data-action-template="{{ url('/super-admin/peserta/__ID__') }}"
            >
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id" name="edit_id" value="{{ old('edit_id') }}">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_nip">NIP</label>
                        <input type="text" id="edit_nip" name="nip" value="{{ old('nip') }}" required>
                        @error('nip', 'updatePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_nama_lengkap">Nama</label>
                        <input type="text" id="edit_nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required>
                        @error('nama_lengkap', 'updatePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email" value="{{ old('email') }}" required>
                        @error('email', 'updatePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_no_telepon">No Telepon</label>
                        <input type="text" id="edit_no_telepon" name="no_telepon" value="{{ old('no_telepon') }}" inputmode="numeric" pattern="[0-9]+" required>
                        @error('no_telepon', 'updatePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_alamat">Alamat</label>
                        <textarea id="edit_alamat" name="alamat" rows="3" required>{{ old('alamat') }}</textarea>
                        @error('alamat', 'updatePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_no_darurat_1">No Darurat 1</label>
                        <input type="text" id="edit_no_darurat_1" name="no_darurat_1" value="{{ old('no_darurat_1') }}" inputmode="numeric" pattern="[0-9]+" required>
                        @error('no_darurat_1', 'updatePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_hubungan_darurat_1">Hubungan Darurat 1</label>
                        <input type="text" id="edit_hubungan_darurat_1" name="hubungan_darurat_1" value="{{ old('hubungan_darurat_1') }}" required>
                        @error('hubungan_darurat_1', 'updatePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_no_darurat_2">No Darurat 2</label>
                        <input type="text" id="edit_no_darurat_2" name="no_darurat_2" value="{{ old('no_darurat_2') }}" inputmode="numeric" pattern="[0-9]+" required>
                        @error('no_darurat_2', 'updatePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_hubungan_darurat_2">Hubungan Darurat 2</label>
                        <input type="text" id="edit_hubungan_darurat_2" name="hubungan_darurat_2" value="{{ old('hubungan_darurat_2') }}" required>
                        @error('hubungan_darurat_2', 'updatePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_instansi">Instansi</label>
                        <input type="text" id="edit_instansi" name="instansi" class="autocomplete-instansi" data-suggestions="{{ json_encode($instansi->pluck('nama_instansi')) }}" value="{{ old('instansi') }}" required>
                        @error('instansi', 'updatePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_pembimbing_id">Pembimbing</label>
                        <select id="edit_pembimbing_id" name="pembimbing_id" class="searchable-select" required>
                            <option value="">Pilih Pembimbing</option>
                            @foreach($pembimbing as $user)
                                <option value="{{ $user->id }}" @selected((string) old('pembimbing_id') === (string) $user->id)>
                                    {{ $user->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                        @error('pembimbing_id', 'updatePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_status_aktif">Status</label>
                        <select id="edit_status_aktif" name="status_aktif" required>
                            <option value="1" @selected(old('status_aktif', '1') === '1')>Aktif</option>
                            <option value="0" @selected(old('status_aktif') === '0')>Tidak Aktif</option>
                        </select>
                        @error('status_aktif', 'updatePeserta')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancel-edit-peserta-modal">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Peserta Modal -->
    <div class="form-modal-backdrop {{ $errors->resetPesertaPassword->any() ? 'is-open' : '' }}" id="reset-password-peserta-modal" aria-hidden="{{ $errors->resetPesertaPassword->any() ? 'false' : 'true' }}">
        <div class="form-modal" role="dialog" aria-modal="true" aria-labelledby="reset-password-peserta-title">
            <div class="form-modal-header">
                <h3 id="reset-password-peserta-title">Reset Password Peserta</h3>
                <button type="button" class="modal-close" id="close-reset-password-peserta-modal" aria-label="Tutup modal">&times;</button>
            </div>

            <form
                action="{{ old('reset_id') ? url('/super-admin/peserta/' . old('reset_id') . '/reset-password') : url('/super-admin/peserta/__ID__/reset-password') }}"
                method="POST"
                class="modal-form"
                id="reset-password-peserta-form"
                data-action-template="{{ url('/super-admin/peserta/__ID__/reset-password') }}"
            >
                @csrf
                @method('PUT')
                <input type="hidden" id="reset_id" name="reset_id" value="{{ old('reset_id') }}">

                <div class="detail-list">
                    <div class="detail-item">
                        <span>Peserta</span>
                        <strong id="reset-password-peserta-name">-</strong>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="reset_password">Password Baru</label>
                        <input type="password" id="reset_password" name="password" minlength="8" required style="width: 100%; padding: 10px 14px; border-radius: 8px;">
                        <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 6px;">
                            <button type="button" id="btn-generate-reset-password" class="btn-secondary" style="padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 600;" title="Buat Password Otomatis">
                                Auto
                            </button>
                            <button type="button" id="toggle-reset-peserta-password" class="password-toggle" style="padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 600; white-space: nowrap; font-family: inherit; font-style: normal; border: 1px solid var(--glass-border); background: rgba(148, 163, 184, 0.14); color: var(--text-primary);">
                                Tampilkan
                            </button>
                        </div>
                        @error('password', 'resetPesertaPassword')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="reset_password_confirmation">Konfirmasi Password</label>
                        <input type="password" id="reset_password_confirmation" name="password_confirmation" minlength="8" required style="width: 100%; padding: 10px 14px; border-radius: 8px;">
                        @error('password_confirmation', 'resetPesertaPassword')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancel-reset-password-peserta-modal">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Filter Peserta Modal -->
    <div class="form-modal-backdrop" id="filter-peserta-modal" aria-hidden="true">
        <div class="form-modal" role="dialog" aria-modal="true" aria-labelledby="filter-peserta-title">
            <div class="form-modal-header">
                <h3 id="filter-peserta-title">Filter Peserta</h3>
                <button type="button" class="modal-close" id="close-filter-peserta-modal" aria-label="Tutup modal">&times;</button>
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
                <div class="form-group">
                    <label for="filter-pembimbing">Pembimbing</label>
                    <select id="filter-pembimbing">
                        <option value="">Semua Pembimbing</option>
                        @foreach($pembimbing as $pem)
                            <option value="{{ $pem->nama_lengkap }}">{{ $pem->nama_lengkap }}</option>
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
    @vite('resources/js/super_admin/peserta.js')
@endpush
