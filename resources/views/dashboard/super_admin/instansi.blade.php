@extends('dashboard.layout')

@section('title', 'Kelola Instansi')
@section('header_title', 'Kelola Instansi')

@push('styles')
    @vite('resources/css/super_admin/instansi.css')
@endpush

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Daftar Instansi</h2>
            <button type="button" class="btn-primary" id="open-add-instansi-modal">
                Tambahkan Instansi
            </button>
        </div>
        
        <div class="table-filter-bar">
            <div class="search-box">
                <input type="text" id="table-search" placeholder="Cari nama instansi atau jenis...">
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
                        <th style="width: 80px;">No</th>
                        <th>Nama Instansi</th>
                        <th>Jenis</th>
                        <th>Jumlah Anggota</th>
                        <th style="width: 120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $index = 1;
                    @endphp
                    @forelse($instansi as $inst)
                        <tr>
                            <td>{{ $index++ }}</td>
                            <td><strong>{{ $inst->nama_instansi }}</strong></td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $inst->jenis }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-success">
                                    {{ $inst->users_count }} Anggota
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button
                                        type="button"
                                        class="btn-action btn-action-edit"
                                        data-action="edit-instansi"
                                        data-id="{{ $inst->id }}"
                                        data-nama="{{ $inst->nama_instansi }}"
                                        data-jenis="{{ $inst->jenis }}"
                                        aria-label="Edit {{ $inst->nama_instansi }}"
                                        title="Edit"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                        </svg>
                                    </button>

                                    <form action="{{ route('super-admin.instansi.destroy', $inst) }}" method="POST" class="delete-instansi-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-action-delete" data-name="{{ $inst->nama_instansi }}" aria-label="Hapus {{ $inst->nama_instansi }}" title="Delete">
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
                            <td colspan="5" class="empty-state">Belum ada instansi terdaftar.</td>
                        </tr>
                    @endforelse
                    <tr id="table-no-results" style="display: none;">
                        <td colspan="5" class="empty-state">Tidak ada instansi yang cocok dengan kriteria pencarian / filter.</td>
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

    <!-- Tambah Instansi Modal -->
    <div class="form-modal-backdrop {{ $errors->storeInstansi->any() ? 'is-open' : '' }}" id="add-instansi-modal" aria-hidden="{{ $errors->storeInstansi->any() ? 'false' : 'true' }}">
        <div class="form-modal" role="dialog" aria-modal="true" aria-labelledby="add-instansi-title">
            <div class="form-modal-header">
                <h3 id="add-instansi-title">Tambah Instansi</h3>
                <button type="button" class="modal-close" id="close-add-instansi-modal" aria-label="Tutup modal">&times;</button>
            </div>

            <form action="{{ route('super-admin.instansi.store') }}" method="POST" class="modal-form">
                @csrf

                <div class="form-grid">
                    <div class="form-group" style="grid-column: span 2;">
                        <label for="nama_instansi">Nama Instansi</label>
                        <input type="text" id="nama_instansi" name="nama_instansi" value="{{ old('nama_instansi') }}" required>
                        @error('nama_instansi', 'storeInstansi')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="jenis">Jenis Instansi</label>
                        <select id="jenis" name="jenis" required>
                            <option value="">Pilih Jenis</option>
                            <option value="Universitas" @selected(old('jenis') === 'Universitas')>Universitas</option>
                            <option value="SMK" @selected(old('jenis') === 'SMK')>SMK</option>
                            <option value="Lainnya" @selected(old('jenis') === 'Lainnya')>Lainnya</option>
                        </select>
                        @error('jenis', 'storeInstansi')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancel-add-instansi-modal">Batal</button>
                    <button type="submit" class="btn-primary">Tambah Instansi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Instansi Modal -->
    <div class="form-modal-backdrop {{ $errors->updateInstansi->any() ? 'is-open' : '' }}" id="edit-instansi-modal" aria-hidden="{{ $errors->updateInstansi->any() ? 'false' : 'true' }}">
        <div class="form-modal" role="dialog" aria-modal="true" aria-labelledby="edit-instansi-title">
            <div class="form-modal-header">
                <h3 id="edit-instansi-title">Edit Instansi</h3>
                <button type="button" class="modal-close" id="close-edit-instansi-modal" aria-label="Tutup modal">&times;</button>
            </div>

            <form
                action="{{ old('edit_id') ? url('/super-admin/instansi/' . old('edit_id')) : url('/super-admin/instansi/__ID__') }}"
                method="POST"
                class="modal-form"
                id="edit-instansi-form"
                data-action-template="{{ url('/super-admin/instansi/__ID__') }}"
            >
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id" name="edit_id" value="{{ old('edit_id') }}">

                <div class="form-grid">
                    <div class="form-group" style="grid-column: span 2;">
                        <label for="edit_nama_instansi">Nama Instansi</label>
                        <input type="text" id="edit_nama_instansi" name="nama_instansi" value="{{ old('nama_instansi') }}" required>
                        @error('nama_instansi', 'updateInstansi')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="edit_jenis">Jenis Instansi</label>
                        <select id="edit_jenis" name="jenis" required>
                            <option value="">Pilih Jenis</option>
                            <option value="Universitas" @selected(old('jenis') === 'Universitas')>Universitas</option>
                            <option value="SMK" @selected(old('jenis') === 'SMK')>SMK</option>
                            <option value="Lainnya" @selected(old('jenis') === 'Lainnya')>Lainnya</option>
                        </select>
                        @error('jenis', 'updateInstansi')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancel-edit-instansi-modal">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Filter Instansi Modal -->
    <div class="form-modal-backdrop" id="filter-instansi-modal" aria-hidden="true">
        <div class="form-modal" role="dialog" aria-modal="true" aria-labelledby="filter-instansi-title">
            <div class="form-modal-header">
                <h3 id="filter-instansi-title">Filter Instansi</h3>
                <button type="button" class="modal-close" id="close-filter-instansi-modal" aria-label="Tutup modal">&times;</button>
            </div>

            <div class="modal-form">
                <div class="form-group">
                    <label for="filter-jenis">Jenis Instansi</label>
                    <select id="filter-jenis">
                        <option value="">Semua Jenis</option>
                        @foreach($instansi->pluck('jenis')->unique()->filter() as $jenisType)
                            <option value="{{ $jenisType }}">{{ $jenisType }}</option>
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
    @vite('resources/js/super_admin/instansi.js')
@endpush
