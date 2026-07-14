@extends('dashboard.layout')

@section('title', 'Izin & Sakit Anak Didik')
@section('header_title', 'Persetujuan Izin & Sakit Anak Didik')

@push('styles')
    @vite('resources/css/admin/interns.css')
@endpush

@section('content')
    <!-- Stats Grid (Izin & Sakit) -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="stat-card hover-lift" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; backdrop-filter: blur(10px);">
            <div class="stat-label" style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">Izin/Sakit Pending</div>
            <div class="stat-value" style="font-size: 1.6rem; font-weight: 700; color: #fbbf24; margin-top: 6px;">{{ $pendingLeavesCount }} Item</div>
        </div>
        <div class="stat-card hover-lift" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; backdrop-filter: blur(10px);">
            <div class="stat-label" style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">Izin/Sakit Disetujui</div>
            <div class="stat-value" style="font-size: 1.6rem; font-weight: 700; color: #34d399; margin-top: 6px;">{{ $approvedLeavesCount }} Item</div>
        </div>
        <div class="stat-card hover-lift" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; backdrop-filter: blur(10px);">
            <div class="stat-label" style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">Izin/Sakit Ditolak</div>
            <div class="stat-value" style="font-size: 1.6rem; font-weight: 700; color: #f87171; margin-top: 6px;">{{ $rejectedLeavesCount }} Item</div>
        </div>
    </div>

    <!-- Leaves List Card (Unified Search, Filter, and Table) -->
    <div class="content-card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; padding: 20px; border-bottom: 1px solid var(--glass-border);">
            <h2 class="card-title" style="margin: 0;">Daftar Pengajuan Izin / Sakit</h2>
            
            <form action="{{ route('admin.leaves') }}" method="GET" style="margin: 0; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <!-- Search input -->
                <input type="text" name="search" value="{{ request('search') }}" class="filter-input" placeholder="Cari nama/alasan..." style="padding: 8px 12px; font-size: 0.85rem; width: 200px; border: 1px solid var(--glass-border); border-radius: 6px; background: rgba(255,255,255,0.05); color: var(--text-primary);" onchange="this.form.submit()">
                
                <!-- Status approval select -->
                <select name="status_approval" class="filter-select" style="padding: 8px 12px; font-size: 0.85rem; border: 1px solid var(--glass-border); border-radius: 6px; background: rgba(0,0,0,0.2); color: var(--text-primary); width: 140px;" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="Pending" {{ request('status_approval') === 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Approved" {{ request('status_approval') === 'Approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="Rejected" {{ request('status_approval') === 'Rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>

                <!-- Jenis select -->
                <select name="jenis" class="filter-select" style="padding: 8px 12px; font-size: 0.85rem; border: 1px solid var(--glass-border); border-radius: 6px; background: rgba(0,0,0,0.2); color: var(--text-primary); width: 130px;" onchange="this.form.submit()">
                    <option value="">Semua Jenis</option>
                    <option value="Izin" {{ request('jenis') === 'Izin' ? 'selected' : '' }}>Izin</option>
                    <option value="Sakit" {{ request('jenis') === 'Sakit' ? 'selected' : '' }}>Sakit</option>
                </select>

                <button type="submit" class="btn-primary" style="padding: 8px 16px; font-size: 0.85rem; border-radius: 6px; cursor: pointer;">Filter</button>
                @if(request()->anyFilled(['search', 'status_approval', 'jenis']))
                    <a href="{{ route('admin.leaves') }}" class="btn-secondary" style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none; border-radius: 6px; display: inline-block;">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama Intern</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Jenis</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                        <tr>
                            <td>
                                <strong>{{ $leave->user?->nama_lengkap }}</strong>
                                <br>
                                <span class="muted-small">{{ $leave->user?->instansi?->nama_instansi ?? '-' }}</span>
                            </td>
                            <td>{{ $leave->tanggal_mulai->format('d M Y') }}</td>
                            <td>{{ $leave->tanggal_selesai->format('d M Y') }}</td>
                            <td>
                                <span class="badge {{ $leave->jenis === 'Sakit' ? 'badge-danger' : 'badge-warning' }}">
                                    {{ $leave->jenis }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $leave->status_approval === 'Approved' ? 'badge-success' : ($leave->status_approval === 'Rejected' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ $leave->status_approval }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; flex-direction: column;">
                                    <button type="button" class="badge badge-info leave-detail-trigger" style="border: none; cursor: pointer; padding: 4px 8px; font-weight: 600; width: 100%; text-align: center;"
                                        data-id="{{ $leave->id }}"
                                        data-name="{{ $leave->user?->nama_lengkap }}"
                                        data-instansi="{{ $leave->user?->instansi?->nama_instansi ?? '-' }}"
                                        data-start="{{ $leave->tanggal_mulai->format('d M Y') }}"
                                        data-end="{{ $leave->tanggal_selesai->format('d M Y') }}"
                                        data-jenis="{{ $leave->jenis }}"
                                        data-alasan="{{ $leave->alasan }}"
                                        data-bukti="{{ $leave->file_bukti ? asset($leave->file_bukti) : '' }}"
                                        data-status="{{ $leave->status_approval }}"
                                        data-comment="{{ $leave->catatan_pembimbing ?? '-' }}">
                                        Detail
                                    </button>
                                    @if($leave->status_approval === 'Pending')
                                        <button type="button" class="badge badge-success leave-approve-btn" style="border: none; cursor: pointer; padding: 4px 8px; font-weight: 600; width: 100%; text-align: center;" data-action-url="{{ route('admin.leave.approve', $leave->id) }}">Setujui</button>
                                        <button type="button" class="badge badge-danger leave-reject-btn" style="border: none; cursor: pointer; padding: 4px 8px; font-weight: 600; width: 100%; text-align: center;" data-action-url="{{ route('admin.leave.reject', $leave->id) }}">Tolak</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">Tidak ada pengajuan izin yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaves->hasPages())
            {{ $leaves->links('partials.pagination') }}
        @endif
    </div>

    <!-- Leave Detail Modal -->
    <div id="leave-detail-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 16px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 30px; position: relative; color: var(--text-primary); box-shadow: 0 15px 35px rgba(0,0,0,0.5); backdrop-filter: blur(20px);">
            <button id="close-detail-btn" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer;">&times;</button>
            
            <div style="margin-bottom: 24px; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px;">
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-primary);">Detail Permohonan Izin / Sakit</h3>
                <p id="modal-intern-info" class="muted-small" style="margin: 6px 0 0 0; color: var(--text-secondary);"></p>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Tanggal Mulai</span>
                        <p id="modal-start-date" style="margin: 6px 0 0 0; font-weight: 500;"></p>
                    </div>
                    <div>
                        <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Tanggal Selesai</span>
                        <p id="modal-end-date" style="margin: 6px 0 0 0; font-weight: 500;"></p>
                    </div>
                </div>
                
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Jenis Pengajuan</span>
                    <div style="margin-top: 6px;">
                        <span id="modal-jenis" class="badge"></span>
                    </div>
                </div>
                
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Alasan Ketidakhadiran</span>
                    <p id="modal-alasan" style="margin: 6px 0 0 0; white-space: pre-wrap; line-height: 1.6; color: var(--text-primary); background: rgba(255,255,255,0.02); padding: 12px; border-radius: 8px; border: 1px solid var(--glass-border);"></p>
                </div>

                <div id="modal-bukti-group">
                    <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Berkas Bukti</span>
                    <div style="margin-top: 6px;">
                        <a id="modal-bukti-link" href="#" target="_blank" class="badge badge-info" style="text-decoration: none; padding: 6px 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                            Unduh Berkas Bukti
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </a>
                        <span id="modal-no-bukti" style="color: var(--text-secondary); font-style: italic; display: none;">Tidak melampirkan berkas bukti</span>
                    </div>
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
                <button type="button" id="modal-approve-btn" class="btn-primary" style="padding: 10px 20px; border-radius: 8px; background: #10b981; border: none; font-weight: 600; cursor: pointer; color: #fff;">Setujui</button>
                <button type="button" id="modal-reject-btn" class="btn-secondary" style="padding: 10px 20px; border-radius: 8px; border: 1px solid #ef4444; color: #ef4444; background: none; font-weight: 600; cursor: pointer;">Tolak</button>
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
    @vite('resources/js/admin/leaves.js')
@endpush
