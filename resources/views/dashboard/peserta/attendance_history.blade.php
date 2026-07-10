@extends('dashboard.layout')

@section('title', 'Riwayat Absensi')
@section('header_title', 'Riwayat Absensi')

@push('styles')
    @vite('resources/css/peserta/dashboard.css')
    <style>
        .calendar-wrapper {
            margin-top: 20px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .calendar-filter {
            display: flex;
            gap: 10px;
        }

        .calendar-filter select {
            padding: 8px 12px;
            border-radius: 8px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            font-family: inherit;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .calendar-day-header {
            text-align: center;
            font-weight: 600;
            padding: 10px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 8px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .calendar-day {
            min-height: 100px;
            padding: 8px;
            background: rgba(255, 255, 255, 0.01);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s ease;
        }

        .calendar-day:hover {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .calendar-day.empty {
            background: transparent;
            border: none;
            pointer-events: none;
        }

        .day-number {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .calendar-day.today .day-number {
            color: var(--accent-primary);
            font-weight: bold;
        }

        .day-status {
            margin-top: 10px;
        }

        .day-status .badge {
            display: block;
            text-align: center;
            font-size: 0.72rem;
            padding: 4px 6px;
            border-radius: 6px;
            font-weight: 600;
        }

        .legend-card {
            background: rgba(255, 255, 255, 0.01);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 20px;
            margin-top: 24px;
        }

        .legend-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 12px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
        }

        .legend-color.hadir { background: rgba(52, 211, 153, 0.15); border: 1px solid #34d399; }
        .legend-color.terlambat { background: rgba(251, 191, 36, 0.15); border: 1px solid #fbbf24; }
        .legend-color.izin { background: rgba(96, 165, 250, 0.15); border: 1px solid #60a5fa; }
        .legend-color.alfa { background: rgba(248, 113, 113, 0.15); border: 1px solid #f87171; }
        .legend-color.holiday { background: rgba(156, 163, 175, 0.15); border: 1px solid #9ca3af; }
    </style>
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
            
            <form action="{{ route('peserta.attendance') }}" method="GET" class="calendar-filter">
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
