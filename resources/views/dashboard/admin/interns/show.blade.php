@extends('dashboard.layout')

@section('title', 'Detail Aktivitas Intern')
@section('header_title', 'Detail Aktivitas Intern: ' . $intern->nama_lengkap)

@push('styles')
    @vite(['resources/css/admin/interns.css', 'resources/css/peserta/dashboard.css', 'resources/css/show.css'])
@endpush

@section('content')
    <!-- Back Button -->
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.interns') }}" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Kembali ke Daftar
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
            <div class="stat-label">Izin / Sakit</div>
            <div class="stat-value" style="color: var(--accent-info);">{{ $leaveCount + $sickCount }} Hari</div>
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
                            Cetak Laporan Kehadiran (PDF)
                        </a>
                        <a href="{{ route('peserta.consolidated-report', ['user_id' => $intern->id]) }}" target="_blank" class="btn-primary" style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none; font-weight: 600; background: #059669; border-color: #059669;">
                            Cetak Rekap Keseluruhan (PDF)
                        </a>
                        <a href="{{ route('peserta.attendance.csv', ['user_id' => $intern->id]) }}" class="btn-secondary" style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none; font-weight: 600;">
                            Rekap Absen (CSV)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. Visual Kalender Card -->
    <div class="content-card" style="margin-bottom: 30px; padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px;">
            <h3 style="margin: 0; font-size: 1.1rem; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                Visual Kalender Kehadiran: {{ $selectedDate->translatedFormat('F Y') }}
            </h3>
            <form action="{{ route('admin.interns.show', $intern->id) }}" method="GET" style="margin: 0; display: flex; gap: 10px;">
                <select name="month" onchange="this.form.submit()" style="padding: 6px 12px; font-size: 0.85rem; border: 1px solid var(--glass-border); border-radius: 6px; background: rgba(0,0,0,0.2); color: var(--text-primary);">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(now()->year, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                <select name="year" onchange="this.form.submit()" style="padding: 6px 12px; font-size: 0.85rem; border: 1px solid var(--glass-border); border-radius: 6px; background: rgba(0,0,0,0.2); color: var(--text-primary);">
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

                        $attendance = $calendarAttendances->get($loopDateStr);
                        $hasSelfie = $attendance && ($attendance->foto_masuk || $attendance->foto_pulang);
                    @endphp

                    <div class="calendar-day {{ $isToday ? 'today' : '' }} {{ $hasSelfie ? 'has-selfie-photo' : '' }}" 
                         style="{{ $isHoliday ? 'background: rgba(156, 163, 175, 0.02);' : '' }} {{ $hasSelfie ? 'cursor: pointer;' : '' }}"
                         @if($hasSelfie)
                            onclick="showSelfiePopup('{{ $attendance->foto_masuk ? asset($attendance->foto_masuk) : '' }}', '{{ $attendance->foto_pulang ? asset($attendance->foto_pulang) : '' }}', '{{ $loopDate->translatedFormat('d M Y') }}')"
                         @endif>
                        <div class="day-number">{{ $day }}</div>
                        
                        <div class="day-status">
                            @if($attendance)
                                <span class="badge {{ $attendance->status === 'Hadir' ? 'badge-success' : ($attendance->status === 'Terlambat' ? 'badge-warning' : ($attendance->status === 'Tanpa Keterangan' ? 'badge-danger' : 'badge-info')) }}">
                                    {{ $attendance->status === 'Tanpa Keterangan' ? 'Alfa' : $attendance->status }}
                                    @if($attendance->jam_masuk)
                                        <div style="font-size: 0.65rem; font-weight: normal; margin-top: 2px;">
                                            {{ \Carbon\Carbon::parse($attendance->jam_masuk)->format('H:i') }}
                                        </div>
                                    @endif
                                </span>
                                
                                @if($hasSelfie)
                                    <div style="display: flex; justify-content: center; margin-top: 8px; color: var(--accent-primary);">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" title="Klik untuk lihat foto"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                    </div>
                                @endif
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

        <!-- Legend Explanation Card -->
        <div class="legend-card" style="margin-top: 20px; background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 12px; padding: 15px;">
            <h4 style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary); margin: 0 0 10px 0;">Keterangan Status Absensi</h4>
            <div class="legend-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--text-secondary);">
                    <div style="width: 12px; height: 12px; border-radius: 3px; background: #10b981;"></div>
                    <span>Hadir Tepat Waktu</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--text-secondary);">
                    <div style="width: 12px; height: 12px; border-radius: 3px; background: #fbbf24;"></div>
                    <span>Terlambat Masuk</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--text-secondary);">
                    <div style="width: 12px; height: 12px; border-radius: 3px; background: #3b82f6;"></div>
                    <span>Izin / Sakit Resmi</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--text-secondary);">
                    <div style="width: 12px; height: 12px; border-radius: 3px; background: #ef4444;"></div>
                    <span>Alfa / Tanpa Keterangan</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Tabel Riwayat Absensi Card -->
    <div class="content-card" style="margin-bottom: 30px; padding: 24px;">
        <div style="border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 1.1rem; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                Tabel Riwayat Absensi
            </h3>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                        <th>Jarak Absen</th>
                        <th>Foto Absen</th>
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
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    @if($attendance->foto_masuk)
                                        <button type="button" 
                                                class="badge badge-success clickable-selfie" 
                                                style="border: none; cursor: pointer; padding: 4px 8px;" 
                                                onclick="showImageModal('{{ asset($attendance->foto_masuk) }}', 'Selfie Masuk - {{ $attendance->tanggal->format('d M Y') }}')">
                                            Masuk
                                        </button>
                                    @else
                                        <span class="muted-small">-</span>
                                    @endif

                                    @if($attendance->foto_pulang)
                                        <button type="button" 
                                                class="badge badge-warning clickable-selfie" 
                                                style="border: none; cursor: pointer; padding: 4px 8px;" 
                                                onclick="showImageModal('{{ asset($attendance->foto_pulang) }}', 'Selfie Pulang - {{ $attendance->tanggal->format('d M Y') }}')">
                                            Pulang
                                        </button>
                                    @else
                                        <span class="muted-small">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">Belum ada riwayat absensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($attendances->hasPages())
            {{ $attendances->links('partials.pagination') }}
        @endif
    </div>


    {{-- ===== Modal: Selfie Preview Popup ===== --}}
    <div class="form-modal-backdrop" id="selfie-modal" oncontextmenu="return false;">
        <div class="form-modal-content" style="position: relative; max-width: 360px; padding: 15px; border-radius: 20px;">
            <button type="button" id="close-selfie-modal" class="modal-close" style="position: absolute; top: -5px; right: -5px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; z-index: 10;">&times;</button>
            <img id="modal-selfie-img" src="" alt="Enlarged Selfie" draggable="false" oncontextmenu="return false;" style="width: 100%; max-height: 420px; object-fit: contain; border-radius: 16px; border: 1px solid var(--glass-border); box-shadow: 0 10px 30px rgba(0,0,0,0.5); display: block; pointer-events: none; user-select: none;">
            <div id="modal-selfie-title" style="text-align: center; margin-top: 12px; font-weight: 600; color: #fff; font-size: 0.95rem; font-family: 'Outfit', sans-serif;"></div>
        </div>
    </div>

    {{-- ===== Modal: Daily Selfie Photos Popup ===== --}}
    <div class="form-modal-backdrop" id="daily-photos-modal" oncontextmenu="return false;">
        <div class="form-modal-content" style="position: relative; max-width: 520px; padding: 24px; border-radius: 20px; background: rgba(30, 30, 50, 0.95); border: 1px solid var(--glass-border); backdrop-filter: blur(20px);">
            <button type="button" id="close-photos-modal" class="modal-close" style="position: absolute; top: 12px; right: 12px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; z-index: 10; border: none; background: rgba(255,255,255,0.1); color: #fff; border-radius: 50%; font-size: 1.5rem; cursor: pointer;">&times;</button>
            <h3 id="modal-photos-title" style="margin-top: 0; margin-bottom: 20px; font-weight: 600; color: #fff; font-size: 1.1rem; font-family: 'Outfit', sans-serif; text-align: center;"></h3>
            
            <div style="display: flex; gap: 20px; justify-content: center; align-items: center; flex-wrap: nowrap; margin-top: 15px;">
                <!-- Selfie Masuk container -->
                <div id="modal-masuk-container" style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: #34d399;">Selfie Masuk</span>
                    <img id="modal-foto-masuk" src="" alt="Selfie Masuk" draggable="false" oncontextmenu="return false;" style="max-width: 220px; max-height: 280px; width: auto; height: auto; border-radius: 12px; border: 1px solid var(--glass-border); object-fit: contain; display: block; pointer-events: none; user-select: none;">
                </div>
                
                <!-- Selfie Pulang container -->
                <div id="modal-pulang-container" style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: #fbbf24;">Selfie Pulang</span>
                    <img id="modal-foto-pulang" src="" alt="Selfie Pulang" draggable="false" oncontextmenu="return false;" style="max-width: 220px; max-height: 280px; width: auto; height: auto; border-radius: 12px; border: 1px solid var(--glass-border); object-fit: contain; display: block; pointer-events: none; user-select: none;">
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/show.js')
    @endpush
@endsection
