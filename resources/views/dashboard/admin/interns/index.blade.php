@extends('dashboard.layout')

@section('title', 'Daftar Anak Bimbingan')
@section('header_title', 'Daftar Anak Bimbingan')

@push('styles')
    @vite('resources/css/admin/interns.css')
@endpush

@section('content')
    <div class="stats-grid">
        <div class="stat-card hover-lift">
            <div class="stat-label">Total Anak Bimbingan</div>
            <div class="stat-value">{{ $totalInternsCount }} Orang</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Hadir Hari Ini</div>
            <div class="stat-value">{{ $activeTodayCount }} Orang</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Izin / Sakit Hari Ini</div>
            <div class="stat-value">{{ $onLeaveTodayCount }} Orang</div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="content-card" style="margin-bottom: 30px;">
        <div class="card-header">
            <h2 class="card-title">Cari Anak Bimbingan</h2>
        </div>
        <form action="{{ route('admin.interns') }}" method="GET" class="filter-row" style="padding: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <div class="filter-group" style="flex: 1; min-width: 250px; display: flex; flex-direction: column; gap: 8px;">
                <label for="search" style="font-weight: 500; font-size: 0.85rem; color: var(--text-secondary);">Nama Peserta Magang</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" class="filter-input" placeholder="Masukkan nama peserta magang..." style="width: 100%;" onchange="this.form.submit()">
            </div>
            <div class="filter-actions" style="margin-top: 26px;">
                <button type="submit" class="btn-primary" style="padding: 10px 20px;">Cari</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.interns') }}" class="btn-secondary" style="padding: 10px 20px; text-decoration: none; display: inline-block;">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Interns List Card -->
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Daftar Aktivitas Intern</h2>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama Peserta</th>
                        <th>Instansi</th>
                        <th>Status Hari Ini</th>
                        <th>Total Hadir</th>
                        <th>Persentase Kehadiran</th>
                        <th>Logbook Disetujui</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($interns as $intern)
                        <tr>
                            <td>
                                <a href="{{ route('admin.interns.show', $intern->id) }}" style="color: var(--text-primary); text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                                    {{ $intern->nama_lengkap }}
                                </a>
                                <br>
                                <span class="muted-small">{{ $intern->email }}</span>
                            </td>
                            <td>{{ $intern->instansi?->nama_instansi ?? '-' }}</td>
                            <td>
                                @if($intern->today_status === 'Hadir')
                                    <span class="badge badge-success">Hadir</span>
                                @elseif($intern->today_status === 'Terlambat')
                                    <span class="badge badge-warning">Terlambat</span>
                                @elseif($intern->today_status === 'Izin' || $intern->today_status === 'Sakit')
                                    <span class="badge badge-info">{{ $intern->today_status }}</span>
                                @else
                                    <span class="badge" style="background: rgba(255, 255, 255, 0.1); color: var(--text-secondary);">{{ $intern->today_status }}</span>
                                @endif
                            </td>
                            <td><strong>{{ $intern->total_present }} Hari</strong></td>
                            <td>
                                <div class="progress-bar-container" style="width: 100%; max-width: 120px; background: rgba(255,255,255,0.05); height: 8px; border-radius: 4px; overflow: hidden; display: inline-block; vertical-align: middle; margin-right: 8px;">
                                    <div class="progress-bar-value" style="width: {{ $intern->attendance_rate }}%; background: {{ $intern->attendance_rate >= 80 ? 'var(--accent-primary)' : '#ef4444' }}; height: 100%; border-radius: 4px;"></div>
                                </div>
                                <span style="font-size: 0.85rem; font-weight: 600;">{{ $intern->attendance_rate }}%</span>
                            </td>
                            <td><strong style="color: var(--accent-primary);">{{ $intern->total_logbook }} Hari</strong></td>
                            <td>
                                <a href="{{ route('admin.interns.show', $intern->id) }}" class="btn-primary" style="padding: 6px 12px; font-size: 0.8rem; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">Lihat Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">Tidak ada anak bimbingan yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
