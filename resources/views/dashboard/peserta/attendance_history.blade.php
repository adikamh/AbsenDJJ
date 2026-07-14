@extends('dashboard.layout')

@section('title', 'Riwayat Absensi')
@section('header_title', 'Riwayat Absensi')

@push('styles')
    @vite('resources/css/peserta/dashboard.css')
@endpush

@section('content')
    <!-- Monthly Stats Summary -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card hover-lift">
            <div class="stat-label">Hadir Tepat Waktu</div>
            <div class="stat-value text-success" style="color: #34d399;">{{ $stats['hadir'] }} Hari</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Terlambat</div>
            <div class="stat-value text-warning" style="color: #fbbf24;">{{ $stats['terlambat'] }} Hari</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Izin & Sakit</div>
            <div class="stat-value text-info" style="color: #60a5fa;">{{ $stats['izin'] }} Hari</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Tanpa Keterangan (Alfa)</div>
            <div class="stat-value text-danger" style="color: #f87171;">{{ $stats['absen'] }} Hari</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Persentase Kehadiran</div>
            <div class="stat-value" style="color: #ffcc33;">{{ $attendanceRate }}%</div>
        </div>
    </div>

    <!-- Main Calendar Card -->
    <div class="content-card">
        <div class="calendar-header">
            <h2 class="card-title">{{ $selectedDate->translatedFormat('F Y') }}</h2>
            
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <form action="{{ route('peserta.attendance') }}" method="GET" class="calendar-filter" style="margin: 0;">
                    <select name="month" onchange="this.form.submit()">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                    <select name="year" onchange="this.form.submit()">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </form>
                <a href="{{ route('peserta.monthly-report', ['month' => $month, 'year' => $year]) }}" 
                   target="_blank" 
                   class="btn-primary" 
                   style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none; font-size: 0.85rem; padding: 8px 16px; border-radius: 8px; line-height: 1.2;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Cetak Laporan Kehadiran
                </a>
                <a href="{{ route('peserta.consolidated-report', ['month' => $month, 'year' => $year]) }}" 
                   target="_blank" 
                   class="btn-primary" 
                   style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none; font-size: 0.85rem; padding: 8px 16px; border-radius: 8px; line-height: 1.2; background: #059669; border-color: #059669;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Cetak Rekap Keseluruhan
                </a>
                <a href="{{ route('peserta.attendance.csv', ['month' => $month, 'year' => $year]) }}" 
                   class="btn-secondary" 
                   style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none; font-size: 0.85rem; padding: 8px 16px; border-radius: 8px; line-height: 1.2;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Ekspor CSV Absen
                </a>
            </div>
        </div>

        <div class="calendar-wrapper">
            <div class="calendar-grid">
                <!-- Day Headers -->
                <div class="calendar-day-header">Sen</div>
                <div class="calendar-day-header">Sel</div>
                <div class="calendar-day-header">Rab</div>
                <div class="calendar-day-header">Kam</div>
                <div class="calendar-day-header">Jum</div>
                <div class="calendar-day-header">Sab</div>
                <div class="calendar-day-header">Min</div>

                <!-- Empty cells before first day of month -->
                @php
                    $firstDayOfWeek = $selectedDate->copy()->startOfMonth()->dayOfWeekIso;
                    $daysInMonth = $selectedDate->daysInMonth;
                    $todayDate = now()->toDateString();
                @endphp

                @for($i = 1; $i < $firstDayOfWeek; $i++)
                    <div class="calendar-day empty"></div>
                @endfor

                <!-- Days of month -->
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $loopDate = Carbon\Carbon::create($year, $month, $day);
                        $loopDateStr = $loopDate->toDateString();
                        $isWeekend = $loopDate->isWeekend();
                        $isToday = $loopDateStr === $todayDate;
                        
                        $customSchedule = $schedules->get($loopDateStr);
                        $isHoliday = $isWeekend || ($customSchedule && $customSchedule->is_holiday);
                        $holidayName = ($customSchedule && $customSchedule->is_holiday) ? $customSchedule->keterangan : ($isWeekend ? 'Akhir Pekan' : null);

                        $attendance = $attendances->get($loopDateStr);
                    @endphp

                    <div class="calendar-day {{ $isToday ? 'today' : '' }}" style="{{ $isHoliday ? 'background: rgba(156, 163, 175, 0.02);' : '' }}">
                        <div class="day-number">{{ $day }}</div>
                        
                        <div class="day-status">
                            @if($attendance)
                                <span class="badge {{ $attendance->status === 'Hadir' ? 'badge-success' : ($attendance->status === 'Terlambat' ? 'badge-warning' : ($attendance->status === 'Tanpa Keterangan' ? 'badge-danger' : 'badge-info')) }}">
                                    {{ $attendance->status }}
                                    @if($attendance->jam_masuk)
                                        <div style="font-size: 0.65rem; font-weight: normal; margin-top: 2px;">
                                            {{ \Carbon\Carbon::parse($attendance->jam_masuk)->format('H:i') }}
                                        </div>
                                    @endif
                                </span>
                            @elseif($isHoliday)
                                <span class="badge" style="background: rgba(156, 163, 175, 0.1); border: 1px solid #9ca3af; color: #9ca3af;" title="{{ $holidayName }}">
                                    Libur
                                </span>
                            @elseif($loopDate->lessThan(now()->startOfDay()))
                                <span class="badge badge-danger">
                                    Alfa
                                </span>
                            @else
                                <span class="badge" style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); color: var(--text-secondary);">
                                    -
                                </span>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Legend Explanation Card -->
    <div class="legend-card">
        <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-primary);">Keterangan Status Absensi</h3>
        <div class="legend-grid">
            <div class="legend-item">
                <div class="legend-color hadir"></div>
                <span>Hadir Tepat Waktu</span>
            </div>
            <div class="legend-item">
                <div class="legend-color terlambat"></div>
                <span>Terlambat Masuk</span>
            </div>
            <div class="legend-item">
                <div class="legend-color izin"></div>
                <span>Izin / Sakit Resmi</span>
            </div>
            <div class="legend-item">
                <div class="legend-color alfa"></div>
                <span>Alfa / Tidak Hadir</span>
            </div>
            <div class="legend-item">
                <div class="legend-color holiday"></div>
                <span>Hari Libur / Akhir Pekan</span>
            </div>
        </div>
    </div>
@endsection
