@extends('dashboard.layout')

@php
    $isTestUser = auth()->check() && auth()->user()->email === 'yogi.sutana@gmail.com';
@endphp

@section('title', 'Intern Dashboard')
@section('header_title', 'Area Kerja Intern')

@push('styles')
    @vite('resources/css/peserta/dashboard.css')
@endpush

@push('scripts')
    <script>
        window.userNeedsAttendanceReminder = @json(!$todayAttendance && !$isHoliday && !$todayLeave);
        // Akun test: gunakan waktu lokal HP untuk keperluan pengujian
        window.isTestUser = @json(auth()->user()->email === 'yogi.sutana@gmail.com');
        window.currentSchedule = {
            jam_masuk: @json($targetJamMasuk),
            jam_pulang: @json($targetJamPulang),
            batas_keterlambatan: @json($targetBatasTerlambat)
        };

        // Geolocation proxy to resolve race condition between inline and compiled script
        (function() {
            if (navigator.geolocation) {
                window.accuratePositionCache = null;
                const originalGet = navigator.geolocation.getCurrentPosition;
                const originalWatch = navigator.geolocation.watchPosition;

                navigator.geolocation.getCurrentPosition = function(success, error, options) {
                    console.log('[GPS Proxy] Intercepted getCurrentPosition');
                    if (window.accuratePositionCache) {
                        console.log('[GPS Proxy] Resolving with cached accurate position');
                        success(window.accuratePositionCache);
                    } else {
                        originalGet.call(navigator.geolocation, 
                            (pos) => {
                                window.accuratePositionCache = pos;
                                success(pos);
                            }, 
                            (err) => {
                                console.warn('[GPS Proxy] getCurrentPosition failed', err);
                                if (error) error(err);
                            }, 
                            options
                        );
                    }
                };

                navigator.geolocation.watchPosition = function(success, error, options) {
                    return originalWatch.call(navigator.geolocation,
                        (pos) => {
                            window.accuratePositionCache = pos;
                            success(pos);
                        },
                        error,
                        options
                    );
                };
            }
        })();
    </script>
    @vite('resources/js/peserta/dashboard.js')
@endpush

