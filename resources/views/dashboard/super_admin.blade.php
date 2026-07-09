@extends('dashboard.layout')

@section('title', 'Super Admin Dashboard')
@section('header_title', 'Super Admin Overview')

@push('styles')
    @vite('resources/css/dashboard-super-admin.css')
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @vite('resources/js/dashboard-super-admin.js')
@endpush

@section('content')
    <div class="stats-grid">
        <div class="stat-card hover-lift">
            <div class="stat-label">Total Pengguna</div>
            <div class="stat-value">{{ $totalUsers }}</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Instansi Terdaftar</div>
            <div class="stat-value">{{ $totalInstansi }}</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Peserta Hadir Hari Ini</div>
            <div class="stat-value">{{ $totalHadirHariIni }}</div>
        </div>
    </div>

    <!-- Attendance Analytics Chart -->
    <div class="content-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2 class="card-title">Analisis Kehadiran (7 Hari Kerja Terakhir)</h2>
        </div>
        <div class="chart-container" style="position: relative; height: 350px; width: 100%; margin-top: 16px;">
            <div id="attendanceChart" data-chart-data="{{ json_encode($attendanceChartData) }}"></div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Pengguna Baru Terdaftar</h2>
            <a href="{{ route('super-admin.users') }}" class="badge badge-info manage-link">Kelola</a>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Hak Akses</th>
                        <th>Instansi</th>
                        <th>Tanggal Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentUsers as $user)
                        <tr>
                            <td><strong>{{ $user->nama_lengkap }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->isSuperAdmin() ? 'badge-danger' : ($user->isAdmin() ? 'badge-info' : 'badge-success') }}">
                                    {{ $user->role->nama_role }}
                                </span>
                            </td>
                            <td>{{ $user->instansi?->nama_instansi ?? '-' }}</td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
