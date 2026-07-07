@extends('dashboard.layout')

@section('title', 'Intern Dashboard')
@section('header_title', 'Area Kerja Intern')

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
            
            <div style="display: flex; flex-direction: column; gap: 20px; align-items: center; justify-content: center; padding: 20px 0;">
                <div style="font-size: 1.1rem; font-weight: 500; text-align: center; color: var(--text-secondary);">
                    Lokasi Anda saat ini: <br>
                    <strong style="color: var(--text-primary); font-family: monospace;">-6.8988, 107.6358 (ITENAS Area)</strong>
                </div>

                <div style="display: flex; gap: 15px; width: 100%; max-width: 400px; margin-top: 10px;">
                    <button type="button" class="btn-logout" style="flex: 1; border-color: rgba(52, 211, 153, 0.3); background: rgba(52, 211, 153, 0.05); color: #34d399;" @if($todayAttendance) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>
                        Absen Masuk
                    </button>
                    <button type="button" class="btn-logout" style="flex: 1; border-color: rgba(168, 85, 247, 0.3); background: rgba(168, 85, 247, 0.05); color: #c084fc;" @if(!$todayAttendance || $todayAttendance->jam_pulang) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>
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
            <div style="padding: 10px 0; text-align: center;">
                <div style="width: 70px; height: 70px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%); margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; color: white;">
                    {{ substr(auth()->user()->pembimbing->nama_lengkap ?? 'P', 0, 1) }}
                </div>
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 5px;">{{ auth()->user()->pembimbing->nama_lengkap ?? 'Belum Ditugaskan' }}</h3>
                <p style="font-size: 0.8rem; color: var(--text-secondary);">{{ auth()->user()->pembimbing->email ?? '-' }}</p>
                <p style="font-size: 0.75rem; color: #a855f7; margin-top: 10px; font-weight: 500;">Dinas Pekerjaan Umum & Tata Ruang</p>
            </div>
        </div>

    </div>

    <!-- Logbook & Leave History Tabular Sections -->
    <div class="dashboard-row">
        
        <!-- Recent Logbooks -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Logbook Kegiatan Terbaru</h2>
                <span class="badge badge-info" style="cursor: pointer;">Tulis Baru</span>
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
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px;">{{ $logbook->deskripsi }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $logbook->status_approval === 'Approved' ? 'badge-success' : ($logbook->status_approval === 'Rejected' ? 'badge-danger' : 'badge-warning') }}">
                                        {{ $logbook->status_approval }}
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size: 0.8rem; color: var(--text-secondary);">{{ $logbook->catatan_pembimbing ?? '-' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-secondary);">Belum ada entri logbook.</td>
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
                <span class="badge badge-info" style="cursor: pointer;">Ajukan</span>
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
                                    <div style="font-size: 0.85rem; font-weight: 500;">{{ $leave->tanggal_mulai->format('d M') }} - {{ $leave->tanggal_selesai->format('d M Y') }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">{{ Str::limit($leave->alasan, 25) }}</div>
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
                                <td colspan="3" style="text-align: center; color: var(--text-secondary);">Belum ada pengajuan izin.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
