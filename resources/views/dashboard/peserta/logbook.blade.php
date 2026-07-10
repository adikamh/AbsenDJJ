@extends('dashboard.layout')

@section('title', 'Logbook Kegiatan')
@section('header_title', 'Logbook Kegiatan')

@push('styles')
    @vite('resources/css/peserta/dashboard.css')
@endpush

@section('content')
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card hover-lift">
            <div class="stat-label">Logbook Disetujui</div>
            <div class="stat-value" style="color: #34d399;">{{ $approvedLogbooksCount }} Berkas</div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Daftar Logbook Kegiatan</h2>
            <div class="header-actions" style="display: flex; gap: 10px;">
                <a href="{{ route('peserta.logbook.pdf') }}" class="btn-secondary" target="_blank" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                    Cetak PDF
                </a>
                <button type="button" class="btn-primary" id="open-add-logbook-modal">
                    Tulis Logbook
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="margin: 15px; padding: 12px 16px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #10b981; font-weight: 500;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" style="margin: 15px; padding: 12px 16px; border-radius: 8px; background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #ef4444; font-weight: 500;">
                {{ session('error') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kegiatan</th>
                        <th>Status</th>
                        <th>Catatan Pembimbing</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logbooks as $logbook)
                        <tr>
                            <td>{{ $logbook->tanggal->format('d M Y') }}</td>
                            <td>
                                <strong>{{ $logbook->kegiatan }}</strong>
                                <div class="logbook-description">{{ $logbook->deskripsi }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $logbook->status_approval === 'Approved' ? 'badge-success' : ($logbook->status_approval === 'Rejected' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ $logbook->status_approval }}
                                </span>
                            </td>
                            <td>
                                <span class="muted-small">{{ $logbook->catatan_pembimbing ?? '-' }}</span>
                            </td>
                            <td>
                                @if($logbook->status_approval === 'Pending')
                                    <div style="display: flex; gap: 8px;">
                                        <button type="button" class="btn-camera btn-camera-primary open-edit-logbook-modal"
                                                data-id="{{ $logbook->id }}"
                                                data-kegiatan="{{ $logbook->kegiatan }}"
                                                data-deskripsi="{{ $logbook->deskripsi }}">
                                            Edit
                                        </button>
                                        <form action="{{ route('peserta.logbook.destroy', $logbook->id) }}" method="POST" class="delete-logbook-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-camera btn-camera-danger btn-delete-logbook">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="muted-small" style="color: var(--text-secondary);">No Action</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">Belum ada entri logbook.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logbooks->hasPages())
            <div style="padding: 20px;">
                {{ $logbooks->links() }}
            </div>
        @endif
    </div>

    {{-- ===== Modal: Tambah Logbook ===== --}}
    <div class="form-modal-backdrop" id="modal-add-logbook">
        <div class="form-modal">
            <div class="form-modal-header">
                <h3>Tulis Logbook Kegiatan Baru</h3>
                <button type="button" class="modal-close" id="close-add-logbook-modal">&times;</button>
            </div>
            <form action="{{ route('peserta.logbook.store') }}" method="POST" class="modal-form">
                @csrf
                <div class="form-group">
                    <label for="tanggal">Tanggal Kegiatan</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label for="kegiatan">Judul Kegiatan / Tugas</label>
                    <input type="text" id="kegiatan" name="kegiatan" placeholder="Contoh: Membuat rancangan database absensi" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi Detail Kegiatan</label>
                    <textarea id="deskripsi" name="deskripsi" rows="5" placeholder="Jelaskan secara rinci kegiatan yang Anda lakukan hari ini..." required></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancel-add-logbook-modal">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Logbook</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Modal: Edit Logbook ===== --}}
    <div class="form-modal-backdrop" id="modal-edit-logbook">
        <div class="form-modal">
            <div class="form-modal-header">
                <h3>Edit Logbook Kegiatan</h3>
                <button type="button" class="modal-close" id="close-edit-logbook-modal">&times;</button>
            </div>
            <form id="edit-logbook-form" action="" method="POST" class="modal-form">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="edit-kegiatan">Judul Kegiatan / Tugas</label>
                    <input type="text" id="edit-kegiatan" name="kegiatan" required>
                </div>
                <div class="form-group">
                    <label for="edit-deskripsi">Deskripsi Detail Kegiatan</label>
                    <textarea id="edit-deskripsi" name="deskripsi" rows="5" required></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancel-edit-logbook-modal">Batal</button>
                    <button type="submit" class="btn-primary">Perbarui Logbook</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/peserta/logbook.js')
@endpush
