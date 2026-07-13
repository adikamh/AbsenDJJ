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
            
        // Fetch work schedule overrides for the selected month (to display holidays/custom workdays)
        $schedules = WorkSchedule::where('type', 'date')
            ->whereBetween('specific_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->specific_date)->toDateString();
            });

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
            } elseif (in_array($att->status, ['Izin', 'Sakit'])) {
                $stats['izin']++;
            } elseif ($att->status === 'Tanpa Keterangan') {
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
            'attendanceRate'
        ));
    }

    /**
     * Export monthly report bundle (Attendance + Logbooks) to a printable page.
     */
    public function exportMonthlyReport(Request $request)
    {
        $user = Auth::user();
        if ($user->isAdmin() && $request->has('user_id')) {
            $targetUser = \App\Models\User::findOrFail($request->input('user_id'));
            if ($targetUser->pembimbing_id !== $user->id) {
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
            ->orderBy('tanggal', 'asc')
            ->get();
            
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
            } elseif (in_array($att->status, ['Izin', 'Sakit'])) {
                $stats['izin']++;
            } elseif ($att->status === 'Tanpa Keterangan') {
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

        return view('dashboard.peserta.monthly_report_pdf', compact(
            'user',
            'month',
            'year',
            'selectedDate',
            'attendances',
            'stats',
            'attendanceRate',
            'logbooks'
        ));
    }

    /**
     * Export selected month's attendance records to a CSV file.
     */
    public function exportCsv(Request $request)
    {
        $user = Auth::user();
        if ($user->isAdmin() && $request->has('user_id')) {
            $targetUser = \App\Models\User::findOrFail($request->input('user_id'));
            if ($targetUser->pembimbing_id !== $user->id) {
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
            ->orderBy('tanggal', 'asc')
            ->get();

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
}
