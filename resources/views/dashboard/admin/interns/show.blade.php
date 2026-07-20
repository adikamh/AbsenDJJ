@extends('dashboard.layout')

<!-- VIEW_DEBUG_ADMIN_ACTIVE_v2 -->

@section('title', 'Detail Aktivitas Intern')
@section('header_title', 'Detail Aktivitas Intern: ' . $intern->nama_lengkap)

@push('styles')
    @vite(['resources/css/admin/interns.css', 'resources/css/peserta/dashboard.css', 'resources/css/show.css'])
    <style>
        .custom-select-wrapper {
            position: relative;
            width: 100%;
        }
        .custom-select-trigger {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid var(--glass-border);
            background: rgba(0, 0, 0, 0.2);
            color: #fff;
            font-size: 0.75rem;
            text-align: left;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            outline: none;
            font-family: inherit;
            transition: border-color 0.2s;
        }
        .custom-select-trigger:hover, .custom-select-trigger:focus {
            border-color: rgba(255, 255, 255, 0.25);
        }
        .custom-select-trigger::after {
            content: '';
            border: solid #9ca3af;
            border-width: 0 2px 2px 0;
            display: inline-block;
            padding: 3px;
            transform: rotate(45deg);
            transition: transform 0.2s;
        }
        .custom-select-wrapper.is-open .custom-select-trigger::after {
            transform: rotate(-135deg);
        }
        .custom-select-options {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #1e293b;
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            margin-top: 4px;
            max-height: 160px; /* displays ~5 items */
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.6);
        }
        .custom-select-options::-webkit-scrollbar {
            width: 6px;
        }
        .custom-select-options::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .custom-select-options::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px;
        }
        .custom-select-options::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .custom-select-wrapper.is-open .custom-select-options {
            display: block;
        }
        .custom-select-option {
            padding: 8px 14px;
            color: #e2e8f0;
            font-size: 0.75rem;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }
        .custom-select-option:hover {
            background: rgba(99, 102, 241, 0.2);
            color: #fff;
        }
        /* === MOBILE-FIRST DESIGN (DEFAULT UNTUK HP) === */
        .form-modal-backdrop {
            z-index: 99999 !important;
            padding: 10px 8px !important; /* Diperkecil agar hemat ruang samping */
            align-items: flex-start !important;
            overflow-y: auto !important;
            box-sizing: border-box !important;
        }
        .form-modal {
            margin: 10px auto 80px auto !important;
            max-height: none !important;
            height: auto !important;
            padding: 16px 12px !important; /* Diperkecil sedikit */
            overflow: hidden !important; /* Cegah kebocoran elemen ke kanan */
            width: 100% !important; /* Paksa ambil lebar penuh area padding */
            max-width: calc(100vw - 16px) !important; /* Kunci mati agar tidak bisa melebihi layar */
            box-sizing: border-box !important;
        }
        .print-modal-content {
            max-height: none !important;
            overflow-x: hidden !important; /* Kunci konten agar tidak overflow horizontal */
            overflow-y: visible !important;
            display: flex;
            flex-direction: column;
            gap: 16px;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        /* Force Reset untuk Semua Komponen Form di Dalam Modal */
        .form-modal .grid-2-col,
        .form-modal .sig-grid {
            display: flex !important;
            flex-direction: column !important; /* Paksa menumpuk ke bawah di HP */
            width: 100% !important;
            gap: 12px !important;
        }
        .form-modal .form-group {
            width: 100% !important;
            clear: both !important;
            box-sizing: border-box !important;
        }
        .form-modal input[type="text"],
        .form-modal input[type="date"],
        .form-modal input[type="file"],
        .form-modal select {
            width: 100% !important; /* Paksa lebar 100% mengikuti batas modal */
            max-width: 100% !important;
            box-sizing: border-box !important; /* Deteksi padding sebagai bagian dari lebar */
            display: block !important;
        }

        /* === DESKTOP & TABLET LAYOUT (LEBAR >= 768px) === */
        @media (min-width: 768px) {
            .form-modal-backdrop {
                padding: 24px !important;
                align-items: center !important;
                overflow-y: hidden !important;
            }
            .form-modal {
                margin: 0 auto !important;
                max-height: calc(100vh - 48px) !important;
                padding: 24px !important;
                width: min(100%, 720px) !important;
                overflow-y: auto !important;
            }
            .print-modal-content {
                max-height: min(420px, calc(100vh - 220px)) !important;
                overflow-y: auto !important;
                padding-right: 8px !important;
            }
            .print-modal-footer {
                flex-direction: row !important;
                justify-content: flex-end !important;
                gap: 10px !important;
            }
            .print-modal-footer button {
                width: auto !important;
                padding: 10px 18px !important;
            }
            .print-modal-footer button:nth-child(1) {
                order: 1 !important; /* Batal di kiri */
            }
            .print-modal-footer button:nth-child(2) {
                order: 2 !important; /* Cetak PDF di tengah */
            }
            .print-modal-footer button:nth-child(3) {
                order: 3 !important; /* Export Word di kanan */
            }
            .grid-2-col, .sig-grid {
                grid-template-columns: 1fr 1fr !important; /* 2 kolom di desktop */
                gap: 15px !important;
            }
            .color-picker-grid {
                grid-column: span 2 !important;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $formatWa = function($phone) {
            if (!$phone) return '';
            $clean = preg_replace('/[^0-9]/', '', $phone);
            if (strpos($clean, '0') === 0) {
                $clean = '62' . substr($clean, 1);
            } elseif (strpos($clean, '62') !== 0) {
                $clean = '62' . $clean;
            }
            return $clean;
        };
    @endphp

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
                <div style="display: flex; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center;">
                        <span style="font-weight: 600; width: 140px; color: var(--text-secondary);">No Telepon</span>
                        <span style="color: var(--text-primary);">{{ $intern->no_telepon ?? '-' }}</span>
                    </div>
                    @if($intern->no_telepon)
                        <a href="https://api.whatsapp.com/send/?phone={{ $formatWa($intern->no_telepon) }}" target="_blank" class="btn-wa-shortcut" title="Hubungi via WhatsApp" style="display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: #25d366; color: #fff; box-shadow: 0 4px 8px rgba(37, 211, 102, 0.25); transition: all 0.2s ease;" onmouseover="this.style.transform='scale(1.1)';" onmouseout="this.style.transform='scale(1)';">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.501-5.736-1.455L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.864-9.864.002-2.637-1.03-5.115-2.906-6.99C16.556 1.876 14.077.842 11.44.842 6.005.842 1.58 5.26 1.577 10.697c-.001 1.705.452 3.37 1.312 4.825L1.875 21.03l5.825-1.528.003-.004z"/>
                            </svg>
                        </a>
                    @endif
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
                <!-- Emergency Contact 1 -->
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <span style="font-weight: 600; color: var(--text-secondary); font-size: 0.85rem;">Kontak Darurat 1</span>
                    <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--glass-border); border-radius: 12px; padding: 12px 16px;">
                        <div>
                            <strong style="color: var(--text-primary); display: block; font-size: 0.95rem;">{{ $intern->nama_darurat_1 ?? 'Nama tidak diisi' }}</strong>
                            <span style="color: var(--text-secondary); font-size: 0.82rem; display: block; margin-top: 2px;">
                                Hubungan: {{ $intern->hubungan_darurat_1 ?? '-' }}
                            </span>
                            <span style="color: var(--text-primary); font-size: 0.85rem; font-family: monospace; display: block; margin-top: 4px;">
                                {{ $intern->no_darurat_1 ?? '-' }}
                            </span>
                        </div>
                        @if($intern->no_darurat_1)
                            <a href="https://api.whatsapp.com/send/?phone={{ $formatWa($intern->no_darurat_1) }}" target="_blank" class="btn-wa-shortcut" title="Hubungi via WhatsApp" style="display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 50%; background: #25d366; color: #fff; box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3); transition: all 0.2s ease;" onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 6px 15px rgba(37, 211, 102, 0.45)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 10px rgba(37, 211, 102, 0.3)';">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.501-5.736-1.455L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.864-9.864.002-2.637-1.03-5.115-2.906-6.99C16.556 1.876 14.077.842 11.44.842 6.005.842 1.58 5.26 1.577 10.697c-.001 1.705.452 3.37 1.312 4.825L1.875 21.03l5.825-1.528.003-.004z"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Emergency Contact 2 -->
                @if($intern->no_darurat_2 || $intern->nama_darurat_2)
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <span style="font-weight: 600; color: var(--text-secondary); font-size: 0.85rem;">Kontak Darurat 2</span>
                        <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--glass-border); border-radius: 12px; padding: 12px 16px;">
                            <div>
                                <strong style="color: var(--text-primary); display: block; font-size: 0.95rem;">{{ $intern->nama_darurat_2 ?? 'Nama tidak diisi' }}</strong>
                                <span style="color: var(--text-secondary); font-size: 0.82rem; display: block; margin-top: 2px;">
                                    Hubungan: {{ $intern->hubungan_darurat_2 ?? '-' }}
                                </span>
                                <span style="color: var(--text-primary); font-size: 0.85rem; font-family: monospace; display: block; margin-top: 4px;">
                                    {{ $intern->no_darurat_2 ?? '-' }}
                                </span>
                            </div>
                            @if($intern->no_darurat_2)
                                <a href="https://api.whatsapp.com/send/?phone={{ $formatWa($intern->no_darurat_2) }}" target="_blank" class="btn-wa-shortcut" title="Hubungi via WhatsApp" style="display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 50%; background: #25d366; color: #fff; box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3); transition: all 0.2s ease;" onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 6px 15px rgba(37, 211, 102, 0.45)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 10px rgba(37, 211, 102, 0.3)';">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.501-5.736-1.455L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.864-9.864.002-2.637-1.03-5.115-2.906-6.99C16.556 1.876 14.077.842 11.44.842 6.005.842 1.58 5.26 1.577 10.697c-.001 1.705.452 3.37 1.312 4.825L1.875 21.03l5.825-1.528.003-.004z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <hr style="border: 0; border-top: 1px solid var(--glass-border); margin: 0;">

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <span style="font-weight: 600; color: var(--text-secondary); font-size: 0.85rem;">Cetak Laporan / Rekap Kegiatan</span>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <a href="{{ route('peserta.monthly-report', ['user_id' => $intern->user_code]) }}" target="_blank" class="btn-primary" style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none; font-weight: 600;">
                            Cetak Laporan Kehadiran (PDF)
                        </a>
                        <a href="{{ route('peserta.consolidated-report', ['user_id' => $intern->user_code]) }}" target="_blank" class="btn-primary" style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none; font-weight: 600; background: #059669; border-color: #059669;">
                            Cetak Rekap Keseluruhan (PDF)
                        </a>
                        <a href="{{ route('peserta.attendance.csv', ['user_id' => $intern->user_code]) }}" class="btn-secondary" style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none; font-weight: 600;">
                            Rekap Absen (CSV)
                        </a>
                        <button type="button"
                           id="btn-open-print-modal"
                           class="btn-primary"
                           style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none; font-weight: 600; background: #6366f1; border-color: #6366f1; cursor: pointer; border: none; border-radius: 6px; color: #fff;">
                            Cetak Formulir Absensi (Landscape)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. Visual Kalender Card -->
    <div class="content-card" id="attendance-calendar-container" style="margin-bottom: 30px; padding: 24px;">
        <input type="hidden" id="poll-user-code" value="{{ $intern->user_code }}">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px;">
            <h3 style="margin: 0; font-size: 1.1rem; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                Visual Kalender Kehadiran: {{ $selectedDate->translatedFormat('F Y') }}
            </h3>
            <form action="{{ route('admin.interns.show', $intern->user_code) }}" method="GET" style="margin: 0; display: flex; gap: 10px;">
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
                        $isToday = $loopDateStr === $todayDate;

                        $scheduleForDate = \App\Models\WorkSchedule::getScheduleForDate($loopDate);
                        $isHoliday = $scheduleForDate ? $scheduleForDate->is_holiday : false;
                        $holidayName = $scheduleForDate ? ($scheduleForDate->keterangan ?? 'Hari Libur') : null;

                        $attendance = $calendarAttendances->get($loopDateStr);
                        $logbooksForDay = isset($calendarLogbooks[$loopDateStr]) ? $calendarLogbooks[$loopDateStr] : collect();
                        $hasSelfie = $attendance && ($attendance->foto_masuk || $attendance->foto_pulang);
                        $isClickable = $attendance || $logbooksForDay->isNotEmpty();
                    @endphp

                    <div class="calendar-day {{ $isToday ? 'today' : '' }} {{ $isClickable ? 'has-selfie-photo' : '' }}"
                         style="{{ $isHoliday ? 'background: rgba(156, 163, 175, 0.02);' : '' }} {{ $isClickable ? 'cursor: pointer;' : '' }}"
                         @if($isClickable)
                            onclick="openActivityDetail('{{ $loopDateStr }}', '{{ $loopDate->translatedFormat('d F Y') }}', '{{ $attendance ? $attendance->status : ($isHoliday ? 'Libur' : 'Tanpa Keterangan') }}', '{{ $attendance && $attendance->jam_masuk ? \Carbon\Carbon::parse($attendance->jam_masuk)->format('H:i:s') : '-' }}', '{{ $attendance && $attendance->jam_pulang ? \Carbon\Carbon::parse($attendance->jam_pulang)->format('H:i:s') : '-' }}', '{{ $attendance && $attendance->foto_masuk ? asset($attendance->foto_masuk) : '' }}', '{{ $attendance && $attendance->foto_pulang ? asset($attendance->foto_pulang) : '' }}', {{ json_encode($logbooksForDay->values()->toArray()) }}, true)"
                         @endif>
                        <div class="day-number">{{ $day }}</div>

                        <div class="day-status">
                            @if($attendance)
                                @php
                                    $badgeClass = '';
                                    $badgeStyle = '';
                                    if ($attendance->status === 'Hadir') {
                                        $badgeClass = 'badge-success';
                                    } elseif ($attendance->status === 'Terlambat') {
                                        $badgeClass = 'badge-warning';
                                    } elseif ($attendance->status === 'Pulang Cepat / Izin') {
                                        $badgeClass = 'badge-info';
                                    } elseif (in_array($attendance->status, ['Izin', 'Sakit'])) {
                                        $badgeClass = 'badge-info';
                                    } elseif (in_array($attendance->status, ['Lupa Absen Masuk', 'Lupa Absen Pulang'])) {
                                        $badgeStyle = 'background: rgba(245, 158, 11, 0.15); border: 1px solid #f59e0b; color: #fbbf24;';
                                    } elseif ($attendance->status === 'Terlambat dan Izin') {
                                        $badgeStyle = 'background: rgba(245, 158, 11, 0.15); border: 1px solid #f59e0b; color: #fbbf24;';
                                    } elseif ($attendance->status === 'Lupa Absen Masuk dan Pulang') {
                                        $badgeStyle = 'background: rgba(139, 92, 246, 0.15); border: 1px solid #8b5cf6; color: #a78bfa;';
                                    } elseif ($attendance->status === 'Lupa Absen Masuk dan Izin') {
                                        $badgeStyle = 'background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171;';
                                    } else {
                                        $badgeClass = 'badge-danger';
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }}" style="{{ $badgeStyle }}">
                                    {{ $attendance->status === 'Tanpa Keterangan' ? 'Alfa' : $attendance->status }}
                                    @if($attendance->jam_masuk)
                                        <div style="font-size: 0.65rem; font-weight: normal; margin-top: 2px;">
                                            {{ \Carbon\Carbon::parse($attendance->jam_masuk)->format('H:i') }}
                                        </div>
                                    @endif
                                </span>

                                <div style="display: flex; justify-content: center; gap: 6px; margin-top: 6px; align-items: center;">
                                    @if($hasSelfie)
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" title="Memiliki foto selfie"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                    @endif
                                    @if($logbooksForDay->isNotEmpty())
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" title="Memiliki logbook"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                    @endif
                                </div>
                            @elseif($isHoliday)
                                <span class="badge" style="background: rgba(156, 163, 175, 0.1); border: 1px solid #9ca3af; color: #9ca3af;" title="{{ $holidayName }}">
                                    Libur
                                </span>
                            @elseif($loopDate->lessThan(now()->startOfDay()))
                                @if($logbooksForDay->isNotEmpty())
                                    {{-- Mock condition handled by controller, but just in case --}}
                                @endif
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
                    <div style="width: 12px; height: 12px; border-radius: 3px; background: #f59e0b;"></div>
                    <span>Lupa Absen Masuk / Pulang</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--text-secondary);">
                    <div style="width: 12px; height: 12px; border-radius: 3px; background: #8b5cf6;"></div>
                    <span>Lupa Absen Masuk & Pulang (Ada Logbook)</span>
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

    {{-- ===== Modal: Detail Aktivitas Intern (Pembimbing & Peserta View) ===== --}}
    <div class="form-modal-backdrop" id="activity-detail-modal">
        <div class="form-modal" style="width: min(100%, 640px); background: rgba(30, 30, 50, 0.95); border: 1px solid var(--glass-border); backdrop-filter: blur(20px); border-radius: 20px; color: #fff; padding: 24px; display: flex; flex-direction: column; max-height: calc(100vh - 80px); overflow: hidden;">
            <div class="form-modal-header" style="border-bottom: 1px solid var(--glass-border); padding-bottom: 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 600; color: #fff;" id="modal-activity-title">Detail Aktivitas Intern</h3>
                <button type="button" class="modal-close" onclick="closeActivityDetailModal()" style="font-size: 1.5rem; background: none; border: none; color: var(--text-secondary); cursor: pointer;">&times;</button>
            </div>
            <div class="form-modal-body" style="overflow-y: auto; padding-right: 8px; flex-grow: 1;">
                <!-- Attendance Section -->
                <div style="margin-bottom: 24px; background: rgba(255,255,255,0.02); padding: 16px; border-radius: 12px; border: 1px solid var(--glass-border);">
                    <h4 style="margin-top: 0; margin-bottom: 12px; font-size: 1rem; color: var(--accent-primary); border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 6px;">Status Absensi & Selfie</h4>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <span style="font-size: 0.85rem; color: var(--text-secondary);">Status Akhir:</span>
                            <div style="margin-top: 4px;"><strong id="modal-attendance-status" style="font-size: 1.05rem;">-</strong></div>
                        </div>
                        <div>
                            <span style="font-size: 0.85rem; color: var(--text-secondary);">Jam Masuk:</span>
                            <div style="margin-top: 4px;" id="modal-attendance-masuk-time">-</div>
                        </div>
                        <div>
                            <span style="font-size: 0.85rem; color: var(--text-secondary);">Jam Pulang:</span>
                            <div style="margin-top: 4px;" id="modal-attendance-pulang-time">-</div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; justify-content: center; margin-top: 16px; flex-wrap: wrap;">
                        <div id="modal-selfie-masuk-wrap" style="text-align: center; flex: 1; min-width: 140px;">
                            <div style="font-size: 0.8rem; font-weight: 600; color: #34d399; margin-bottom: 6px;">Foto Masuk</div>
                            <img id="modal-selfie-masuk-img" src="" alt="Selfie Masuk" style="width: 100%; max-height: 180px; object-fit: cover; border-radius: 8px; border: 1px solid var(--glass-border);">
                            <div id="modal-selfie-masuk-placeholder" style="width: 100%; height: 180px; border-radius: 8px; border: 1px dashed var(--glass-border); background: rgba(255,255,255,0.02); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); font-size: 0.8rem;">Belum Absen Masuk</div>
                        </div>
                        <div id="modal-selfie-pulang-wrap" style="text-align: center; flex: 1; min-width: 140px;">
                            <div style="font-size: 0.8rem; font-weight: 600; color: #fbbf24; margin-bottom: 6px;">Foto Pulang</div>
                            <img id="modal-selfie-pulang-img" src="" alt="Selfie Pulang" style="width: 100%; max-height: 180px; object-fit: cover; border-radius: 8px; border: 1px solid var(--glass-border);">
                            <div id="modal-selfie-pulang-placeholder" style="width: 100%; height: 180px; border-radius: 8px; border: 1px dashed var(--glass-border); background: rgba(255,255,255,0.02); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); font-size: 0.8rem;">Belum Absen Pulang</div>
                        </div>
                    </div>
                </div>

                <!-- Logbook Section -->
                <div id="modal-logbook-section">
                    <h4 style="margin-top: 0; margin-bottom: 12px; font-size: 1rem; color: var(--accent-primary); border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 6px;">Laporan Logbook Kegiatan</h4>
                    <div id="modal-logbook-list">
                        <!-- Will be dynamically populated via JS -->
                    </div>
                </div>
            </div>
            <div class="form-modal-footer" style="border-top: 1px solid var(--glass-border); padding-top: 16px; margin-top: 20px; display: flex; justify-content: flex-end; flex-shrink: 0;">
                <button type="button" class="btn-secondary" onclick="closeActivityDetailModal()" style="padding: 10px 20px; border-radius: 8px; cursor: pointer;">Tutup</button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Define functions immediately in global scope
            window.showImageModal = function(src, title) {
                const selfieModal = document.getElementById('selfie-modal');
                const modalSelfieImg = document.getElementById('modal-selfie-img');
                const modalSelfieTitle = document.getElementById('modal-selfie-title');
                if (!selfieModal || !modalSelfieImg || !modalSelfieTitle) return;

                modalSelfieImg.src = src;
                modalSelfieTitle.textContent = title;
                selfieModal.classList.add('is-open');
            };

            window.showSelfiePopup = function(fotoMasuk, fotoPulang, formattedDate) {
                const photosModal = document.getElementById('daily-photos-modal');
                const imgMasuk = document.getElementById('modal-foto-masuk');
                const imgPulang = document.getElementById('modal-foto-pulang');
                const containerMasuk = document.getElementById('modal-masuk-container');
                const containerPulang = document.getElementById('modal-pulang-container');
                const modalPhotosTitle = document.getElementById('modal-photos-title');

                if (!photosModal || !modalPhotosTitle || !imgMasuk || !imgPulang) return;

                modalPhotosTitle.textContent = `Foto Absensi - ${formattedDate}`;

                if (fotoMasuk) {
                    imgMasuk.src = fotoMasuk;
                    if (containerMasuk) containerMasuk.style.display = 'flex';
                } else {
                    imgMasuk.src = '';
                    if (containerMasuk) containerMasuk.style.display = 'none';
                }

                if (fotoPulang) {
                    imgPulang.src = fotoPulang;
                    if (containerPulang) containerPulang.style.display = 'flex';
                } else {
                    imgPulang.src = '';
                    if (containerPulang) containerPulang.style.display = 'none';
                }

                photosModal.classList.add('is-open');
            };

            window.openActivityDetail = function(dateStr, formattedDate, attendanceStatus, jamMasuk, jamPulang, fotoMasuk, fotoPulang, logbooks, isSupervisor) {
                const activityModal = document.getElementById('activity-detail-modal');
                if (!activityModal) return;

                document.getElementById('modal-activity-title').textContent = 'Detail Aktivitas - ' + formattedDate;

                const statusEl = document.getElementById('modal-attendance-status');
                statusEl.textContent = attendanceStatus;

                statusEl.className = '';
                if (attendanceStatus === 'Hadir') {
                    statusEl.style.color = '#34d399';
                } else if (attendanceStatus === 'Terlambat') {
                    statusEl.style.color = '#fbbf24';
                } else if (['Izin', 'Sakit'].includes(attendanceStatus)) {
                    statusEl.style.color = '#60a5fa';
                } else {
                    statusEl.style.color = '#f87171';
                }

                document.getElementById('modal-attendance-masuk-time').textContent = jamMasuk;
                document.getElementById('modal-attendance-pulang-time').textContent = jamPulang;

                const imgM = document.getElementById('modal-selfie-masuk-img');
                const placeholderM = document.getElementById('modal-selfie-masuk-placeholder');
                if (fotoMasuk) {
                    imgM.src = fotoMasuk;
                    imgM.style.display = 'block';
                    if (placeholderM) placeholderM.style.display = 'none';
                } else {
                    imgM.style.display = 'none';
                    if (placeholderM) placeholderM.style.display = 'flex';
                }

                const imgP = document.getElementById('modal-selfie-pulang-img');
                const placeholderP = document.getElementById('modal-selfie-pulang-placeholder');
                if (fotoPulang) {
                    imgP.src = fotoPulang;
                    imgP.style.display = 'block';
                    if (placeholderP) placeholderP.style.display = 'none';
                } else {
                    imgP.style.display = 'none';
                    if (placeholderP) placeholderP.style.display = 'flex';
                }

                const logbookList = document.getElementById('modal-logbook-list');
                logbookList.innerHTML = '';

                if (logbooks && logbooks.length > 0) {
                    logbooks.forEach(lb => {
                        const lbCard = document.createElement('div');
                        lbCard.style.background = 'rgba(255,255,255,0.02)';
                        lbCard.style.border = '1px solid var(--glass-border)';
                        lbCard.style.borderRadius = '8px';
                        lbCard.style.padding = '12px';
                        lbCard.style.marginBottom = '12px';

                        let badgeClass = 'badge-warning';
                        let badgeText = 'Pending';
                        let badgeStyle = '';
                        if (lb.status_approval === 'Approved') {
                            badgeClass = 'badge-success';
                            badgeText = 'Disetujui';
                        } else if (lb.status_approval === 'Rejected') {
                            badgeClass = 'badge-danger';
                            badgeText = 'Ditolak';
                        } else if (lb.status_approval === 'Revisi') {
                            badgeClass = 'badge-warning-custom';
                            badgeText = 'Revisi';
                            badgeStyle = 'background-color: #fbbf24 !important; border-color: #fbbf24 !important; color: #1e1b4b !important;';
                        } else if (lb.status_approval === 'Draft') {
                            badgeClass = 'draft-badge';
                            badgeText = 'Draft';
                        }

                        let tagsHtml = '';
                        if (lb.tags) {
                            const tagsList = lb.tags.split(',');
                            tagsList.forEach(t => {
                                tagsHtml += `<span class="badge" style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border); font-size: 0.75rem; margin-right: 4px;">${t.trim()}</span>`;
                            });
                        }

                        let actionButtonsHtml = '';
                        if (isSupervisor) {
                            if (lb.status_approval === 'Revisi') {
                                actionButtonsHtml = '';
                            } else {
                                let approveBtnHtml = lb.status_approval === 'Approved' ? '' : `<button type="button" class="btn-camera btn-camera-success" onclick="handleLogbookAction(${lb.id}, 'approve')" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 6px; background: #10b981; border: none; color: #fff; cursor: pointer;">Setujui</button>`;
                                let rejectBtnHtml = lb.status_approval === 'Rejected' ? '' : `<button type="button" class="btn-camera btn-camera-danger" onclick="handleLogbookAction(${lb.id}, 'reject')" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 6px; cursor: pointer;">Tolak</button>`;
                                let revisionBtnHtml = `<button type="button" class="btn-camera" onclick="handleLogbookAction(${lb.id}, 'revision')" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 6px; background: #fbbf24; border: none; color: #1e1b4b; font-weight: 600; cursor: pointer;">Minta Revisi</button>`;

                                actionButtonsHtml = `
                                    <div style="display: flex; gap: 8px; margin-top: 12px; justify-content: flex-end; flex-wrap: wrap;">
                                        ${rejectBtnHtml}
                                        ${revisionBtnHtml}
                                        ${approveBtnHtml}
                                    </div>
                                `;
                            }
                        }

                        let catatanHtml = '';
                        if (lb.catatan_pembimbing) {
                            catatanHtml = `
                                <div style="margin-top: 8px; font-size: 0.8rem; font-style: italic; color: #fbbf24; border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 6px;">
                                    Catatan Pembimbing: ${lb.catatan_pembimbing}
                                </div>
                            `;
                        }

                        lbCard.innerHTML = `
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                <strong style="color: var(--accent-primary); font-size: 0.95rem;">${lb.kegiatan}</strong>
                                <span class="badge ${badgeClass}" style="${badgeStyle}">${badgeText}</span>
                            </div>
                            <p style="margin: 0 0 8px 0; font-size: 0.85rem; line-height: 1.5; color: var(--text-secondary); white-space: pre-wrap;">${lb.deskripsi}</p>
                            <div style="margin-bottom: 8px;">${tagsHtml}</div>
                            ${catatanHtml}
                            ${actionButtonsHtml}
                        `;
                        logbookList.appendChild(lbCard);
                    });
                    document.getElementById('modal-logbook-section').style.display = 'block';
                } else {
                    document.getElementById('modal-logbook-section').style.display = 'none';
                }

                activityModal.classList.add('is-open');
            };

            window.closeActivityDetailModal = function() {
                const activityModal = document.getElementById('activity-detail-modal');
                activityModal?.classList.remove('is-open');
            };

            window.handleLogbookAction = async function (logbookId, action) {
                if (!window.Swal) {
                    alert('SweetAlert2 tidak aktif.');
                    return;
                }

                const isRevision = action === 'revision';
                const actionText = action === 'approve' ? 'menyetujui' : (isRevision ? 'meminta revisi' : 'menolak');
                const confirmButtonColor = action === 'approve' ? '#10b981' : (isRevision ? '#fbbf24' : '#ef4444');
                const confirmButtonText = action === 'approve' ? 'Setujui' : (isRevision ? 'Minta Revisi' : 'Tolak');
                const titleText = action === 'approve' ? 'Konfirmasi Persetujuan' : (isRevision ? 'Konfirmasi Permintaan Revisi' : 'Konfirmasi Penolakan');
                const inputPlaceholder = isRevision ? 'Tulis catatan instruksi revisi (wajib)...' : 'Tulis catatan pembimbing di sini...';

                const { value: catatan } = await window.Swal.fire({
                    title: titleText,
                    text: `Apakah Anda yakin ingin ${actionText} logbook ini?`,
                    input: 'textarea',
                    inputPlaceholder: inputPlaceholder,
                    showCancelButton: true,
                    confirmButtonText: confirmButtonText,
                    cancelButtonText: 'Batal',
                    confirmButtonColor: confirmButtonColor,
                    inputValidator: (value) => {
                        if (action !== 'approve' && !value.trim()) {
                            return 'Catatan tidak boleh kosong!';
                        }
                    },
                    inputAttributes: {
                        'maxlength': 255
                    }
                });

                if (catatan !== undefined) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/logbook/${logbookId}/${action}`;

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    form.appendChild(csrfInput);

                    const catatanInput = document.createElement('input');
                    catatanInput.type = 'hidden';
                    catatanInput.name = 'catatan_pembimbing';
                    catatanInput.value = catatan;
                    form.appendChild(catatanInput);

                    document.body.appendChild(form);

                    window.Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang menyimpan status verifikasi logbook.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            window.Swal.showLoading();
                        }
                    });

                    form.submit();
                }
            };

            document.addEventListener('DOMContentLoaded', () => {
                const selfieModal = document.getElementById('selfie-modal');
                const closeSelfieModal = document.getElementById('close-selfie-modal');
                closeSelfieModal?.addEventListener('click', () => {
                    selfieModal?.classList.remove('is-open');
                });
                selfieModal?.addEventListener('click', (e) => {
                    if (e.target === selfieModal) {
                        selfieModal.classList.remove('is-open');
                    }
                });

                const photosModal = document.getElementById('daily-photos-modal');
                const closePhotosModal = document.getElementById('close-photos-modal');
                closePhotosModal?.addEventListener('click', () => {
                    photosModal?.classList.remove('is-open');
                });
                photosModal?.addEventListener('click', (e) => {
                    if (e.target === photosModal) {
                        photosModal.classList.remove('is-open');
                    }
                });

                const activityModal = document.getElementById('activity-detail-modal');
                activityModal?.addEventListener('click', (e) => {
                    if (e.target === activityModal) {
                        activityModal.classList.remove('is-open');
                    }
                });

                // Print landscape modal logic
                const printModal = document.getElementById('modal-print-landscape');
                const openPrintBtn = document.getElementById('btn-open-print-modal');
                const closePrintBtn = document.getElementById('close-print-modal');
                const cancelPrintBtn = document.getElementById('btn-cancel-print');

                if (openPrintBtn && printModal) {
                    openPrintBtn.addEventListener('click', () => {
                        const selectedMonth = @json($month);
                        const selectedYear = @json($year);

                        const pad = (num) => String(num).padStart(2, '0');
                        const startDateStr = `${selectedYear}-${pad(selectedMonth)}-01`;
                        const lastDay = new Date(selectedYear, selectedMonth, 0).getDate();
                        const endDateStr = `${selectedYear}-${pad(selectedMonth)}-${pad(lastDay)}`;

                        const startDateInput = document.getElementById('print_start_date');
                        const endDateInput = document.getElementById('print_end_date');

                        if (startDateInput) startDateInput.value = startDateStr;
                        if (endDateInput) endDateInput.value = endDateStr;

                        printModal.style.display = 'flex';
                        printModal.classList.add('is-open');
                    });
                }

                // Dynamic Signatures Generator
                const sigContainer = document.getElementById('signature-list-container');
                const addSigBtn = document.getElementById('btn-add-signature');

                if (sigContainer && addSigBtn) {
                    const defaultSignatures = JSON.parse(sigContainer.dataset.defaultSignatures || '[]');

                    const reindexSignatures = () => {
                        Array.from(sigContainer.children).forEach((card, index) => {
                            const titleLabel = card.querySelector('.sig-index-label');
                            if (titleLabel) titleLabel.textContent = `Penandatangan #${index + 1}`;

                            const fields = card.querySelectorAll('input, select');
                            fields.forEach(field => {
                                const match = field.name.match(/signatures\[\d+\]\[(\w+)\]/);
                                if (match) {
                                    const name = match[1];
                                    field.name = `signatures[${index}][${name}]`;
                                    field.id = `signatures_${index}_${name}`;
                                }
                            });
                        });
                    };

                    const addSignatureRow = (data = {}) => {
                        const index = sigContainer.children.length;
                        const card = document.createElement('div');
                        card.className = 'signature-card';
                        card.style.cssText = 'background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); padding: 12px; border-radius: 8px; display: flex; flex-direction: column; gap: 10px; position: relative; margin-top: 10px; text-align: left;';

                        const header = document.createElement('div');
                        header.style.cssText = 'display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 4px;';

                        const label = document.createElement('span');
                        label.className = 'sig-index-label';
                        label.textContent = `Penandatangan #${index + 1}`;
                        label.style.cssText = 'font-size: 0.75rem; font-weight: 700; color: #a5b4fc; text-transform: uppercase;';
                        header.appendChild(label);

                        const deleteBtn = document.createElement('button');
                        deleteBtn.type = 'button';
                        deleteBtn.textContent = 'Hapus';
                        deleteBtn.style.cssText = 'padding: 3px 6px; font-size: 0.7rem; border-radius: 4px; background: #ef4444; border: none; color: #fff; cursor: pointer; font-weight: 600; font-family: inherit;';
                        deleteBtn.addEventListener('click', () => {
                            card.remove();
                            reindexSignatures();
                        });
                        header.appendChild(deleteBtn);
                        card.appendChild(header);

                        const grid = document.createElement('div');
                        grid.className = 'sig-grid';

                        const createInput = (lblText, name, val, isFile = false) => {
                            const group = document.createElement('div');
                            group.style.cssText = 'display: flex; flex-direction: column; gap: 4px;';

                            const lbl = document.createElement('label');
                            lbl.textContent = lblText;
                            lbl.style.cssText = 'font-size: 0.7rem; color: #9ca3af; font-weight: 600; text-transform: uppercase;';
                            group.appendChild(lbl);

                            const input = document.createElement('input');
                            input.type = isFile ? 'file' : 'text';
                            if (isFile) {
                                input.accept = 'image/png, image/jpeg, image/jpg, image/webp';
                                input.style.cssText = 'width: 100%; padding: 4px; border-radius: 6px; border: 1px dashed rgba(255,255,255,0.15); background: rgba(0,0,0,0.2); color: #fff; font-size: 0.75rem; font-family: inherit;';
                            } else {
                                input.value = val;
                                input.style.cssText = 'width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); color: #fff; font-size: 0.75rem; outline: none; font-family: inherit;';
                            }
                            input.name = `signatures[${index}][${name}]`;
                            input.id = `signatures_${index}_${name}`;
                            group.appendChild(input);
                            return group;
                        };

                        const createSelect = (lblText, name, val, optionsList) => {
                            const group = document.createElement('div');
                            group.style.cssText = 'display: flex; flex-direction: column; gap: 4px;';

                            const lbl = document.createElement('label');
                            lbl.textContent = lblText;
                            lbl.style.cssText = 'font-size: 0.7rem; color: #9ca3af; font-weight: 600; text-transform: uppercase;';
                            group.appendChild(lbl);

                            const wrapper = document.createElement('div');
                            wrapper.className = 'custom-select-wrapper';
                            wrapper.style.cssText = 'position: relative; width: 100%;';

                            const trigger = document.createElement('button');
                            trigger.type = 'button';
                            trigger.className = 'custom-select-trigger';
                            trigger.style.cssText = 'width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--glass-border); background: #1e293b; color: #fff; font-size: 0.75rem; text-align: left; cursor: pointer;';

                            const initialOpt = optionsList.find(opt => String(opt.value) === String(val)) || optionsList[0];
                            trigger.textContent = initialOpt ? initialOpt.text : 'Pilih...';
                            wrapper.appendChild(trigger);

                            const optionsContainer = document.createElement('div');
                            optionsContainer.className = 'custom-select-options';
                            optionsContainer.style.cssText = 'position: absolute; top: 100%; left: 0; width: 100%; background: #1e293b; border: 1px solid var(--glass-border); border-radius: 6px; z-index: 10; display: none; max-height: 150px; overflow-y: auto;';

                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = `signatures[${index}][${name}]`;
                            hiddenInput.id = `signatures_${index}_${name}`;
                            hiddenInput.value = initialOpt ? initialOpt.value : '';
                            wrapper.appendChild(hiddenInput);

                            optionsList.forEach(opt => {
                                const optDiv = document.createElement('div');
                                optDiv.className = 'custom-select-option';
                                optDiv.style.cssText = 'padding: 6px 10px; cursor: pointer; font-size: 0.75rem;';
                                if (String(opt.value) === String(val)) {
                                    optDiv.style.background = '#374151';
                                }
                                optDiv.textContent = opt.text;
                                optDiv.dataset.value = opt.value;

                                optDiv.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                    optionsContainer.querySelectorAll('.custom-select-option').forEach(el => el.style.background = 'transparent');
                                    optDiv.style.background = '#374151';

                                    trigger.textContent = opt.text;
                                    hiddenInput.value = opt.value;
                                    optionsContainer.style.display = 'none';
                                });

                                optionsContainer.appendChild(optDiv);
                            });

                            wrapper.appendChild(optionsContainer);

                            trigger.addEventListener('click', (e) => {
                                e.stopPropagation();
                                document.querySelectorAll('.custom-select-options').forEach(o => { if(o !== optionsContainer) o.style.display = 'none'; });
                                optionsContainer.style.display = optionsContainer.style.display === 'block' ? 'none' : 'block';
                            });

                            group.appendChild(wrapper);
                            return group;
                        };

                        grid.appendChild(createSelect('Baris Tanda Tangan (Posisi)', 'row', data.row || 1, [
                            { value: 1, text: 'Baris 1 (Paling Atas)' },
                            { value: 2, text: 'Baris 2' },
                            { value: 3, text: 'Baris 3' },
                            { value: 4, text: 'Baris 4' },
                            { value: 5, text: 'Baris 5' },
                            { value: 6, text: 'Baris 6' },
                            { value: 7, text: 'Baris 7' },
                            { value: 8, text: 'Baris 8' },
                            { value: 9, text: 'Baris 9' },
                            { value: 10, text: 'Baris 10 (Paling Bawah)' }
                        ]));
                        grid.appendChild(createInput('Sebutan / Jabatan (e.g. Diperiksa Oleh)', 'title', data.title || ''));
                        grid.appendChild(createInput('Nama Lengkap', 'nama', data.nama || ''));
                        grid.appendChild(createInput('NIP (Opsional)', 'nip', data.nip || ''));
                        grid.appendChild(createInput('Instansi / Kantor (Opsional)', 'instansi', data.instansi || ''));
                        grid.appendChild(createInput('Divisi / Bidang (Opsional)', 'divisi', data.divisi || ''));
                        grid.appendChild(createInput('Tanda Tangan (Gambar PNG/JPG)', 'ttd', '', true));

                        card.appendChild(grid);
                        sigContainer.appendChild(card);
                    };

                    // Populating defaults
                    if (defaultSignatures.length > 0) {
                        defaultSignatures.forEach(sig => addSignatureRow(sig));
                    } else {
                        addSignatureRow();
                    }

                    addSigBtn.addEventListener('click', () => {
                        addSignatureRow();
                    });

                    document.addEventListener('click', () => {
                        document.querySelectorAll('.custom-select-options').forEach(o => o.style.display = 'none');
                    });
                }

                const hidePrintModal = () => {
                    if (printModal) {
                        printModal.style.display = 'none';
                        printModal.classList.remove('is-open');
                    }
                };

                closePrintBtn?.addEventListener('click', hidePrintModal);
                cancelPrintBtn?.addEventListener('click', hidePrintModal);

                printModal?.addEventListener('click', (e) => {
                    if (e.target === printModal) {
                        hidePrintModal();
                    }
                });

                // Sync color picker label text
                const bgPicker = document.getElementById('laporan_header_bg');
                if (bgPicker) {
                    bgPicker.addEventListener('input', (e) => {
                        const label = bgPicker.nextElementSibling;
                        if (label) label.textContent = `${e.target.value.toUpperCase()} ${e.target.value.toUpperCase() === '#0C2340' ? '(Default)' : ''}`;
                    });
                }
                const textPicker = document.getElementById('laporan_header_text');
                if (textPicker) {
                    textPicker.addEventListener('input', (e) => {
                        const label = textPicker.nextElementSibling;
                        if (label) label.textContent = `${e.target.value.toUpperCase()} ${e.target.value.toUpperCase() === '#FFFFFF' ? '(Default)' : ''}`;
                    });
                }
            });
        </script>

        <!-- Modal Cetak Laporan Landscape -->
        <div class="form-modal-backdrop" id="modal-print-landscape" aria-hidden="true" style="z-index: 1050; display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); align-items: center; justify-content: center;">
            <div class="form-modal" role="dialog" aria-modal="true" aria-labelledby="print-landscape-title" style="background: #1e293b; border: 1px solid var(--glass-border); border-radius: 12px; width: 95%; max-width: 800px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); color: #fff; text-align: left;">
                <div class="form-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px;">
                    <h3 id="print-landscape-title" style="margin: 0; font-size: 1.2rem; color: #fff; font-weight: 600;">Cetak Formulir Absensi Harian</h3>
                    <button type="button" class="modal-close" id="close-print-modal" style="background: none; border: none; font-size: 1.5rem; color: #9ca3af; cursor: pointer;">&times;</button>
                </div>

                <form action="{{ route('peserta.formulir-absensi') }}" method="POST" target="_blank" class="modal-form" id="print-landscape-form" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 16px;">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $intern->user_code }}">

                    <div class="print-modal-content">
                       <!-- Filter Tanggal & Subjudul Laporan -->
                        <div class="grid-2-col" style="background: rgba(255,255,255,0.02); padding: 15px; border-radius: 8px; border: 1px solid var(--glass-border);">
                            <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                                <label for="print_start_date" style="font-size: 0.8rem; color: #9ca3af; font-weight: 600;">TANGGAL MULAI</label>
                                <input type="date" id="print_start_date" name="start_date" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); color: #fff; outline: none;">
                            </div>
                            <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                                <label for="print_end_date" style="font-size: 0.8rem; color: #9ca3af; font-weight: 600;">TANGGAL SELESAI</label>
                                <input type="date" id="print_end_date" name="end_date" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); color: #fff; outline: none;">
                            </div>
                            <div class="form-group" style="grid-column: span 2; display: flex; flex-direction: column; gap: 6px; margin-top: 10px;">
                                <label for="laporan_title" style="font-size: 0.8rem; color: #9ca3af; font-weight: 600;">JUDUL UTAMA LAPORAN (KOP/HEADER)</label>
                                <input type="text" id="laporan_title" name="laporan_title" value="FORMULIR ABSENSI PERSONIL" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); color: #fff; outline: none;">
                            </div>
                            <div class="form-group" style="grid-column: span 2; display: flex; flex-direction: column; gap: 6px; margin-top: 5px;">
                                <label for="laporan_subtitle" style="font-size: 0.8rem; color: #9ca3af; font-weight: 600;">SUB-JUDUL LAPORAN (KOP/HEADER)</label>
                                <input type="text" id="laporan_subtitle" name="laporan_subtitle" value="KONSULTAN MANAJEMEN DATA DAN INFORMASI JALAN & JEMBATAN" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); color: #fff; outline: none;">
                            </div>
                            <div class="form-group" style="grid-column: span 2; display: flex; flex-direction: column; gap: 6px; margin-top: 5px;">
                                <label for="laporan_kop" style="font-size: 0.8rem; color: #9ca3af; font-weight: 600;">UNGGAH GAMBAR KOP SURAT (PNG/JPG, OPSIONAL)</label>
                                <input type="file" id="laporan_kop" name="laporan_kop" accept="image/png, image/jpeg, image/jpg, image/webp" style="width: 100%; padding: 6px; border-radius: 8px; border: 1px dashed var(--glass-border); background: rgba(0,0,0,0.2); color: #fff;">
                            </div>
                            <div class="form-group" style="grid-column: span 2; display: flex; flex-direction: column; gap: 6px; margin-top: 5px;">
                                <label for="laporan_foto" style="font-size: 0.8rem; color: #9ca3af; font-weight: 600;">TAMPILKAN FOTO DOKUMENTASI</label>
                                <select id="laporan_foto" name="laporan_foto" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: #1e293b; color: #fff; outline: none; font-family: inherit; cursor: pointer;">
                                    <option value="both" selected>Keduanya (Foto Masuk & Foto Pulang)</option>
                                    <option value="masuk">Hanya Foto Masuk</option>
                                    <option value="pulang">Hanya Foto Pulang</option>
                                    <option value="none">Tidak Ditampilkan (Tanpa Foto)</option>
                                </select>
                            </div>
                             <div class="grid-2-col color-picker-grid" style="grid-column: span 2; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 5px;">
                                 <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                                     <label for="laporan_header_bg" style="font-size: 0.8rem; color: #9ca3af; font-weight: 600;">WARNA BG HEADER</label>
                                     <div style="display: flex; align-items: center; gap: 10px;">
                                         <input type="color" id="laporan_header_bg" name="laporan_header_bg" value="#0c2340" style="border: none; padding: 0; width: 40px; height: 35px; border-radius: 6px; cursor: pointer; background: transparent;">
                                         <span style="font-size: 0.8rem; color: #fff;">#0C2340 (Default)</span>
                                     </div>
                                 </div>
                                 <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                                     <label for="laporan_header_text" style="font-size: 0.8rem; color: #9ca3af; font-weight: 600;">WARNA TEKS HEADER</label>
                                     <div style="display: flex; align-items: center; gap: 10px;">
                                         <input type="color" id="laporan_header_text" name="laporan_header_text" value="#ffffff" style="border: none; padding: 0; width: 40px; height: 35px; border-radius: 6px; cursor: pointer; background: transparent;">
                                         <span style="font-size: 0.8rem; color: #fff;">#FFFFFF (Default)</span>
                                     </div>
                                 </div>
                             </div>
                              <div class="form-group" style="grid-column: span 2; display: flex; flex-direction: column; gap: 6px; margin-top: 5px;">
                                  <label for="laporan_header_footer" style="font-size: 0.8rem; color: #9ca3af; font-weight: 600;">INFO BROWSER (HEADER/FOOTER URL & WAKTU)</label>
                                  <select id="laporan_header_footer" name="laporan_header_footer" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: #1e293b; color: #fff; outline: none; font-family: inherit; cursor: pointer;">
                                      <option value="hide" selected>Sembunyikan (Bersih & Rekomendasi)</option>
                                      <option value="show">Tampilkan (Bawaan Browser)</option>
                                  </select>
                              </div>
                              <div class="form-group" style="grid-column: span 2; display: flex; flex-direction: column; gap: 6px; margin-top: 5px;">
                                  <label for="laporan_keterangan" style="font-size: 0.8rem; color: #9ca3af; font-weight: 600;">KOLOM KETERANGAN (STATUS PRESENSI)</label>
                                  <select id="laporan_keterangan" name="laporan_keterangan" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: #1e293b; color: #fff; outline: none; font-family: inherit; cursor: pointer;">
                                      <option value="system" selected>Bawaan Sistem (Hadir, Izin, Sakit, Alpa, dll.)</option>
                                      <option value="wfo">WFO (Work From Office)</option>
                                      <option value="wfh">WFH (Work From Home)</option>
                                      <option value="wfa">WFA (Work From Anywhere)</option>
                                  </select>
                              </div>
                          </div>
                        </div>

                        <!-- Dynamic Signature Section -->
                        <div style="border-top: 1px solid var(--glass-border); padding-top: 15px; margin-top: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <span style="font-size: 0.85rem; font-weight: 700; color: #818cf8; text-transform: uppercase;">Daftar Penandatangan Laporan</span>
                                <button type="button" id="btn-add-signature" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 6px; background: #10b981; border: none; color: #fff; cursor: pointer; font-weight: 600; font-family: inherit;">+ Tambah Penandatangan</button>
                            </div>

                            <!-- Tip Format Teks -->
                            <div style="background: rgba(147, 197, 253, 0.06); border: 1px solid rgba(147, 197, 253, 0.18); padding: 10px 14px; border-radius: 8px; font-size: 0.76rem; line-height: 1.4; color: #93c5fd; margin-bottom: 12px; display: flex; gap: 8px; align-items: flex-start; text-align: left;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 1px;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                                <div>
                                    <strong style="color: #bfdbfe;">Tips Format Teks Penandatangan:</strong> Anda dapat menghias teks dengan tag HTML dasar:
                                    <span style="display: block; margin-top: 2px;">
                                        <code>&lt;b&gt;Tebal&lt;/b&gt;</code>,
                                        <code>&lt;i&gt;Miring&lt;/i&gt;</code>,
                                        <code>&lt;u&gt;Garis Bawah&lt;/u&gt;</code> (garis bawah akan menyambung termasuk spasi), dan
                                        <code>&lt;br&gt;</code> untuk pindah baris (Enter).
                                    </span>
                                </div>
                            </div>

                             <div id="signature-list-container"
                                  data-default-signatures="{{ json_encode([
                                      [
                                          'row' => 1,
                                          'title' => 'Diperiksa Oleh,',
                                          'nama' => $intern->pembimbing?->nama_lengkap ?? 'Yunus Susilo',
                                          'nip' => $intern->pembimbing?->nip ?? '',
                                          'instansi' => $intern->pembimbing?->instansi?->nama_instansi ?? 'Konsultan Manajemen Data dan Sistem Informasi',
                                          'divisi' => 'Jalan dan Jembatan'
                                      ],
                                      [
                                          'row' => 1,
                                          'title' => "Mengetahui,\nProject Officer",
                                          'nama' => 'Vito Borkat Harahap',
                                          'nip' => '198903042010121006',
                                          'instansi' => 'Pembinaan Data dan Sistem Informasi Jalan dan Jembatan',
                                          'divisi' => 'Satker Direktorat Bina Teknik Jalan dan Jembatan'
                                      ]
                                  ]) }}"
                                 style="display: flex; flex-direction: column; gap: 15px;">
                                <!-- Signature cards will be appended dynamically by JavaScript -->
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="export_format" id="export_format_landscape_admin" value="pdf">
                    <div class="print-modal-footer">
                        <button type="button" class="btn-secondary" id="btn-cancel-print" style="padding: 10px 18px; border-radius: 8px; cursor: pointer; border: 1px solid var(--glass-border); background: transparent; color: #fff;">Batal</button>
                        <button type="submit" onclick="document.getElementById('export_format_landscape_admin').value='pdf'" class="btn-primary" style="padding: 10px 18px; border-radius: 8px; cursor: pointer; background: #6366f1; border-color: #6366f1; color: #fff; font-weight: 600;">Cetak PDF</button>
                        <button type="submit" onclick="document.getElementById('export_format_landscape_admin').value='word'" class="btn-secondary" style="padding: 10px 18px; border-radius: 8px; cursor: pointer; background: #10b981; border: 1px solid #10b981; color: #fff; font-weight: 600;">Export Word</button>
                    </div>
                </form>
            </div>
        </div>
    @endpush
@endsection
