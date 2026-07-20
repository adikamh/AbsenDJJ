@extends('dashboard.layout')

@section('title', 'Logbook Kegiatan')
@section('header_title', 'Logbook Kegiatan')

@push('styles')
    @vite(['resources/css/peserta/dashboard.css', 'resources/css/logbook.css'])
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
                <a href="{{ route('peserta.logbook.csv') }}" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Ekspor CSV
                </a>
                <button type="button" class="btn-primary" id="open-add-logbook-modal">
                    Tulis Logbook
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="margin: 15px 20px; padding: 12px 16px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #10b981; font-weight: 500;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" style="margin: 15px 20px; padding: 12px 16px; border-radius: 8px; background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #ef4444; font-weight: 500;">
                {{ session('error') }}
            </div>
        @endif

        <!-- Integrated Filter Form -->
        <form action="{{ route('peserta.logbook') }}" method="GET" class="filter-row">
            <div class="filter-item">
                <label for="search">Cari</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" class="filter-search" placeholder="Cari kegiatan/deskripsi/tag... (Tekan Enter)" onchange="this.form.submit()">
            </div>
            <div class="filter-item">
                <label for="status_approval">Status</label>
                <select id="status_approval" name="status_approval" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="Draft" {{ request('status_approval') === 'Draft' ? 'selected' : '' }}>Draft (Simpan Sementara)</option>
                    <option value="Pending" {{ request('status_approval') === 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Approved" {{ request('status_approval') === 'Approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="Rejected" {{ request('status_approval') === 'Rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="filter-item">
                <label for="tanggal">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ request('tanggal') }}" class="filter-select" onchange="this.form.submit()" style="color: var(--text-primary); cursor: pointer; min-height: 38px;">
            </div>
            <div class="filter-item">
                <label for="bulan">Bulan</label>
                <select id="bulan" name="bulan" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Bulan</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(now()->year, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="filter-item">
                <label for="tahun">Tahun</label>
                <select id="tahun" name="tahun" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Tahun</option>
                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
            @if(request()->anyFilled(['search', 'status_approval', 'tanggal', 'bulan', 'tahun']))
                <a href="{{ route('peserta.logbook') }}" class="btn-filter-reset">Reset Filter</a>
            @endif
        </form>

        <div class="table-responsive" id="logbook-table-container">
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
                                
                                @if($logbook->tags)
                                    <div style="margin: 6px 0; display: flex; gap: 6px; flex-wrap: wrap;">
                                        @foreach(explode(',', $logbook->tags) as $tag)
                                            @if(trim($tag) !== '')
                                                <span class="tag-badge">#{{ trim($tag) }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                <div class="logbook-description" style="margin-top: 4px;">{{ $logbook->deskripsi }}</div>
                            </td>
                            <td>
                                @if($logbook->status_approval === 'Approved')
                                    <span class="badge badge-success">Disetujui</span>
                                @elseif($logbook->status_approval === 'Rejected')
                                    <span class="badge badge-danger">Ditolak</span>
                                @elseif($logbook->status_approval === 'Revisi')
                                    <span class="badge badge-warning" style="background-color: #fbbf24 !important; border-color: #fbbf24 !important; color: #1e1b4b !important;">Revisi</span>
                                @elseif($logbook->status_approval === 'Draft')
                                    <span class="draft-badge">Draft</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($logbook->status_approval === 'Revisi')
                                    <span style="color: #fbbf24; font-weight: 600; display: block; margin-bottom: 2px;">⚠️ Perlu Revisi:</span>
                                    <span style="color: var(--text-primary); font-style: italic;">"{{ $logbook->catatan_pembimbing }}"</span>
                                @else
                                    <span class="muted-small">{{ !empty($logbook->catatan_pembimbing) ? $logbook->catatan_pembimbing : '-' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($logbook->status_approval === 'Draft')
                                    <div style="display: flex; gap: 8px;">
                                        <button type="button" class="btn-camera btn-camera-primary open-edit-logbook-modal"
                                                data-id="{{ $logbook->logbook_code }}"
                                                data-kegiatan="{{ $logbook->kegiatan }}"
                                                data-tags="{{ $logbook->tags }}"
                                                data-deskripsi="{{ $logbook->deskripsi }}">
                                            Edit
                                        </button>
                                        <form action="{{ route('peserta.logbook.destroy', $logbook->logbook_code) }}" method="POST" class="delete-logbook-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-camera btn-camera-danger btn-delete-logbook">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                @elseif($logbook->status_approval === 'Revisi')
                                    <div style="display: flex; gap: 8px;">
                                        <button type="button" class="btn-camera btn-camera-primary open-edit-logbook-modal"
                                                data-id="{{ $logbook->logbook_code }}"
                                                data-kegiatan="{{ $logbook->kegiatan }}"
                                                data-tags="{{ $logbook->tags }}"
                                                data-deskripsi="{{ $logbook->deskripsi }}"
                                                style="background: #fbbf24 !important; border-color: #fbbf24 !important; color: #1e1b4b !important; font-weight: 600;">
                                            Edit Revisi
                                        </button>
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
            {{ $logbooks->links('partials.pagination') }}
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
                    <label>Tanggal Kegiatan</label>
                    <input type="text" value="{{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}" disabled style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--glass-border); font-weight: 500;">
                    <input type="hidden" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label for="kegiatan">Judul Kegiatan / Tugas</label>
                    <input type="text" id="kegiatan" name="kegiatan" placeholder="Contoh: Membuat rancangan database absensi" required>
                </div>
                <div class="form-group">
                    <label for="tags">Tag Kegiatan (Pisahkan dengan koma)</label>
                    <input type="text" id="tags" name="tags" placeholder="Contoh: laravel, mysql, refactor">
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi Detail Kegiatan</label>
                    <textarea id="deskripsi" name="deskripsi" rows="5" placeholder="Jelaskan secara rinci kegiatan yang Anda lakukan hari ini..." required></textarea>
                </div>
                <div class="modal-actions" style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn-secondary" id="cancel-add-logbook-modal">Batal</button>
                    <button type="submit" name="action" value="draft" class="btn-secondary" style="border: 1px solid var(--accent-primary); color: var(--accent-primary);">Simpan Draft</button>
                    <button type="submit" name="action" value="submit" class="btn-primary">Kirim ke Pembimbing</button>
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
                    <label for="edit-tags">Tag Kegiatan (Pisahkan dengan koma)</label>
                    <input type="text" id="edit-tags" name="tags" placeholder="Contoh: laravel, mysql, refactor">
                </div>
                <div class="form-group">
                    <label for="edit-deskripsi">Deskripsi Detail Kegiatan</label>
                    <textarea id="edit-deskripsi" name="deskripsi" rows="5" required></textarea>
                </div>
                <div class="modal-actions" style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn-secondary" id="cancel-edit-logbook-modal">Batal</button>
                    <button type="submit" name="action" value="draft" class="btn-secondary" style="border: 1px solid var(--accent-primary); color: var(--accent-primary);">Simpan Draft</button>
                    <button type="submit" name="action" value="submit" class="btn-primary">Kirim ke Pembimbing</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/logbook.js')
    <div id="logbook-dates-data" data-dates='@json($existingDates)'></div>
    <div id="today-logbook-data" 
         data-exists="{{ $todayLogbook ? 'true' : 'false' }}"
         data-id="{{ $todayLogbook?->logbook_code }}"
         data-status="{{ $todayLogbook?->status_approval }}"
         data-kegiatan="{{ $todayLogbook?->kegiatan }}"
         data-tags="{{ $todayLogbook?->tags }}"
         data-deskripsi="{{ $todayLogbook?->deskripsi }}">
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btnOpenAddLogbook = document.getElementById('open-add-logbook-modal');
            const dataEl = document.getElementById('logbook-dates-data');

            // Auto open write logbook modal if write-logbook parameter is present
            if (btnOpenAddLogbook) {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('write-logbook')) {
                    btnOpenAddLogbook.click();
                }
            }

            if (btnOpenAddLogbook && dataEl) {
                btnOpenAddLogbook.addEventListener('click', (e) => {
                    const todayDataEl = document.getElementById('today-logbook-data');
                    if (todayDataEl && todayDataEl.getAttribute('data-exists') === 'true') {
                        e.stopImmediatePropagation();
                        e.preventDefault();

                        const status = todayDataEl.getAttribute('data-status');
                        if (status === 'Draft') {
                            // Automatically open Edit Modal with today's draft details
                            const id = todayDataEl.getAttribute('data-id');
                            const kegiatan = todayDataEl.getAttribute('data-kegiatan');
                            const tags = todayDataEl.getAttribute('data-tags') || '';
                            const deskripsi = todayDataEl.getAttribute('data-deskripsi');

                            const editLogbookForm = document.getElementById('edit-logbook-form');
                            const editKegiatanInput = document.getElementById('edit-kegiatan');
                            const editTagsInput = document.getElementById('edit-tags');
                            const editDeskripsiInput = document.getElementById('edit-deskripsi');
                            const modalEditLogbook = document.getElementById('modal-edit-logbook');

                            if (editLogbookForm) editLogbookForm.action = `/peserta/logbook/${id}`;
                            if (editKegiatanInput) editKegiatanInput.value = kegiatan;
                            if (editTagsInput) editTagsInput.value = tags;
                            if (editDeskripsiInput) editDeskripsiInput.value = deskripsi;

                            if (modalEditLogbook) {
                                modalEditLogbook.classList.add('is-open');
                            }
                        } else {
                            // Logbook already submitted (Pending / Approved / Rejected)
                            if (window.Swal) {
                                const isLight = document.documentElement.getAttribute('data-theme') === 'light';
                                window.Swal.fire({
                                    background: isLight ? '#ffffff' : '#1e293b',
                                    color: isLight ? '#0f172a' : '#f8fafc',
                                    confirmButtonColor: isLight ? '#2e4085' : '#ffcc33',
                                    icon: 'error',
                                    title: 'Logbook Sudah Terkirim',
                                    text: 'Anda sudah mengirimkan logbook untuk hari ini. Logbook yang sudah diajukan/disetujui tidak dapat diubah kembali.',
                                    confirmButtonText: 'Mengerti'
                                });
                            } else {
                                alert('Anda sudah mengirimkan logbook untuk hari ini. Logbook yang sudah diajukan/disetujui tidak dapat diubah kembali.');
                            }
                        }
                    }
                }, true); // capture phase
            }

            const form = document.querySelector('#modal-add-logbook form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    const dateInput = document.getElementById('tanggal');
                    if (dateInput) {
                        const selectedDate = dateInput.value;
                        if (dataEl) {
                            const existingDates = JSON.parse(dataEl.getAttribute('data-dates') || '[]');
                            if (existingDates.includes(selectedDate)) {
                                e.preventDefault();
                                e.stopPropagation();

                                if (window.Swal) {
                                    const isLight = document.documentElement.getAttribute('data-theme') === 'light';
                                    window.Swal.fire({
                                        background: isLight ? '#ffffff' : '#1e293b',
                                        color: isLight ? '#0f172a' : '#f8fafc',
                                        confirmButtonColor: isLight ? '#2e4085' : '#ffcc33',
                                        icon: 'error',
                                        title: 'Logbook Sudah Ada',
                                        text: 'Anda sudah mengisi logbook untuk tanggal tersebut. Hanya diperbolehkan satu logbook per hari.',
                                        confirmButtonText: 'Mengerti'
                                    });
                                } else {
                                    alert('Anda sudah mengisi logbook untuk tanggal tersebut. Hanya diperbolehkan satu logbook per hari.');
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
