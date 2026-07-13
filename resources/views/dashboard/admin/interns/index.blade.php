@extends('dashboard.layout')

@section('title', 'Daftar Anak Bimbingan')
@section('header_title', 'Daftar Anak Bimbingan')

@push('styles')
    @vite('resources/css/admin/interns.css')
@endpush

@section('content')
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="stat-card hover-lift" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; backdrop-filter: blur(10px);">
            <div class="stat-label" style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">Total Anak Bimbingan</div>
            <div class="stat-value" style="font-size: 1.6rem; font-weight: 700; color: var(--text-primary); margin-top: 6px;">{{ $totalInternsCount }} Orang</div>
        </div>
        <div class="stat-card hover-lift" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; backdrop-filter: blur(10px);">
            <div class="stat-label" style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">Hadir Hari Ini</div>
            <div class="stat-value" style="font-size: 1.6rem; font-weight: 700; color: #34d399; margin-top: 6px;">{{ $activeTodayCount }} Orang</div>
        </div>
        <div class="stat-card hover-lift" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; backdrop-filter: blur(10px);">
            <div class="stat-label" style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">Izin / Sakit Hari Ini</div>
            <div class="stat-value" style="font-size: 1.6rem; font-weight: 700; color: #fbbf24; margin-top: 6px;">{{ $onLeaveTodayCount }} Orang</div>
        </div>
    </div>

    <!-- Interns List Card (Filter and Table Unified) -->
    <div class="content-card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; padding: 20px; border-bottom: 1px solid var(--glass-border);">
            <h2 class="card-title" style="margin: 0;">Daftar Aktivitas Intern</h2>
            
            <!-- Unified Inline Search Form -->
            <form action="{{ route('admin.interns') }}" method="GET" style="margin: 0; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <input type="text" name="search" value="{{ request('search') }}" class="filter-input" placeholder="Cari nama peserta..." style="padding: 8px 12px; font-size: 0.85rem; width: 220px; border: 1px solid var(--glass-border); border-radius: 6px; background: rgba(255,255,255,0.05); color: var(--text-primary);" onchange="this.form.submit()">
                <button type="submit" class="btn-primary" style="padding: 8px 16px; font-size: 0.85rem; border-radius: 6px; cursor: pointer;">Cari</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.interns') }}" class="btn-secondary" style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none; border-radius: 6px; display: inline-block;">Reset</a>
                @endif
            </form>
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

        @if($interns->hasPages())
            {{ $interns->links('partials.pagination') }}
        @endif
    </div>
@endsection
