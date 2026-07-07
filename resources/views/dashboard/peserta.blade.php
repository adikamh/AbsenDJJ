@extends('dashboard.layout')

@section('title', 'Intern Dashboard')
@section('header_title', 'Area Kerja Intern')

@push('styles')
    @vite('resources/css/dashboard-peserta.css')
@endpush

@push('scripts')
    @vite('resources/js/dashboard-peserta.js')
@endpush

@section('content')
    <div class="stats-grid">
        <div class="stat-card hover-lift">
            <div class="stat-label">Kehadiran Hari Ini</div>
            <div class="stat-value">
                @if($todayAttendance)
                    <span class="badge {{ $todayAttendance->status === 'Hadir' ? 'badge-success' : ($todayAttendance->status === 'Terlambat' ? 'badge-warning' : 'badge-danger') }}">
                        {{ $todayAttendance->status }}
                    </span>
                @else
                    <span class="badge badge-danger">Belum Absen</span>
                @endif
            </div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Jam Masuk</div>
            <div class="stat-value">{{ $todayAttendance?->jam_masuk ? \Carbon\Carbon::parse($todayAttendance->jam_masuk)->format('H:i') : '--:--' }}</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Jam Pulang</div>
            <div class="stat-value">{{ $todayAttendance?->jam_pulang ? \Carbon\Carbon::parse($todayAttendance->jam_pulang)->format('H:i') : '--:--' }}</div>
        </div>
    </div>

    <!-- Attendance Action & Quick Logbook Row -->
    <div class="dashboard-row">
        
        <!-- Attendance Control Panel -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Kontrol Kehadiran Harian</h2>
            </div>
            
            <div class="attendance-panel">
                <div class="location-text">
                    Lokasi Anda saat ini: <br>
                    <strong class="location-coordinate">-6.8988, 107.6358 (ITENAS Area)</strong>
                </div>

                <div class="attendance-actions">
                    <button type="button" class="btn-logout attendance-button attendance-button-in" @disabled($todayAttendance)>
                        Absen Masuk
                    </button>
                    <button type="button" class="btn-logout attendance-button attendance-button-out" @disabled(!$todayAttendance || $todayAttendance->jam_pulang)>
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
                <p class="supervisor-email">{{ auth()->user()->pembimbing->email ?? '-' }}</p>
                <p class="supervisor-organization">Dinas Pekerjaan Umum & Tata Ruang</p>
            </div>
        </div>

    </div>

    <!-- Logbook & Leave History Tabular Sections -->
    <div class="dashboard-row">
        
        <!-- Recent Logbooks -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Logbook Kegiatan Terbaru</h2>
                <span class="badge badge-info action-badge">Tulis Baru</span>
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
                <span class="badge badge-info action-badge">Ajukan</span>
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
@endsection
