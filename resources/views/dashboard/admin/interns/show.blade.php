@extends('dashboard.layout')

@section('title', 'Detail Aktivitas Intern')
@section('header_title', 'Detail Aktivitas Intern: ' . $intern->nama_lengkap)

@push('styles')
    @vite('resources/css/admin/interns.css')
@endpush

@section('content')
    <!-- Back Button -->
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.interns') }}" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px;">
            &larr; Kembali ke Daftar
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="stat-card hover-lift">
            <div class="stat-label">Hadir</div>
            <div class="stat-value" style="color: var(--accent-primary);">{{ $presentCount }} Hari</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Terlambat</div>
            <div class="stat-value" style="color: var(--accent-warning);">{{ $lateCount }} Hari</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Izin</div>
            <div class="stat-value" style="color: var(--accent-info);">{{ $leaveCount }} Hari</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Sakit</div>
            <div class="stat-value" style="color: #ef4444;">{{ $sickCount }} Hari</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Logbook Disetujui</div>
            <div class="stat-value" style="color: var(--accent-primary);">{{ $approvedLogbooksCount }} Hari</div>
        </div>
    </div>

    <!-- Profile & Export Section -->
    <div class="profile-layout" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
        <!-- Profile Card -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Profil Peserta</h2>
            </div>
            <div style="padding: 24px; display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px;">
                    <span style="font-weight: 600; width: 140px; color: var(--text-secondary);">Nama Lengkap</span>
                    <span style="color: var(--text-primary);">{{ $intern->nama_lengkap }}</span>
                </div>
                <div style="display: flex; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px;">
                    <span style="font-weight: 600; width: 140px; color: var(--text-secondary);">Email</span>
                    <span style="color: var(--text-primary);">{{ $intern->email }}</span>
                </div>
                <div style="display: flex; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px;">
                    <span style="font-weight: 600; width: 140px; color: var(--text-secondary);">No Telepon</span>
                    <span style="color: var(--text-primary);">{{ $intern->no_telepon ?? '-' }}</span>
                </div>
                <div style="display: flex; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px;">
                    <span style="font-weight: 600; width: 140px; color: var(--text-secondary);">Instansi</span>
                    <span style="color: var(--text-primary);">{{ $intern->instansi?->nama_instansi ?? '-' }}</span>
                </div>
                <div style="display: flex; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px;">
                    <span style="font-weight: 600; width: 140px; color: var(--text-secondary);">Alamat</span>
                    <span style="color: var(--text-primary);">{{ $intern->alamat ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Emergency Contacts & Actions -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Kontak Darurat & Unduhan</h2>
            </div>
            <div style="padding: 24px; display: flex; flex-direction: column; gap: 20px;">
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <span style="font-weight: 600; color: var(--text-secondary); font-size: 0.85rem;">Hubungan Darurat 1</span>
                    <span style="color: var(--text-primary);">{{ $intern->no_darurat_1 ?? '-' }} ({{ $intern->hubungan_darurat_1 ?? '-' }})</span>
                </div>
                
                <hr style="border: 0; border-top: 1px solid var(--glass-border); margin: 0;">

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <span style="font-weight: 600; color: var(--text-secondary); font-size: 0.85rem;">Cetak Laporan / Rekap Kegiatan</span>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <a href="{{ route('peserta.monthly-report', ['user_id' => $intern->id]) }}" target="_blank" class="btn-primary" style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none; font-weight: 600;">
                            Cetak Laporan Bulanan (PDF)
                        </a>
                        <a href="{{ route('peserta.attendance.csv', ['user_id' => $intern->id]) }}" class="btn-secondary" style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none; font-weight: 600;">
                            Rekap Absen (CSV)
                        </a>
                        <a href="{{ route('peserta.logbook.pdf', ['user_id' => $intern->id]) }}" target="_blank" class="btn-primary" style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none; font-weight: 600;">
                            Cetak Logbook (PDF)
                        </a>
                        <a href="{{ route('peserta.logbook.csv', ['user_id' => $intern->id]) }}" class="btn-secondary" style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none; font-weight: 600;">
                            Rekap Logbook (CSV)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabbed Navigation -->
    <div class="tabs-container">
        <div class="tabs-header">
            <button class="tab-btn active" data-tab="tab-attendance">Riwayat Absensi</button>
            <button class="tab-btn" data-tab="tab-logbooks">Logbook Harian</button>
        </div>

        <!-- Tab 1: Attendance -->
        <div class="tab-content active" id="tab-attendance">
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Status</th>
                            <th>Jarak Absen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            <tr>
                                <td>{{ $attendance->tanggal->format('d M Y') }}</td>
                                <td>{{ $attendance->jam_masuk ? $attendance->jam_masuk->format('H:i:s') : '-' }}</td>
                                <td>{{ $attendance->jam_pulang ? $attendance->jam_pulang->format('H:i:s') : '-' }}</td>
                                <td>
                                    @if($attendance->status === 'Hadir')
                                        <span class="badge badge-success">Hadir</span>
                                    @elseif($attendance->status === 'Terlambat')
                                        <span class="badge badge-warning">Terlambat</span>
                                    @elseif($attendance->status === 'Izin' || $attendance->status === 'Sakit')
                                        <span class="badge badge-info">{{ $attendance->status }}</span>
                                    @else
                                        <span class="badge badge-danger">{{ $attendance->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->jarak_meter_masuk)
                                        Masuk: {{ round($attendance->jarak_meter_masuk) }}m
                                        @if($attendance->jarak_meter_pulang)
                                            | Pulang: {{ round($attendance->jarak_meter_pulang) }}m
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">Belum ada riwayat absensi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($attendances->hasPages())
                {{ $attendances->links('partials.pagination') }}
            @endif
        </div>

        <!-- Tab 2: Logbooks -->
        <div class="tab-content" id="tab-logbooks">
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kegiatan</th>
                            <th>Deskripsi</th>
                            <th>Tag</th>
                            <th>Status</th>
                            <th>Catatan Pembimbing</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logbooks as $logbook)
                            <tr>
                                <td>{{ $logbook->tanggal->format('d M Y') }}</td>
                                <td><strong>{{ $logbook->kegiatan }}</strong></td>
                                <td>
                                    <span class="muted-small" style="display: block; max-width: 250px; white-space: normal; line-height: 1.4;">
                                        {{ $logbook->deskripsi }}
                                    </span>
                                </td>
                                <td>
                                    @if($logbook->tags)
                                        @foreach(explode(',', $logbook->tags) as $tag)
                                            <span class="badge badge-info" style="font-size: 0.7rem; padding: 2px 6px; margin: 1px;">#{{ trim($tag) }}</span>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $logbook->status_approval === 'Approved' ? 'badge-success' : ($logbook->status_approval === 'Rejected' ? 'badge-danger' : 'badge-warning') }}">
                                        {{ $logbook->status_approval }}
                                    </span>
                                </td>
                                <td>{{ $logbook->catatan_pembimbing ?? '-' }}</td>
                                <td>
                                    @if($logbook->status_approval === 'Pending')
                                        <div style="display: flex; gap: 6px; flex-direction: column;">
                                            <form action="{{ route('admin.logbook.approve', $logbook->id) }}" method="POST" style="margin: 0;">
                                                @csrf
                                                <button type="submit" class="badge badge-success" style="border: none; cursor: pointer; padding: 4px 8px; font-weight: 600; width: 100%;">Setujui</button>
                                            </form>
                                            <form action="{{ route('admin.logbook.reject', $logbook->id) }}" method="POST" style="margin: 0; display: flex; flex-direction: column; gap: 4px;">
                                                @csrf
                                                <input type="text" name="catatan_pembimbing" placeholder="Catatan..." style="font-size: 0.7rem; padding: 4px; border: 1px solid var(--glass-border); border-radius: 4px; background: rgba(255,255,255,0.05); color: #fff; width: 90px;" required>
                                                <button type="submit" class="badge badge-danger" style="border: none; cursor: pointer; padding: 4px 8px; font-weight: 600;">Tolak</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="muted-small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">Belum ada riwayat logbook kegiatan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logbooks->hasPages())
                {{ $logbooks->links('partials.pagination') }}
            @endif
        </div>
    </div>

    <!-- JS script for tab swapping -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabs = document.querySelectorAll('.tab-btn');
            const contents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const target = tab.getAttribute('data-tab');

                    tabs.forEach(t => t.classList.remove('active'));
                    contents.forEach(c => c.classList.remove('active'));

                    tab.classList.add('active');
                    const targetEl = document.getElementById(target);
                    if (targetEl) {
                        targetEl.classList.add('active');
                    }
                });
            });
        });
    </script>
@endsection
