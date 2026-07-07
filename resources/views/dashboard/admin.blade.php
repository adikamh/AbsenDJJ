@extends('dashboard.layout')

@section('title', 'Pembimbing Dashboard')
@section('header_title', 'Dashboard Pembimbing Lapangan')

@push('styles')
    @vite('resources/css/dashboard-admin.css')
@endpush

@push('scripts')
    @vite('resources/js/dashboard-admin.js')
@endpush

@section('content')
    <div class="stats-grid">
        <div class="stat-card hover-lift">
            <div class="stat-label">Anak Bimbingan Aktif</div>
            <div class="stat-value">{{ $interns->count() }} Orang</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Intern Hadir Hari Ini</div>
            <div class="stat-value">{{ $hadirTodayCount }} Orang</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Persetujuan Logbook Pending</div>
            <div class="stat-value">{{ $pendingLogbooks->count() }} Item</div>
        </div>
    </div>

    <div class="dashboard-row">
        <!-- Logbooks Pending Section -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Persetujuan Logbook Harian</h2>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Intern</th>
                            <th>Tanggal</th>
                            <th>Kegiatan</th>
                            <th>Deskripsi</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingLogbooks as $logbook)
                            <tr>
                                <td><strong>{{ $logbook->user->nama_lengkap }}</strong></td>
                                <td>{{ $logbook->tanggal->format('d M Y') }}</td>
                                <td>{{ $logbook->kegiatan }}</td>
                                <td><span class="muted-small">{{ Str::limit($logbook->deskripsi, 60) }}</span></td>
                                <td>
                                    <span class="badge badge-warning action-badge">Tinjau</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">Tidak ada logbook yang membutuhkan persetujuan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Guided Interns List -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Daftar Intern Anda</h2>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Instansi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($interns as $intern)
                            <tr>
                                <td><strong>{{ $intern->nama_lengkap }}</strong></td>
                                <td><span class="muted-small">{{ $intern->instansi?->nama_instansi ?? '-' }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="empty-state">Belum ada peserta yang dibimbing.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Leave Requests Section -->
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Pengajuan Izin / Sakit</h2>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama Intern</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Jenis</th>
                        <th>Alasan</th>
                        <th>Bukti</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingLeaves as $leave)
                        <tr>
                            <td><strong>{{ $leave->user->nama_lengkap }}</strong></td>
                            <td>{{ $leave->tanggal_mulai->format('d M Y') }}</td>
                            <td>{{ $leave->tanggal_selesai->format('d M Y') }}</td>
                            <td>
                                <span class="badge {{ $leave->jenis === 'Sakit' ? 'badge-danger' : 'badge-warning' }}">
                                    {{ $leave->jenis }}
                                </span>
                            </td>
                            <td>{{ $leave->alasan }}</td>
                            <td>
                                @if($leave->file_bukti)
                                    <a href="#" class="badge badge-info download-link">Unduh Bukti</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-success action-badge action-badge-spaced">Setujui</span>
                                <span class="badge badge-danger action-badge">Tolak</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">Tidak ada pengajuan izin yang tertunda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
