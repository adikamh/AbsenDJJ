@extends('dashboard.layout')

@section('title', 'Pembimbing Dashboard')
@section('header_title', 'Dashboard Pembimbing Lapangan')

@push('styles')
    @vite('resources/css/admin/dashboard.css')
@endpush

@push('scripts')
    @vite('resources/js/admin/dashboard.js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Today's Attendance Chart
            const ctxToday = document.getElementById('todayAttendanceChart').getContext('2d');
            const totalActivity = {{ $hadirTodayCount }} + {{ $terlambatTodayCount }} + {{ $izinSakitTodayCount }} + {{ $alfaTodayCount }};
            const hasData = totalActivity > 0;
            
            new Chart(ctxToday, {
                type: 'doughnut',
                data: {
                    labels: ['Tepat Waktu', 'Terlambat', 'Izin / Sakit', 'Belum Absen (Alfa)'],
                    datasets: [{
                        data: hasData 
                            ? [{{ $hadirTodayCount }}, {{ $terlambatTodayCount }}, {{ $izinSakitTodayCount }}, {{ $alfaTodayCount }}]
                            : [0, 0, 0, 1], // fallback if no data
                        backgroundColor: hasData
                            ? ['#10b981', '#fbbf24', '#3b82f6', '#ef4444']
                            : ['rgba(255,255,255,0.05)'],
                        borderWidth: 1,
                        borderColor: 'rgba(255,255,255,0.1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#a0aec0',
                                font: {
                                    family: 'Outfit, sans-serif',
                                    size: 11
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (!hasData) return ' Tidak ada aktivitas bimbingan hari ini';
                                    const value = context.raw;
                                    return ` ${context.label}: ${value} Orang`;
                                }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });

            // 2. Compliance Bar Chart
            const ctxCompliance = document.getElementById('complianceChart').getContext('2d');
            const complianceData = @json($internAttendanceData);
            const labels = complianceData.map(item => item.name);
            const rates = complianceData.map(item => item.rate);

            new Chart(ctxCompliance, {
                type: 'bar',
                data: {
                    labels: labels.length > 0 ? labels : ['Belum ada data'],
                    datasets: [{
                        label: 'Persentase Kehadiran (%)',
                        data: rates.length > 0 ? rates : [0],
                        backgroundColor: 'rgba(124, 58, 237, 0.45)',
                        borderColor: '#7c3aed',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        barPercentage: 0.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ` Kepatuhan: ${context.raw}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            max: 100,
                            ticks: {
                                color: '#a0aec0',
                                font: {
                                    family: 'Outfit, sans-serif'
                                }
                            },
                            grid: {
                                color: 'rgba(255,255,255,0.05)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#a0aec0',
                                font: {
                                    family: 'Outfit, sans-serif',
                                    size: 10
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush

@section('content')
    <!-- Premium Stats Grid -->
    <div class="stats-grid">
        <!-- Card 1: Guided Interns -->
        <div class="stat-card hover-lift">
            <div class="stat-info">
                <span class="stat-label">Anak Bimbingan Aktif</span>
                <span class="stat-value">{{ $totalInternsCount }} Orang</span>
            </div>
            <div class="stat-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
        </div>

        <!-- Card 2: Hadir Today -->
        <div class="stat-card hover-lift">
            <div class="stat-info">
                <span class="stat-label">Intern Hadir Hari Ini</span>
                <span class="stat-value">{{ $totalHadirToday }} Orang</span>
            </div>
            <div class="stat-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
        </div>

        <!-- Card 3: Pending Logbooks -->
        <div class="stat-card hover-lift">
            <div class="stat-info">
                <span class="stat-label">Pending Logbook</span>
                <span class="stat-value">{{ $totalPendingLogbooksCount }} Item</span>
            </div>
            <div class="stat-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
            </div>
        </div>

        <!-- Card 4: Pending Leaves -->
        <div class="stat-card hover-lift">
            <div class="stat-info">
                <span class="stat-label">Pending Izin / Sakit</span>
                <span class="stat-value">{{ $totalPendingLeavesCount }} Permohonan</span>
            </div>
            <div class="stat-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
        </div>
    </div>

    <!-- Analysis & Insights Section -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; margin-bottom: 30px;">
        <!-- Card 1: Today's Attendance Doughnut -->
        <div class="content-card" style="padding: 24px; display: flex; flex-direction: column;">
            <div style="border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 20px;">
                <h3 style="margin: 0; font-size: 1.1rem; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                    Analisis Kehadiran Hari Ini
                </h3>
            </div>
            <div style="flex-grow: 1; display: flex; align-items: center; justify-content: center; min-height: 240px; position: relative;">
                <canvas id="todayAttendanceChart"></canvas>
            </div>
        </div>

        <!-- Card 2: Attendance Rate Comparison Bar Chart -->
        <div class="content-card" style="padding: 24px; display: flex; flex-direction: column;">
            <div style="border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 20px;">
                <h3 style="margin: 0; font-size: 1.1rem; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    Tingkat Kepatuhan Absensi (%)
                </h3>
            </div>
            <div style="flex-grow: 1; display: flex; align-items: center; justify-content: center; min-height: 240px; position: relative;">
                <canvas id="complianceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Rows -->
    <div class="admin-dashboard-row">
        <!-- Logbooks Pending Section (Highlight Max 3) -->
        <div class="content-card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px;">
                <h2 class="card-title" style="margin: 0;">Persetujuan Logbook Harian</h2>
                <span class="badge badge-warning">{{ $totalPendingLogbooksCount }} Pending</span>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Intern</th>
                            <th>Tanggal</th>
                            <th>Kegiatan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingLogbooks as $logbook)
                            <tr>
                                <td>
                                    <strong>{{ $logbook->user->nama_lengkap }}</strong>
                                    <br>
                                    <span class="muted-small">{{ $logbook->user->instansi?->nama_instansi ?? '-' }}</span>
                                </td>
                                <td>{{ $logbook->tanggal->format('d M Y') }}</td>
                                <td>
                                    <strong>{{ $logbook->kegiatan }}</strong>
                                    <br>
                                    <span class="muted-small">{{ Str::limit($logbook->deskripsi, 45) }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-warning">Pending</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-state">Tidak ada logbook yang membutuhkan persetujuan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($totalPendingLogbooksCount > 3)
                <div class="card-footer" style="padding: 15px; text-align: center; border-top: 1px solid var(--glass-border);">
                    <a href="{{ route('admin.logbooks', ['status_approval' => 'Pending']) }}" class="btn-secondary" style="font-size: 0.85rem; text-decoration: none; padding: 8px 16px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                        Lihat Semua Pending ({{ $totalPendingLogbooksCount }})
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                </div>
            @endif
        </div>

        <!-- Guided Interns List (Highlight Max 3) -->
        <div class="content-card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px;">
                <h2 class="card-title" style="margin: 0;">Daftar Intern Anda</h2>
                <span class="badge badge-info">{{ $totalInternsCount }} Total</span>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Instansi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($interns as $intern)
                            <tr>
                                <td><strong>{{ $intern->nama_lengkap }}</strong></td>
                                <td><span class="muted-small">{{ $intern->instansi?->nama_instansi ?? '-' }}</span></td>
                                <td>
                                    <a href="{{ route('admin.interns.show', $intern->id) }}" class="badge badge-info" style="text-decoration: none; display: inline-block;">Lihat Profil</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">Belum ada peserta yang dibimbing.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($totalInternsCount > 3)
                <div class="card-footer" style="padding: 15px; text-align: center; border-top: 1px solid var(--glass-border);">
                    <a href="{{ route('admin.interns') }}" class="btn-secondary" style="font-size: 0.85rem; text-decoration: none; padding: 8px 16px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                        Kelola Semua Intern ({{ $totalInternsCount }})
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Leave Requests Section (Highlight Max 3) -->
    <div class="content-card" style="margin-top: 30px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px;">
            <h2 class="card-title" style="margin: 0;">Pengajuan Izin / Sakit Terbaru</h2>
            <span class="badge badge-danger">{{ $totalPendingLeavesCount }} Tertunda</span>
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
                            <td>
                                <strong>{{ $leave->user->nama_lengkap }}</strong>
                                <br>
                                <span class="muted-small">{{ $leave->user->instansi?->nama_instansi ?? '-' }}</span>
                            </td>
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
                                    <a href="{{ asset($leave->file_bukti) }}" target="_blank" class="badge badge-info download-link" style="text-decoration: none;">Unduh Bukti</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; flex-direction: column;">
                                    <button type="button" class="badge badge-success approve-btn" style="border: none; cursor: pointer; padding: 4px 8px; font-weight: 600; text-align: center;" data-type="Izin/Sakit" data-action-url="{{ route('admin.leave.approve', $leave->id) }}">Setujui</button>
                                    <button type="button" class="badge badge-danger reject-btn" style="border: none; cursor: pointer; padding: 4px 8px; font-weight: 600; text-align: center;" data-type="Izin/Sakit" data-action-url="{{ route('admin.leave.reject', $leave->id) }}">Tolak</button>
                                </div>
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
        @if($totalPendingLeavesCount > 3)
            <div class="card-footer" style="padding: 15px; text-align: center; border-top: 1px solid var(--glass-border);">
                <a href="{{ route('admin.leaves', ['status_approval' => 'Pending']) }}" class="btn-secondary" style="font-size: 0.85rem; text-decoration: none; padding: 8px 16px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                    Lihat Semua Pending ({{ $totalPendingLeavesCount }})
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
            </div>
        @endif
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
    @vite('resources/js/admin/dashboard.js')
@endpush
