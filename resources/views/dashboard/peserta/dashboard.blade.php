@extends('dashboard.layout')

@section('title', 'Intern Dashboard')
@section('header_title', 'Area Kerja Intern')

@push('styles')
    @vite('resources/css/peserta/dashboard.css')
@endpush

@push('scripts')
    @vite('resources/js/peserta/dashboard.js')
@endpush

@section('content')
    <div class="stats-grid">
        <div class="stat-card hover-lift">
            <div class="stat-label">Kehadiran Hari Ini</div>
            <div class="stat-value">
                @if($todayAttendance)
                    <span class="badge {{ $todayAttendance->status === 'Hadir' ? 'badge-success' : ($todayAttendance->status === 'Terlambat' ? 'badge-warning' : (in_array($todayAttendance->status, ['Izin', 'Sakit']) ? 'badge-info' : 'badge-danger')) }}">
                        {{ $todayAttendance->status }}
                    </span>
                @elseif($todayLeave)
                    <span class="badge badge-info">
                        {{ $todayLeave->jenis }}
                    </span>
                @else
                    <span class="badge badge-danger">Belum Absen</span>
                @endif
            </div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Jam Masuk</div>
            <div class="stat-value">
                {{ $targetJamMasuk ?? '--:--' }}
                <span style="font-size: 0.9rem; font-weight: normal; color: var(--text-secondary); margin-left: 8px;">
                    (Absen: {{ $todayAttendance?->jam_masuk ? \Carbon\Carbon::parse($todayAttendance->jam_masuk)->format('H:i') : '--:--' }})
                </span>
            </div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Jam Pulang</div>
            <div class="stat-value">
                {{ $targetJamPulang ?? '--:--' }}
                <span style="font-size: 0.9rem; font-weight: normal; color: var(--text-secondary); margin-left: 8px;">
                    (Absen: {{ $todayAttendance?->jam_pulang ? \Carbon\Carbon::parse($todayAttendance->jam_pulang)->format('H:i') : '--:--' }})
                </span>
            </div>
        </div>
    </div>

    <!-- Attendance Action & Quick Logbook Row -->
    <div class="admin-dashboard-row">
        
        <!-- Attendance Control Panel -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Kontrol Kehadiran Harian</h2>
                <!-- Live Digital Clock -->
                <div class="digital-clock-container" data-server-timestamp="{{ now()->getTimestamp() * 1000 }}">
                    <div id="digital-clock">00:00:00</div>
                    <div id="digital-date">Memuat waktu...</div>
                </div>
            </div>
            
            <div class="attendance-panel">
                <div class="location-text">
                    Lokasi Anda saat ini: <br>
                    <strong class="location-coordinate" 
                            id="location-coordinate"
                            data-office-lat="{{ $officeLat }}"
                            data-office-lng="{{ $officeLng }}"
                            data-office-radius="{{ $officeRadius }}">Mendeteksi lokasi...</strong>
                </div>

                <div class="location-text" style="margin-top: 8px;">
                    Jarak Ke Kantor: <br>
                    <strong id="location-distance" style="font-size: 0.95rem; font-weight: bold; color: var(--text-secondary); display: inline-flex; align-items: center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Menghitung jarak...
                    </strong>
                </div>

                @if(!$todayAttendance)
                    <!-- Camera Control Area for Selfie -->
                    <div class="camera-container">
                        <!-- Camera Selection Dropdown (hidden until Buka Kamera clicked) -->
                        <div class="camera-select-container" id="camera-select-wrap" style="display: none; width: 100%; max-width: 320px; text-align: left;">
                            <label for="camera-select" style="font-size: 0.82rem; font-weight: 600; color: var(--text-secondary); display: block; margin-bottom: 4px;">Pilih Kamera:</label>
                            <select id="camera-select" class="camera-select-input">
                                <option value="">Mendeteksi kamera...</option>
                            </select>
                            <button type="button" id="btn-confirm-camera" class="btn-camera btn-camera-primary" style="margin-top: 8px; width: 100%;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                Mulai Kamera
                            </button>
                        </div>

                        <!-- Video Stream Preview -->
                        <video id="webcam-video" autoplay playsinline class="camera-video-preview"></video>
                        <!-- Selfie Image Preview -->
                        <img id="selfie-preview" class="camera-selfie-result" alt="Selfie Preview">
                        
                        <!-- Canvas for processing (Hidden) -->
                        <canvas id="attendance-canvas" style="display: none;"></canvas>

                        <!-- Camera Action Buttons -->
                        <div class="camera-buttons">
                            <button type="button" id="btn-start-camera" class="btn-camera btn-camera-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                Buka Kamera
                            </button>
                            <button type="button" id="btn-capture-photo" class="btn-camera btn-camera-primary" style="display: none;">
                                Ambil Foto
                            </button>
                            <button type="button" id="btn-retake-photo" class="btn-camera btn-camera-danger" style="display: none;">
                                Foto Ulang
                            </button>
                        </div>
                    </div>
                @else
                    {{-- ===== SUDAH ABSEN MASUK ===== --}}
                    <div class="camera-container">

                        {{-- Always show check-in and check-out selfies in a side-by-side row if saved --}}
                        <div class="saved-selfies-row" style="display: flex; justify-content: center; gap: 20px; margin-bottom: 15px; flex-wrap: wrap;">
                            @if($todayAttendance->foto_masuk)
                                <div class="selfie-item" style="text-align: center;">
                                    <img src="{{ asset($todayAttendance->foto_masuk) }}"
                                         class="selfie-thumb selfie-thumb-in clickable-selfie"
                                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; cursor: pointer; transition: all 0.2s;"
                                         alt="Selfie Masuk"
                                         onclick="showImageModal('{{ asset($todayAttendance->foto_masuk) }}', 'Selfie Masuk')">
                                    <div class="selfie-label selfie-label-in" style="font-size: 0.65rem; margin-top: 4px;">✓ Masuk</div>
                                </div>
                            @endif

                            @if($todayAttendance->foto_pulang)
                                <div class="selfie-item" style="text-align: center;">
                                    <img src="{{ asset($todayAttendance->foto_pulang) }}"
                                         class="selfie-thumb selfie-thumb-out clickable-selfie"
                                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; cursor: pointer; transition: all 0.2s;"
                                         alt="Selfie Pulang"
                                         onclick="showImageModal('{{ asset($todayAttendance->foto_pulang) }}', 'Selfie Pulang')">
                                    <div class="selfie-label selfie-label-out" style="font-size: 0.65rem; margin-top: 4px;">✓ Pulang</div>
                                </div>
                            @endif
                        </div>

                        {{-- Check-out camera UI — only when not yet checked out --}}
                        @if(!$todayAttendance->jam_pulang)
                            <div class="selfie-divider">Selfie Absen Pulang</div>

                            <!-- Camera Selection Dropdown (hidden until Buka Kamera clicked) -->
                            <div class="camera-select-container" id="camera-select-out-wrap" style="display: none; width: 100%; max-width: 320px; text-align: left;">
                                <label for="camera-select-out" style="font-size: 0.82rem; font-weight: 600; color: var(--text-secondary); display: block; margin-bottom: 4px;">Pilih Kamera:</label>
                                <select id="camera-select-out" class="camera-select-input">
                                    <option value="">Mendeteksi kamera...</option>
                                </select>
                                <button type="button" id="btn-confirm-camera-out" class="btn-camera btn-camera-primary" style="margin-top: 8px; width: 100%;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                    Mulai Kamera
                                </button>
                            </div>

                            <video id="webcam-video-out" autoplay playsinline class="camera-video-preview"></video>
                            <img id="selfie-preview-out" class="camera-selfie-result" alt="Selfie Pulang Preview">
                            <canvas id="attendance-canvas-out" style="display: none;"></canvas>

                            <div class="camera-buttons">
                                <button type="button" id="btn-start-camera-out" class="btn-camera btn-camera-primary">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                    Buka Kamera
                                </button>
                                <button type="button" id="btn-capture-photo-out" class="btn-camera btn-camera-primary"
                                        style="display: none;">
                                    Ambil Foto
                                </button>
                                <button type="button" id="btn-retake-photo-out" class="btn-camera btn-camera-danger"
                                        style="display: none;">
                                    Foto Ulang
                                </button>
                            </div>

                        @else
                            <div class="selfie-divider">Selesai Hari Ini</div>
                        @endif

                    </div>
                @endif

                <div class="attendance-actions">
                    <button type="button" id="btn-submit-in"
                            class="btn-logout attendance-button attendance-button-in"
                            @disabled(true)>
                        Absen Masuk
                    </button>
                    <button type="button" id="btn-submit-out"
                            class="btn-logout attendance-button attendance-button-out"
                            @disabled(!$todayAttendance || $todayAttendance->jam_pulang)
                            data-today-logbooks-count="{{ $todayLogbooksCount }}"
                            {{ ($todayAttendance && !$todayAttendance->jam_pulang) ? 'data-needs-selfie=true' : '' }}>
                        Absen Pulang
                    </button>
                </div>
            </div>
        </div>

        <!-- Supervisor Info Card -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Pembimbing Lapangan</h2>
            </div>
            <div class="supervisor-card">
                <div class="supervisor-avatar">
                    {{ substr(auth()->user()->pembimbing->nama_lengkap ?? 'P', 0, 1) }}
                </div>
                <h3 class="supervisor-name">{{ auth()->user()->pembimbing->nama_lengkap ?? 'Belum Ditugaskan' }}</h3>

                <div class="supervisor-details">
                    @if(auth()->user()->pembimbing)
                        <div class="supervisor-detail-row">
                            <span class="supervisor-detail-label">NIP</span>
                            <span class="supervisor-detail-value">{{ auth()->user()->pembimbing->nip ?? '-' }}</span>
                        </div>
                        <div class="supervisor-detail-row">
                            <span class="supervisor-detail-label">Email</span>
                            <span class="supervisor-detail-value">{{ auth()->user()->pembimbing->email ?? '-' }}</span>
                        </div>
                        <div class="supervisor-detail-row">
                            <span class="supervisor-detail-label">No. Telepon</span>
                            <span class="supervisor-detail-value">{{ auth()->user()->pembimbing->no_telepon ?? '-' }}</span>
                        </div>
                        <div class="supervisor-detail-row">
                            <span class="supervisor-detail-label">Instansi</span>
                            <span class="supervisor-detail-value">{{ auth()->user()->pembimbing->instansi->nama_instansi ?? 'Tidak terdaftar' }}</span>
                        </div>
                        <div class="supervisor-detail-row">
                            <span class="supervisor-detail-label">Status</span>
                            <span class="supervisor-detail-value">
                                <span class="badge {{ auth()->user()->pembimbing->status_aktif ? 'badge-success' : 'badge-danger' }}" style="font-size: 0.72rem;">
                                    {{ auth()->user()->pembimbing->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </span>
                        </div>
                    @else
                        <p style="color: var(--text-secondary); font-size: 0.85rem; text-align: center; margin-top: 10px;">Pembimbing belum ditugaskan.</p>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- Logbook & Leave History Tabular Sections -->
    <div class="peserta-stack">
        
        <!-- Recent Logbooks -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Logbook Kegiatan Terbaru</h2>
                <a href="{{ route('peserta.logbook') }}" class="badge badge-info action-badge" style="text-decoration: none;">Selengkapnya</a>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kegiatan</th>
                            <th>Status</th>
                            <th>Catatan Pembimbing</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLogbooks as $logbook)
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-state">Belum ada entri logbook.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Leave Requests -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Pengajuan Izin Sakit</h2>
                <a href="{{ route('peserta.leave') }}" class="badge badge-info action-badge" style="text-decoration: none;">Selengkapnya</a>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLeaves as $leave)
                            <tr>
                                <td>
                                    <div class="leave-date">{{ $leave->tanggal_mulai->format('d M') }} - {{ $leave->tanggal_selesai->format('d M Y') }}</div>
                                    <div class="leave-reason">{{ Str::limit($leave->alasan, 25) }}</div>
                                </td>
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">Belum ada pengajuan izin.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>





    {{-- ===== Modal: Selfie Preview Popup ===== --}}
    <div class="form-modal-backdrop" id="selfie-modal">
        <div style="position: relative; max-width: 480px; width: 100%; padding: 15px;">
            <button type="button" id="close-selfie-modal" class="modal-close" style="position: absolute; top: -5px; right: -5px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; z-index: 10;">&times;</button>
            <img id="modal-selfie-img" src="" alt="Enlarged Selfie" style="width: 100%; border-radius: 16px; border: 1px solid var(--glass-border); box-shadow: 0 10px 30px rgba(0,0,0,0.5); display: block;">
            <div id="modal-selfie-title" style="text-align: center; margin-top: 12px; font-weight: 600; color: #fff; font-size: 0.95rem; font-family: 'Outfit', sans-serif;"></div>
        </div>
    </div>
@endsection