@section('content')
    <div class="stats-grid">
        <div class="stat-card hover-lift">
            <div class="stat-label">Kehadiran Hari Ini</div>
            <div class="stat-value">
                @if($todayAttendance)
                    @php
                        $badgeClass = 'badge-danger';
                        $badgeStyle = '';
                        if ($todayAttendance->status === 'Hadir') {
                            $badgeClass = 'badge-success';
                        } elseif ($todayAttendance->status === 'Terlambat') {
                            $badgeClass = 'badge-warning';
                        } elseif (in_array($todayAttendance->status, ['Izin', 'Sakit', 'Pulang Cepat / Izin'])) {
                            $badgeClass = 'badge-info';
                        } elseif (in_array($todayAttendance->status, ['Lupa Absen Masuk', 'Lupa Absen Pulang', 'Terlambat dan Izin'])) {
                            $badgeClass = 'badge-warning';
                        } elseif ($todayAttendance->status === 'Lupa Absen Masuk dan Pulang') {
                            $badgeStyle = 'background: rgba(139, 92, 246, 0.15); border: 1px solid #8b5cf6; color: #a78bfa;';
                        } elseif ($todayAttendance->status === 'Lupa Absen Masuk dan Izin') {
                            $badgeStyle = 'background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171;';
                        }
                    @endphp
                    <span class="badge {{ $badgeClass }}" style="{{ $badgeStyle }}">
                        {{ $todayAttendance->status }}
                    </span>
                @elseif($todayLeave)
                    <span class="badge badge-info">
                        {{ $todayLeave->jenis }}
                    </span>
                @elseif($isHoliday)
                    <span class="badge badge-info" style="background: rgba(59, 130, 246, 0.15); border: 1px solid #3b82f6; color: #60a5fa;">
                        Libur ({{ $holidayName }})
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
                @if($isHoliday && !$todayAttendance)
                    <div style="text-align: center; padding: 30px 20px; background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 16px; width: 100%; box-sizing: border-box; margin: 10px 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px;">
                        <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; color: #60a5fa;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                                <path d="M8 14h.01"></path>
                                <path d="M12 14h.01"></path>
                                <path d="M16 14h.01"></path>
                                <path d="M8 18h.01"></path>
                                <path d="M12 18h.01"></path>
                                <path d="M16 18h.01"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 style="margin: 0 0 4px 0; font-size: 1.05rem; color: var(--text-primary); font-weight: 700;">Absensi Libur</h4>
                            <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5; max-width: 280px; margin: 0 auto;">
                                Hari ini adalah hari libur (<strong>{{ $holidayName }}</strong>). Layanan absensi masuk & pulang dinonaktifkan.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="location-text">
                        Lokasi Anda saat ini: <br>
                        <strong class="location-coordinate" 
                                id="location-coordinate"
                                data-office-lat="{{ $officeLat }}"
                                data-office-lng="{{ $officeLng }}"
                                data-office-radius="{{ $officeRadius }}"
                                data-office-locations='@json($officeLocations)'
                                data-require-photo="{{ $requirePhoto ? 'true' : 'false' }}"
                                data-accuracy="0">Mendeteksi lokasi...</strong>
                    </div>

                    <div class="location-text" style="margin-top: 8px;">
                        Jarak Ke Kantor: <br>
                        <strong id="location-distance" style="font-size: 0.95rem; font-weight: bold; color: var(--text-secondary); display: inline-flex; align-items: center;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            Menghitung jarak...
                        </strong>
                    </div>

                    @if(!$todayAttendance)
                        @if($isPastLateLimit && !$isTestUser)
                            {{-- User forgot to check-in, show check-out camera UI --}}
                            @if($requirePhoto)
                                <div class="camera-container">
                                    <div class="selfie-divider" style="margin-top: 10px; color: var(--accent-primary); font-weight: 600;">Selfie Absen Pulang (Lupa Absen Masuk)</div>
                                    
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

                                    <div class="camera-buttons" style="display: flex; gap: 8px; justify-content: center; align-items: center; width: 100%; flex-wrap: wrap;">
                                        <button type="button" id="btn-start-camera-out" class="btn-camera btn-camera-primary">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                            Buka Kamera
                                        </button>
                                        <button type="button" id="btn-capture-photo-out" class="btn-camera btn-camera-primary" style="display: none;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="3"></circle></svg>
                                            Ambil Foto
                                        </button>
                                        <button type="button" id="btn-stop-camera-out" class="btn-camera btn-camera-danger" style="display: none; background: rgba(239, 68, 68, 0.12); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.25);">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>
                                            Tutup Kamera
                                        </button>
                                        <button type="button" id="btn-retake-photo-out" class="btn-camera btn-camera-danger" style="display: none;">
                                            Foto Ulang
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 15px; width: 100%; box-sizing: border-box;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="2" style="margin-bottom: 8px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                    <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">
                                        Absensi foto dinonaktifkan oleh pembimbing.
                                    </div>
                                </div>
                            @endif
                        @else
                            <!-- Camera Control Area for Selfie -->
                            @if($requirePhoto)
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
                                    <div class="camera-buttons" style="display: flex; gap: 8px; justify-content: center; align-items: center; width: 100%; flex-wrap: wrap;">
                                        <button type="button" id="btn-start-camera" class="btn-camera btn-camera-primary">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                            Buka Kamera
                                        </button>
                                        <button type="button" id="btn-capture-photo" class="btn-camera btn-camera-primary" style="display: none;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="3"></circle></svg>
                                            Ambil Foto
                                        </button>
                                        <button type="button" id="btn-stop-camera" class="btn-camera btn-camera-danger" style="display: none; background: rgba(239, 68, 68, 0.12); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.25);">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>
                                            Tutup Kamera
                                        </button>
                                        <button type="button" id="btn-retake-photo" class="btn-camera btn-camera-danger" style="display: none;">
                                            Foto Ulang
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 15px; width: 100%; box-sizing: border-box;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="2" style="margin-bottom: 8px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                    <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">
                                        Absensi foto dinonaktifkan oleh pembimbing.
                                    </div>
                                </div>
                            @endif
                        @endif
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
                                @if($requirePhoto)
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

                                    <div class="camera-buttons" style="display: flex; gap: 8px; justify-content: center; align-items: center; width: 100%; flex-wrap: wrap;">
                                        <button type="button" id="btn-start-camera-out" class="btn-camera btn-camera-primary">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                            Buka Kamera
                                        </button>
                                        <button type="button" id="btn-capture-photo-out" class="btn-camera btn-camera-primary" style="display: none;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="3"></circle></svg>
                                            Ambil Foto
                                        </button>
                                        <button type="button" id="btn-stop-camera-out" class="btn-camera btn-camera-danger" style="display: none; background: rgba(239, 68, 68, 0.12); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.25);">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>
                                            Tutup Kamera
                                        </button>
                                        <button type="button" id="btn-retake-photo-out" class="btn-camera btn-camera-danger" style="display: none;">
                                            Foto Ulang
                                        </button>
                                    </div>
                                @else
                                    <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 15px; width: 100%; box-sizing: border-box;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="2" style="margin-bottom: 8px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                        <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">
                                            Absensi foto dinonaktifkan oleh pembimbing.
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="selfie-divider">Selesai Hari Ini</div>
                            @endif

                        </div>
                    @endif

                    <div class="attendance-actions">
                        <button type="button" id="btn-submit-in"
                                class="btn-logout attendance-button attendance-button-in"
                                @disabled($todayAttendance || (!$isTestUser && !$isPastLateLimit))
                                data-is-past-limit="{{ $isPastLateLimit ? 'true' : 'false' }}"
                                data-limit-time="{{ $targetBatasTerlambat }}">
                            @if($todayAttendance)
                                Sudah Absen Masuk
                            @elseif($isTestUser)
                                Absen Masuk
                            @else
                                {{ $isPastLateLimit ? 'Waktu Absen Berakhir' : 'Absen Masuk' }}
                            @endif
                        </button>
                        <button type="button" id="btn-submit-out"
                                class="btn-logout attendance-button attendance-button-out"
                                @disabled(($todayAttendance && $todayAttendance->jam_pulang) || (!$todayAttendance && !$isPastLateLimit && !$isTestUser))
                                data-today-logbooks-count="{{ $todayLogbooksCount }}"
                                {{ (($todayAttendance && !$todayAttendance->jam_pulang) || (!$todayAttendance && ($isPastLateLimit || $isTestUser))) ? 'data-needs-selfie=true' : '' }}>
                            Absen Pulang
                        </button>
                    </div>
                @endif
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
                                    <span class="muted-small">{{ !empty($logbook->catatan_pembimbing) ? $logbook->catatan_pembimbing : '-' }}</span>
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

    <!-- Configuration for Early Checkout calculation -->
    <div id="attendance-config-data" style="display: none;"
         data-jam-masuk="{{ $targetJamMasuk }}"
         data-jam-pulang="{{ $targetJamPulang }}"
         data-batas-terlambat="{{ $targetBatasTerlambat }}"
         data-actual-masuk="{{ $todayAttendance?->jam_masuk }}">
    </div>

    {{-- ===== Modal: Tambah Logbook (Directly on Dashboard) ===== --}}
    <div class="form-modal-backdrop" id="modal-add-logbook" style="z-index: 10000;">
        <div class="form-modal">
            <div class="form-modal-header">
                <h3>Tulis Logbook Kegiatan Baru</h3>
                <button type="button" class="modal-close" id="close-add-logbook-modal">&times;</button>
            </div>
            <form action="{{ route('peserta.logbook.store') }}" method="POST" class="modal-form">
                @csrf
                <input type="hidden" name="redirect_to" value="dashboard">
                <div class="form-group">
                    <label style="color: var(--text-secondary); display: block; margin-bottom: 6px; font-weight: 500;">Tanggal Kegiatan</label>
                    <input type="text" value="{{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}" disabled style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--glass-border); font-weight: 500; width: 100%; padding: 10px; border-radius: 8px; box-sizing: border-box;">
                    <input type="hidden" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label for="kegiatan" style="color: var(--text-secondary); display: block; margin-bottom: 6px; font-weight: 500;">Judul Kegiatan / Tugas <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="kegiatan" name="kegiatan" placeholder="Contoh: Membuat rancangan database absensi" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(15,23,42,0.4); color: #fff; box-sizing: border-box;">
                </div>
                <div class="form-group">
                    <label for="tags" style="color: var(--text-secondary); display: block; margin-bottom: 6px; font-weight: 500;">Tag Kegiatan (Pisahkan dengan koma)</label>
                    <input type="text" id="tags" name="tags" placeholder="Contoh: laravel, mysql, refactor" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(15,23,42,0.4); color: #fff; box-sizing: border-box;">
                </div>
                <div class="form-group">
                    <label for="deskripsi" style="color: var(--text-secondary); display: block; margin-bottom: 6px; font-weight: 500;">Deskripsi Detail Kegiatan <span style="color: #ef4444;">*</span></label>
                    <textarea id="deskripsi" name="deskripsi" rows="5" placeholder="Jelaskan secara rinci kegiatan yang Anda lakukan hari ini..." required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(15,23,42,0.4); color: #fff; box-sizing: border-box; font-family: inherit; resize: vertical;"></textarea>
                </div>
                <div class="modal-actions" style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 15px;">
                    <button type="button" class="btn-secondary" id="cancel-add-logbook-modal">Batal</button>
                    <button type="submit" name="action" value="draft" class="btn-secondary" style="border: 1px solid var(--accent-primary); color: var(--accent-primary);">Simpan Draft</button>
                    <button type="submit" name="action" value="submit" class="btn-primary">Kirim ke Pembimbing</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function triggerQuickAttendance() {
            const card = document.querySelector('.attendance-panel');
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => {
                    const btnIn = document.getElementById('btn-start-camera');
                    const btnOut = document.getElementById('btn-start-camera-out');
                    if (btnIn && btnIn.style.display !== 'none' && !btnIn.disabled) {
                        btnIn.click();
                    } else if (btnOut && btnOut.style.display !== 'none' && !btnOut.disabled) {
                        btnOut.click();
                    }
                }, 800);
            }
        }

        // Auto trigger attendance if URL parameter is present
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('trigger-attendance')) {
                // Remove parameter from address bar to prevent triggering again on page refresh
                const url = new URL(window.location);
                url.searchParams.delete('trigger-attendance');
                window.history.replaceState({}, '', url);

                setTimeout(() => {
                    triggerQuickAttendance();
                }, 1000);
            }

            // Dashboard Logbook modal controls
            const modalAddLogbook = document.getElementById('modal-add-logbook');
            const btnCloseAddLogbook = document.getElementById('close-add-logbook-modal');
            const btnCancelAddLogbook = document.getElementById('cancel-add-logbook-modal');

            window.toggleAddLogbookModal = function(show) {
                if (modalAddLogbook) {
                    modalAddLogbook.classList.toggle('is-open', show);
                }
            };

            btnCloseAddLogbook?.addEventListener('click', () => window.toggleAddLogbookModal(false));
            btnCancelAddLogbook?.addEventListener('click', () => window.toggleAddLogbookModal(false));

            modalAddLogbook?.addEventListener('click', (e) => {
                if (e.target === modalAddLogbook) {
                    window.toggleAddLogbookModal(false);
                }
            });
        });
    </script>
@endsection
