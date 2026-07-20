<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceHistoryController extends Controller
{
    /**
     * Show attendance history calendar for the logged-in intern.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Parse selected month & year, default to current
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        
        $selectedDate = Carbon::create($year, $month, 1);
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();
        
        // Fetch all attendance records for the selected month
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->tanggal)->toDateString();
            });
            
        // Fetch all logbooks for the selected month
        $calendarLogbooks = \App\Models\Logbook::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->tanggal)->toDateString();
            });

        // Fetch work schedule overrides for the selected month (to display holidays/custom workdays)
        $schedules = WorkSchedule::where('type', 'date')
            ->whereBetween('specific_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->specific_date)->toDateString();
            });

        // Dynamically mock Lupa Absen Masuk dan Pulang if logbook is present but no attendance on workdays
        $tempAttendances = collect();
        $daysInMonth = $selectedDate->daysInMonth;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $loopDateStr = Carbon::create($year, $month, $day)->toDateString();
            if ($attendances->has($loopDateStr)) {
                $tempAttendances->put($loopDateStr, $attendances->get($loopDateStr));
            } elseif (isset($calendarLogbooks[$loopDateStr]) && $calendarLogbooks[$loopDateStr]->isNotEmpty()) {
                $loopDateObj = Carbon::parse($loopDateStr);
                $schedule = WorkSchedule::getScheduleForDate($loopDateObj);
                $isHoliday = $schedule ? $schedule->is_holiday : false;
                
                if (!$isHoliday && Carbon::parse($loopDateStr)->lessThan(Carbon::today())) {
                    $mock = new Attendance([
                        'user_id' => $user->id,
                        'tanggal' => $loopDateStr,
                        'jam_masuk' => null,
                        'jam_pulang' => null,
                        'status' => 'Lupa Absen Masuk dan Pulang'
                    ]);
                    $tempAttendances->put($loopDateStr, $mock);
                }
            }
        }
        $attendances = $tempAttendances;

        // Calculate statistics
        $stats = [
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'absen' => 0,
        ];

        foreach ($attendances as $att) {
            if ($att->status === 'Hadir') {
                $stats['hadir']++;
            } elseif ($att->status === 'Terlambat') {
                $stats['terlambat']++;
            } elseif (in_array($att->status, ['Izin', 'Sakit', 'Terlambat dan Izin', 'Pulang Cepat / Izin'])) {
                $stats['izin']++;
            } elseif (in_array($att->status, ['Tanpa Keterangan', 'Lupa Absen Masuk', 'Lupa Absen Pulang', 'Lupa Absen Masuk dan Pulang', 'Lupa Absen Masuk dan Izin'])) {
                $stats['absen']++;
            }
        }

        // Calculate overall attendance rate
        $totalHadir = Attendance::where('user_id', $user->id)
            ->where('status', 'Hadir')
            ->count();
            
        $totalTerlambat = Attendance::where('user_id', $user->id)
            ->where('status', 'Terlambat')
            ->count();
            
        $totalAttendanceCount = Attendance::where('user_id', $user->id)->count();
        $attendanceRate = $totalAttendanceCount > 0 
            ? round((($totalHadir + $totalTerlambat) / $totalAttendanceCount) * 100) 
            : 0;

        return view('dashboard.peserta.attendance_history', compact(
            'attendances',
            'schedules',
            'stats',
            'selectedDate',
            'month',
            'year',
            'attendanceRate',
            'calendarLogbooks'
        ));
    }

    /**
     * Export monthly report bundle (Attendance + Logbooks) to a printable page.
     */
    public function exportMonthlyReport(Request $request)
    {
        $user = Auth::user();
        if (($user->isAdmin() || $user->isSuperAdmin()) && $request->has('user_id')) {
            $targetUser = \App\Models\User::where('user_code', $request->input('user_id'))
                ->orWhere('id', $request->input('user_id'))
                ->firstOrFail();
            if ($user->isAdmin() && (int) $targetUser->pembimbing_id !== (int) $user->id) {
                abort(403, 'Anda tidak memiliki akses ke laporan peserta magang ini.');
            }
            $user = $targetUser;
        }
        
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        
        $selectedDate = Carbon::create($year, $month, 1);
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();
        
        // Load User relationships
        $user->load(['instansi', 'pembimbing']);

        // Fetch attendance records for the selected month
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->tanggal)->toDateString();
            });

        // Fetch all logbooks for the selected month
        $calendarLogbooks = \App\Models\Logbook::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->tanggal)->toDateString();
            });

        // Fetch work schedule overrides
        $schedules = WorkSchedule::where('type', 'date')
            ->whereBetween('specific_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->specific_date)->toDateString();
            });

        // Dynamically mock Lupa Absen Masuk dan Pulang if logbook is present but no attendance on workdays
        $tempAttendances = collect();
        $daysInMonth = $selectedDate->daysInMonth;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $loopDateStr = Carbon::create($year, $month, $day)->toDateString();
            if ($attendances->has($loopDateStr)) {
                $tempAttendances->put($loopDateStr, $attendances->get($loopDateStr));
            } elseif (isset($calendarLogbooks[$loopDateStr]) && $calendarLogbooks[$loopDateStr]->isNotEmpty()) {
                $loopDateObj = Carbon::parse($loopDateStr);
                $schedule = WorkSchedule::getScheduleForDate($loopDateObj);
                $isHoliday = $schedule ? $schedule->is_holiday : false;
                
                if (!$isHoliday && Carbon::parse($loopDateStr)->lessThan(Carbon::today())) {
                    $mock = new Attendance([
                        'user_id' => $user->id,
                        'tanggal' => $loopDateStr,
                        'jam_masuk' => null,
                        'jam_pulang' => null,
                        'status' => 'Lupa Absen Masuk dan Pulang'
                    ]);
                    $tempAttendances->put($loopDateStr, $mock);
                }
            }
        }
        $attendances = $tempAttendances->sortBy('tanggal')->values();
            
        // Calculate statistics
        $stats = [
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'absen' => 0,
        ];

        foreach ($attendances as $att) {
            if ($att->status === 'Hadir') {
                $stats['hadir']++;
            } elseif ($att->status === 'Terlambat') {
                $stats['terlambat']++;
            } elseif (in_array($att->status, ['Izin', 'Sakit', 'Terlambat dan Izin', 'Pulang Cepat / Izin'])) {
                $stats['izin']++;
            } elseif (in_array($att->status, ['Tanpa Keterangan', 'Lupa Absen Masuk', 'Lupa Absen Pulang', 'Lupa Absen Masuk dan Pulang', 'Lupa Absen Masuk dan Izin'])) {
                $stats['absen']++;
            }
        }

        $totalAttendanceCount = $attendances->count();
        $attendanceRate = $totalAttendanceCount > 0 
            ? round((($stats['hadir'] + $stats['terlambat']) / $totalAttendanceCount) * 100) 
            : 0;

        return view('dashboard.peserta.monthly_report_pdf', compact(
            'user',
            'month',
            'year',
            'selectedDate',
            'attendances',
            'stats',
            'attendanceRate'
        ));
    }

    /**
     * Export consolidated monthly report bundle (Attendance + Logbooks + Leave Requests) to a printable page.
     */
    public function exportConsolidatedReport(Request $request)
    {
        $user = Auth::user();
        if (($user->isAdmin() || $user->isSuperAdmin()) && $request->has('user_id')) {
            $targetUser = \App\Models\User::where('user_code', $request->input('user_id'))
                ->orWhere('id', $request->input('user_id'))
                ->firstOrFail();
            if ($user->isAdmin() && (int) $targetUser->pembimbing_id !== (int) $user->id) {
                abort(403, 'Anda tidak memiliki akses ke laporan peserta magang ini.');
            }
            $user = $targetUser;
        }
        
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        
        $selectedDate = Carbon::create($year, $month, 1);
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();
        
        // Load User relationships
        $user->load(['instansi', 'pembimbing']);

        // Fetch attendance records for the selected month
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->tanggal)->toDateString();
            });

        // Fetch all logbooks for the selected month
        $calendarLogbooks = \App\Models\Logbook::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->tanggal)->toDateString();
            });

        // Fetch work schedule overrides
        $schedules = WorkSchedule::where('type', 'date')
            ->whereBetween('specific_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->specific_date)->toDateString();
            });

        // Dynamically mock Lupa Absen Masuk dan Pulang if logbook is present but no attendance on workdays
        $tempAttendances = collect();
        $daysInMonth = $selectedDate->daysInMonth;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $loopDateStr = Carbon::create($year, $month, $day)->toDateString();
            if ($attendances->has($loopDateStr)) {
                $tempAttendances->put($loopDateStr, $attendances->get($loopDateStr));
            } elseif (isset($calendarLogbooks[$loopDateStr]) && $calendarLogbooks[$loopDateStr]->isNotEmpty()) {
                $loopDateObj = Carbon::parse($loopDateStr);
                $schedule = WorkSchedule::getScheduleForDate($loopDateObj);
                $isHoliday = $schedule ? $schedule->is_holiday : false;
                
                if (!$isHoliday && Carbon::parse($loopDateStr)->lessThan(Carbon::today())) {
                    $mock = new Attendance([
                        'user_id' => $user->id,
                        'tanggal' => $loopDateStr,
                        'jam_masuk' => null,
                        'jam_pulang' => null,
                        'status' => 'Lupa Absen Masuk dan Pulang'
                    ]);
                    $tempAttendances->put($loopDateStr, $mock);
                }
            }
        }
        $attendances = $tempAttendances->sortBy('tanggal')->values();
            
        // Calculate statistics
        $stats = [
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'absen' => 0,
        ];

        foreach ($attendances as $att) {
            if ($att->status === 'Hadir') {
                $stats['hadir']++;
            } elseif ($att->status === 'Terlambat') {
                $stats['terlambat']++;
            } elseif (in_array($att->status, ['Izin', 'Sakit', 'Terlambat dan Izin', 'Pulang Cepat / Izin'])) {
                $stats['izin']++;
            } elseif (in_array($att->status, ['Tanpa Keterangan', 'Lupa Absen Masuk', 'Lupa Absen Pulang', 'Lupa Absen Masuk dan Pulang', 'Lupa Absen Masuk dan Izin'])) {
                $stats['absen']++;
            }
        }

        $totalAttendanceCount = $attendances->count();
        $attendanceRate = $totalAttendanceCount > 0 
            ? round((($stats['hadir'] + $stats['terlambat']) / $totalAttendanceCount) * 100) 
            : 0;

        // Fetch logbook entries for the selected month
        $logbooks = \App\Models\Logbook::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->orderBy('tanggal', 'asc')
            ->get();

        // Fetch approved leave requests that start or end in this month
        $leaves = \App\Models\LeaveRequest::where('user_id', $user->id)
            ->where('status_approval', 'Approved')
            ->where(function($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('tanggal_mulai', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                  ->orWhereBetween('tanggal_selesai', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
            })
            ->orderBy('tanggal_mulai', 'asc')
            ->get();

        return view('dashboard.peserta.consolidated_report_pdf', compact(
            'user',
            'month',
            'year',
            'selectedDate',
            'attendances',
            'stats',
            'attendanceRate',
            'logbooks',
            'leaves'
        ));
    }

    /**
     * Export selected month's attendance records to a CSV file.
     */
    public function exportCsv(Request $request)
    {
        $user = Auth::user();
        if (($user->isAdmin() || $user->isSuperAdmin()) && $request->has('user_id')) {
            $targetUser = \App\Models\User::where('user_code', $request->input('user_id'))
                ->orWhere('id', $request->input('user_id'))
                ->firstOrFail();
            if ($user->isAdmin() && (int) $targetUser->pembimbing_id !== (int) $user->id) {
                abort(403, 'Anda tidak memiliki akses ke laporan peserta magang ini.');
            }
            $user = $targetUser;
        }
        
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        
        $selectedDate = Carbon::create($year, $month, 1);
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();
        
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->tanggal)->toDateString();
            });

        // Fetch all logbooks for the selected month
        $calendarLogbooks = \App\Models\Logbook::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->tanggal)->toDateString();
            });

        // Fetch work schedule overrides
        $schedules = WorkSchedule::where('type', 'date')
            ->whereBetween('specific_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->specific_date)->toDateString();
            });

        // Dynamically mock Lupa Absen Masuk dan Pulang if logbook is present but no attendance on workdays
        $tempAttendances = collect();
        $daysInMonth = $selectedDate->daysInMonth;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $loopDateStr = Carbon::create($year, $month, $day)->toDateString();
            if ($attendances->has($loopDateStr)) {
                $tempAttendances->put($loopDateStr, $attendances->get($loopDateStr));
            } elseif (isset($calendarLogbooks[$loopDateStr]) && $calendarLogbooks[$loopDateStr]->isNotEmpty()) {
                $loopDateObj = Carbon::parse($loopDateStr);
                $schedule = WorkSchedule::getScheduleForDate($loopDateObj);
                $isHoliday = $schedule ? $schedule->is_holiday : false;
                
                if (!$isHoliday && Carbon::parse($loopDateStr)->lessThan(Carbon::today())) {
                    $mock = new Attendance([
                        'user_id' => $user->id,
                        'tanggal' => $loopDateStr,
                        'jam_masuk' => null,
                        'jam_pulang' => null,
                        'status' => 'Lupa Absen Masuk dan Pulang'
                    ]);
                    $tempAttendances->put($loopDateStr, $mock);
                }
            }
        }
        $attendances = $tempAttendances->sortBy('tanggal')->values();

        $filename = "Absensi_" . str_replace(' ', '_', $user->nama_lengkap) . "_" . $selectedDate->format('Y-m') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['No', 'Tanggal', 'Jam Masuk', 'Jam Pulang', 'Status Kehadiran'];

        $callback = function() use($attendances, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fwrite($file, "sep=;\n"); // Force MS Excel to open using semicolon separator
            fputcsv($file, $columns, ';'); // Use semicolon for better MS Excel local support

            foreach ($attendances as $index => $att) {
                $row['No'] = $index + 1;
                $row['Tanggal'] = $att->tanggal;
                $row['Jam Masuk'] = $att->jam_masuk ? Carbon::parse($att->jam_masuk)->format('H:i:s') : '-';
                $row['Jam Pulang'] = $att->jam_pulang ? Carbon::parse($att->jam_pulang)->format('H:i:s') : '-';
                $row['Status Kehadiran'] = $att->status;

                fputcsv($file, array_values($row), ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Print custom landscape personnel attendance form with date range filter and dynamic signatures.
     */
    public function printFormulirAbsensi(Request $request)
    {
        $user = Auth::user();
        if (($user->isAdmin() || $user->isSuperAdmin()) && $request->has('user_id')) {
            $targetUser = \App\Models\User::where('user_code', $request->input('user_id'))
                ->orWhere('id', $request->input('user_id'))
                ->firstOrFail();
            if ($user->isAdmin() && (int) $targetUser->pembimbing_id !== (int) $user->id) {
                abort(403, 'Anda tidak memiliki akses ke laporan peserta magang ini.');
            }
            $user = $targetUser;
        }

        $request->validate([
            'start_date'         => ['required', 'date'],
            'end_date'           => ['required', 'date', 'after_or_equal:start_date'],
            'laporan_title'      => ['nullable', 'string', 'max:255'],
            'laporan_subtitle'   => ['nullable', 'string', 'max:255'],
            'laporan_kop'        => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'laporan_foto'       => ['nullable', 'string', 'in:both,masuk,pulang,none'],
            'laporan_header_bg'   => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'laporan_header_text' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'laporan_header_footer' => ['nullable', 'string', 'in:show,hide'],
            'laporan_keterangan' => ['nullable', 'string', 'in:system,wfo,wfh,wfa'],
            'export_format'      => ['nullable', 'string', 'in:pdf,word'],
            'signatures'         => ['required', 'array', 'min:1'],
            'signatures.*.row'      => ['nullable', 'integer', 'min:1', 'max:10'],
            'signatures.*.title'    => ['nullable', 'string', 'max:255'],
            'signatures.*.nama'     => ['nullable', 'string', 'max:255'],
            'signatures.*.nip'      => ['nullable', 'string', 'max:60'],
            'signatures.*.instansi' => ['nullable', 'string', 'max:255'],
            'signatures.*.divisi'   => ['nullable', 'string', 'max:255'],
            'signatures.*.ttd'      => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
        ]);

        app()->setLocale('id');
        Carbon::setLocale('id');

        $startDate = Carbon::parse($request->input('start_date'));
        $endDate   = Carbon::parse($request->input('end_date'));

        $user->load(['instansi', 'pembimbing.instansi']);

        // Load attendances in date range
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->tanggal)->toDateString();
            });

        // Load logbooks in date range
        $logbooks = \App\Models\Logbook::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->tanggal)->toDateString();
            });

        // Load work schedule overrides (holidays)
        $schedules = WorkSchedule::where('type', 'date')
            ->whereBetween('specific_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->specific_date)->toDateString();
            });

        $laporanKeterangan = $request->input('laporan_keterangan', 'system');

        // Build records daily loop
        $records = [];
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $att  = $attendances->get($dateStr);
            $log  = $logbooks->get($dateStr);
            
            // Check holiday or weekend
            $sched = $schedules->get($dateStr);
            $isWeekend = in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
            $isHoliday = $sched ? (bool) $sched->is_holiday : $isWeekend;
            $holidayName = $sched ? $sched->name : ($isWeekend ? 'LIBUR NASIONAL' : null);

            $status = '-';
            $keterangan = '-';
            $masuk = '-';
            $pulang = '-';
            $kegiatan = '';
            $fotoMasuk = null;
            $fotoPulang = null;

            // 1. Calculate system-generated status and default system remarks
            $keteranganSystem = 'MASUK';
            $isIzinOrSakit = false;
            if ($isHoliday) {
                $status = 'Libur';
                $keteranganSystem = 'LIBUR';
                $kegiatan = $holidayName ?: 'LIBUR';
            } elseif ($att) {
                $masuk = $att->jam_masuk ? Carbon::parse($att->jam_masuk)->format('H:i') : '-';
                $pulang = $att->jam_pulang ? Carbon::parse($att->jam_pulang)->format('H:i') : '-';
                $status = $att->status;
                $fotoMasuk = $att->foto_masuk;
                $fotoPulang = $att->foto_pulang;

                $statusLower = strtolower($att->status);
                if (str_contains($statusLower, 'izin') || str_contains($statusLower, 'ijin') || str_contains($statusLower, 'pulang sebelum waktunya')) {
                    $keteranganSystem = 'IZIN';
                    $isIzinOrSakit = true;
                } elseif (str_contains($statusLower, 'sakit')) {
                    $keteranganSystem = 'SAKIT';
                    $isIzinOrSakit = true;
                } elseif (str_contains($statusLower, 'lupa absen masuk')) {
                    $keteranganSystem = 'LUPA ABSEN MASUK';
                } elseif (str_contains($statusLower, 'lupa absen pulang')) {
                    $keteranganSystem = 'LUPA ABSEN PULANG';
                } elseif (str_contains($statusLower, 'alpa')) {
                    $keteranganSystem = 'ALPA';
                } else {
                    $keteranganSystem = 'MASUK';
                }
            } else {
                // Workday but no attendance
                if ($date->lessThan(Carbon::today())) {
                    if ($log) {
                        $status = 'Lupa Absen Masuk dan Pulang';
                        $keteranganSystem = 'LUPA ABSEN';
                    } else {
                        $status = 'Tanpa Keterangan';
                        $keteranganSystem = 'ALPA';
                    }
                } else {
                    $keteranganSystem = '-';
                }
            }

            // 2. Apply override based on user choice (laporan_keterangan)
            $keterangan = $keteranganSystem;
            if (in_array($laporanKeterangan, ['wfo', 'wfh', 'wfa'])) {
                // Keep LIBUR, SAKIT, and IZIN (including early leave) untouched, override everything else
                if ($keteranganSystem !== 'LIBUR' && !$isIzinOrSakit) {
                    $keterangan = strtoupper($laporanKeterangan);
                }
            }

            if ($log) {
                // If there's a logbook, list activities
                $kegiatanRaw = "• " . $log->kegiatan;
                if (!empty($log->deskripsi)) {
                    $descLines = explode("\n", str_replace("\r", "", $log->deskripsi));
                    foreach ($descLines as $line) {
                        if (trim($line) !== '') {
                            $kegiatanRaw .= "\n• " . trim($line);
                        }
                    }
                }
                $kegiatan = $kegiatanRaw;
            }

            $records[] = [
                'tanggal'      => $date->translatedFormat('l, d F Y'),
                'hari'         => $date->translatedFormat('l'),
                'tanggal_indo' => $date->translatedFormat('d F Y'),
                'raw_date'     => $date,
                'masuk'        => $masuk,
                'pulang'       => $pulang,
                'kegiatan'     => $kegiatan,
                'foto_masuk'   => $fotoMasuk,
                'foto_pulang'  => $fotoPulang,
                'status'       => $status,
                'keterangan'   => $keterangan,
                'is_holiday'   => $isHoliday,
                'holiday_name' => $holidayName,
            ];
        }

        // Approver values from request with defaults
        $approvers = [
            'laporan_title'      => $request->input('laporan_title', 'FORMULIR ABSENSI PERSONIL'),
            'laporan_subtitle'   => $request->input('laporan_subtitle', 'KONSULTAN MANAJEMEN DATA DAN INFORMASI JALAN & JEMBATAN'),
        ];

        // Process dynamic signatures list
        $signatures = [];
        $signaturesInput = $request->input('signatures', []);
        foreach ($signaturesInput as $index => $sig) {
            $ttdBase64 = null;
            if ($request->hasFile("signatures.{$index}.ttd")) {
                $file = $request->file("signatures.{$index}.ttd");
                $ttdBase64 = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getPathname()));
            }
            $signatures[] = [
                'row'      => intval($sig['row'] ?? 1),
                'title'    => $sig['title'] ?? '',
                'nama'     => $sig['nama'] ?? '',
                'nip'      => $sig['nip'] ?? '',
                'instansi' => $sig['instansi'] ?? '',
                'divisi'   => $sig['divisi'] ?? '',
                'ttd'      => $ttdBase64
            ];
        }

        $laporanKop = null;
        if ($request->hasFile('laporan_kop')) {
            $file = $request->file('laporan_kop');
            $laporanKop = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getPathname()));
        }

        $fotoMode = $request->input('laporan_foto', 'both');
        $headerBg   = $request->input('laporan_header_bg', '#0c2340');
        $headerText = $request->input('laporan_header_text', '#ffffff');
        $showBrowserHeader = $request->input('laporan_header_footer', 'hide') === 'show';
        $exportFormat = $request->input('export_format', 'pdf');

        // Helper to convert storage or public files into inline Base64 data for Word portability
        $convertToBase64 = function($path) {
            if (empty($path)) return null;
            
            $publicPath = public_path(ltrim($path, '/'));
            if (file_exists($publicPath) && is_file($publicPath)) {
                $mime = mime_content_type($publicPath);
                $data = base64_encode(file_get_contents($publicPath));
                return 'data:' . $mime . ';base64,' . $data;
            }

            $storagePath = storage_path('app/public/' . ltrim($path, '/'));
            if (file_exists($storagePath) && is_file($storagePath)) {
                $mime = mime_content_type($storagePath);
                $data = base64_encode(file_get_contents($storagePath));
                return 'data:' . $mime . ';base64,' . $data;
            }

            return null;
        };

        if ($exportFormat === 'word') {
            if ($user->signature_path) {
                $user->signature_path = $convertToBase64($user->signature_path);
            }
            foreach ($records as &$rec) {
                if ($rec['foto_masuk']) {
                    $rec['foto_masuk'] = $convertToBase64($rec['foto_masuk']);
                }
                if ($rec['foto_pulang']) {
                    $rec['foto_pulang'] = $convertToBase64($rec['foto_pulang']);
                }
            }
            unset($rec); // Break the reference
        }

        if ($exportFormat === 'word') {
            $html = view('dashboard.peserta.formulir_absensi_pdf', compact(
                'user', 'records', 'startDate', 'endDate', 'approvers', 'signatures', 
                'laporanKop', 'fotoMode', 'headerBg', 'headerText', 'showBrowserHeader', 'exportFormat'
            ))->render();

            $filename = 'Formulir_Absensi_' . str_replace(' ', '_', $user->nama_lengkap) . '_' . $startDate->format('d-m-Y') . '.doc';

            return response($html)
                ->header('Content-Type', 'application/vnd.ms-word')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'max-age=0, no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }

        return view('dashboard.peserta.formulir_absensi_pdf', compact(
            'user', 'records', 'startDate', 'endDate', 'approvers', 'signatures', 
            'laporanKop', 'fotoMode', 'headerBg', 'headerText', 'showBrowserHeader', 'exportFormat'
        ));
    }
}
