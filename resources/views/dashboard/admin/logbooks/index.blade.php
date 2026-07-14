@extends('dashboard.layout')

@section('title', 'Logbook Anak Didik')
@section('header_title', 'Logbook Kegiatan Anak Didik')

@push('styles')
    @vite('resources/css/admin/interns.css')
@endpush

@section('content')
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="stat-card hover-lift" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; backdrop-filter: blur(10px);">
            <div class="stat-label" style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">Logbook Pending</div>
            <div class="stat-value" style="font-size: 1.6rem; font-weight: 700; color: #fbbf24; margin-top: 6px;">{{ $pendingLogbooksCount }} Item</div>
        </div>
        <div class="stat-card hover-lift" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; backdrop-filter: blur(10px);">
            <div class="stat-label" style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">Logbook Disetujui</div>
            <div class="stat-value" style="font-size: 1.6rem; font-weight: 700; color: #34d399; margin-top: 6px;">{{ $approvedLogbooksCount }} Item</div>
        </div>
        <div class="stat-card hover-lift" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; backdrop-filter: blur(10px);">
            <div class="stat-label" style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">Logbook Ditolak</div>
            <div class="stat-value" style="font-size: 1.6rem; font-weight: 700; color: #f87171; margin-top: 6px;">{{ $rejectedLogbooksCount }} Item</div>
        </div>
    </div>

    <!-- Cetak & Rekap Logbook Card -->
    <div class="content-card" style="margin-bottom: 30px;">
        <div class="card-header" style="padding: 20px; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: flex-start !important; align-items: center; gap: 10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            <h2 class="card-title" style="margin: 0; font-size: 1.1rem; color: var(--text-primary); text-align: left;">Cetak & Rekap Logbook Anak Didik</h2>
        </div>
        <div style="padding: 24px;">
            <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; align-items: flex-end;">
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; letter-spacing: 0.5px;">PILIH ANAK DIDIK</label>
                    <select name="user_id" required style="padding: 10px 14px; font-size: 0.88rem; border: 1px solid var(--glass-border); border-radius: 8px; background: rgba(0,0,0,0.15); color: var(--text-primary); width: 100%; transition: all 0.3s ease; outline: none; cursor: pointer;">
                        <option value="" style="background: #1a1a2e; color: #fff;">-- Pilih Peserta --</option>
                        @foreach($guidedInterns as $intern)
                            <option value="{{ $intern->id }}" style="background: #1a1a2e; color: #fff;">{{ $intern->nama_lengkap }} ({{ $intern->instansi?->nama_instansi ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; letter-spacing: 0.5px;">PILIH BULAN</label>
                    <select name="month" style="padding: 10px 14px; font-size: 0.88rem; border: 1px solid var(--glass-border); border-radius: 8px; background: rgba(0,0,0,0.15); color: var(--text-primary); width: 100%; transition: all 0.3s ease; outline: none; cursor: pointer;">
                        <option value="" style="background: #1a1a2e; color: #fff;">Semua Bulan</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" style="background: #1a1a2e; color: #fff;">{{ \Carbon\Carbon::create(now()->year, $m, 1)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; letter-spacing: 0.5px;">PILIH TAHUN</label>
                    <select name="year" style="padding: 10px 14px; font-size: 0.88rem; border: 1px solid var(--glass-border); border-radius: 8px; background: rgba(0,0,0,0.15); color: var(--text-primary); width: 100%; transition: all 0.3s ease; outline: none; cursor: pointer;">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }} style="background: #1a1a2e; color: #fff;">{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <button type="submit" formaction="{{ route('peserta.logbook.pdf') }}" formtarget="_blank" class="btn-primary hover-lift" style="padding: 10px 20px; font-size: 0.88rem; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: var(--accent-primary); border-color: var(--accent-primary); font-weight: 600; flex: 1; min-width: 140px; height: 42px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Cetak PDF
                    </button>
                    <button type="submit" formaction="{{ route('peserta.logbook.csv') }}" class="btn-secondary hover-lift" style="padding: 10px 20px; font-size: 0.88rem; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; flex: 1; min-width: 140px; height: 42px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Rekap CSV
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logbooks List Card (Unified Search, Filter, and Table) -->
    <div class="content-card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; padding: 20px; border-bottom: 1px solid var(--glass-border);">
            <h2 class="card-title" style="margin: 0;">Daftar Logbook Intern</h2>
            
            <form action="{{ route('admin.logbooks') }}" method="GET" style="margin: 0; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <!-- Search input -->
                <input type="text" name="search" value="{{ request('search') }}" class="filter-input" placeholder="Cari nama/kegiatan..." style="padding: 8px 12px; font-size: 0.85rem; width: 220px; border: 1px solid var(--glass-border); border-radius: 6px; background: rgba(255,255,255,0.05); color: var(--text-primary);" onchange="this.form.submit()">
                
                <!-- Status approval select -->
                <select name="status_approval" class="filter-select" style="padding: 8px 12px; font-size: 0.85rem; border: 1px solid var(--glass-border); border-radius: 6px; background: rgba(0,0,0,0.2); color: var(--text-primary); width: 140px;" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="Pending" {{ request('status_approval') === 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Approved" {{ request('status_approval') === 'Approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="Rejected" {{ request('status_approval') === 'Rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>

                <button type="submit" class="btn-primary" style="padding: 8px 16px; font-size: 0.85rem; border-radius: 6px; cursor: pointer;">Filter</button>
                @if(request()->anyFilled(['search', 'status_approval']))
                    <a href="{{ route('admin.logbooks') }}" class="btn-secondary" style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none; border-radius: 6px; display: inline-block;">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Intern</th>
                        <th>Kegiatan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logbooks as $logbook)
                        <tr>
                            <td>{{ $logbook->tanggal->format('d M Y') }}</td>
                            <td>
                                <strong>{{ $logbook->user?->nama_lengkap }}</strong>
                                <br>
                                <span class="muted-small">{{ $logbook->user?->instansi?->nama_instansi ?? '-' }}</span>
                            </td>
                            <td><strong>{{ $logbook->kegiatan }}</strong></td>
                            <td>
                                <span class="badge {{ $logbook->status_approval === 'Approved' ? 'badge-success' : ($logbook->status_approval === 'Rejected' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ $logbook->status_approval }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; flex-direction: column;">
                                    <button type="button" class="badge badge-info logbook-detail-trigger" style="border: none; cursor: pointer; padding: 4px 8px; font-weight: 600; width: 100%; text-align: center;"
                                        data-id="{{ $logbook->id }}"
                                        data-name="{{ $logbook->user?->nama_lengkap }}"
                                        data-instansi="{{ $logbook->user?->instansi?->nama_instansi ?? '-' }}"
                                        data-date="{{ $logbook->tanggal->format('d M Y') }}"
                                        data-kegiatan="{{ $logbook->kegiatan }}"
                                        data-description="{{ $logbook->deskripsi }}"
                                        data-tags="{{ $logbook->tags }}"
                                        data-status="{{ $logbook->status_approval }}"
                                        data-comment="{{ $logbook->catatan_pembimbing ?? '-' }}">
                                        Detail
                                    </button>
                                    @if($logbook->status_approval === 'Pending')
                                        <button type="button" class="badge badge-success logbook-approve-btn" style="border: none; cursor: pointer; padding: 4px 8px; font-weight: 600; width: 100%; text-align: center;" data-action-url="{{ route('admin.logbook.approve', $logbook->id) }}">Setujui</button>
                                        <button type="button" class="badge badge-danger logbook-reject-btn" style="border: none; cursor: pointer; padding: 4px 8px; font-weight: 600; width: 100%; text-align: center;" data-action-url="{{ route('admin.logbook.reject', $logbook->id) }}">Tolak</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">Tidak ada logbook anak didik yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logbooks->hasPages())
            {{ $logbooks->links('partials.pagination') }}
        @endif
    </div>

    <!-- Logbook Detail Modal -->
    <div id="logbook-detail-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 16px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 30px; position: relative; color: var(--text-primary); box-shadow: 0 15px 35px rgba(0,0,0,0.5); backdrop-filter: blur(20px);">
            <button id="close-modal-btn" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer;">&times;</button>
            
            <div style="margin-bottom: 24px; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px;">
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-primary);">Detail Logbook Kegiatan</h3>
                <p id="modal-intern-info" class="muted-small" style="margin: 6px 0 0 0; color: var(--text-secondary);"></p>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Tanggal</span>
                    <p id="modal-date" style="margin: 6px 0 0 0; font-weight: 500;"></p>
                </div>
                
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Kegiatan</span>
                    <p id="modal-kegiatan" style="margin: 6px 0 0 0; font-weight: 700; font-size: 1.1rem; color: var(--accent-primary);"></p>
                </div>
                
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Deskripsi Detail</span>
                    <p id="modal-description" style="margin: 6px 0 0 0; white-space: pre-wrap; line-height: 1.6; color: var(--text-primary); background: rgba(255,255,255,0.02); padding: 12px; border-radius: 8px; border: 1px solid var(--glass-border);"></p>
                </div>

                <div>
                    <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Tags / Label</span>
                    <div id="modal-tags" style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 6px;"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Status Approval</span>
                        <div style="margin-top: 6px;">
                            <span id="modal-status" class="badge"></span>
                        </div>
                    </div>
                    <div>
                        <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Catatan Pembimbing</span>
                        <p id="modal-comment" style="margin: 6px 0 0 0; color: var(--text-primary); font-style: italic;"></p>
                    </div>
                </div>
            </div>

            <!-- Action buttons if pending -->
            <div id="modal-actions" style="margin-top: 30px; border-top: 1px solid var(--glass-border); padding-top: 20px; display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" id="modal-approve-btn" class="btn-primary" style="padding: 10px 20px; border-radius: 8px; background: #10b981; border: none; font-weight: 600; cursor: pointer; color: #fff;">Setujui Logbook</button>
                <button type="button" id="modal-reject-btn" class="btn-secondary" style="padding: 10px 20px; border-radius: 8px; border: 1px solid #ef4444; color: #ef4444; background: none; font-weight: 600; cursor: pointer;">Tolak Logbook</button>
            </div>
        </div>
    </div>

    <!-- Action Confirmation Modal -->
    <div id="action-confirm-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 1100; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 16px; width: 100%; max-width: 450px; padding: 24px; position: relative; color: var(--text-primary); box-shadow: 0 15px 35px rgba(0,0,0,0.5); backdrop-filter: blur(20px);">
            <h3 id="confirm-modal-title" style="margin: 0 0 12px 0; font-size: 1.15rem; font-weight: 700; color: var(--text-primary);">Konfirmasi Tindakan</h3>
            <p id="confirm-modal-text" style="margin: 0 0 20px 0; font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5;"></p>
            
            <form id="confirm-action-form" method="POST" style="display: flex; flex-direction: column; gap: 15px; margin: 0;">
                @csrf
                <div id="confirm-comment-group" style="display: flex; flex-direction: column; gap: 8px;">
                    <label id="confirm-comment-label" for="confirm-catatan" style="font-weight: 600; font-size: 0.85rem; color: var(--text-secondary);">Catatan Pembimbing</label>
                    <textarea id="confirm-catatan" name="catatan_pembimbing" placeholder="Tulis catatan (opsional)..." style="padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.05); color: var(--text-primary); width: 100%; min-height: 80px; resize: vertical; font-family: inherit; font-size: 0.9rem;"></textarea>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px;">
                    <button type="button" id="confirm-modal-cancel" class="btn-secondary" style="padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; cursor: pointer;">Batal</button>
                    <button type="submit" id="confirm-modal-submit" class="btn-primary" style="padding: 8px 16px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; color: #fff; font-size: 0.85rem;"></button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/admin/logbooks.js')
@endpush
