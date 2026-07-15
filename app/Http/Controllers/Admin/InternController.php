<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Logbook;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InternController extends Controller
{
    /**
     * Display a listing of guided interns.
     */
    public function index(Request $request)
    {
        $pembimbing = auth()->user();
        
        // 1. Calculate overall stats for cards
        $allInterns = $pembimbing->anakBimbingan()->get();
        $totalInternsCount = $allInterns->count();
        
        $activeTodayCount = 0;
        $onLeaveTodayCount = 0;
        
        foreach ($allInterns as $intern) {
            $todayAttendance = $intern->attendances()->whereDate('tanggal', Carbon::today())->first();
            if ($todayAttendance) {
                if (in_array($todayAttendance->status, ['Hadir', 'Terlambat'])) {
                    $activeTodayCount++;
                }
            } else {
                $todayLeave = $intern->leaveRequests()
                    ->where('status_approval', 'Approved')
                    ->whereDate('tanggal_mulai', '<=', Carbon::today())
                    ->whereDate('tanggal_selesai', '>=', Carbon::today())
                    ->first();
                if ($todayLeave && in_array($todayLeave->jenis, ['Izin', 'Sakit'])) {
                    $onLeaveTodayCount++;
                }
            }
        }

        // 2. Query with search filter and pagination
        $query = $pembimbing->anakBimbingan()->with('instansi');

        if ($request->filled('search')) {
            $query->where('nama_lengkap', 'like', '%' . $request->input('search') . '%');
        }

        $interns = $query->paginate(5)->withQueryString()->through(function ($intern) {
            // Get today's attendance status
            $todayAttendance = $intern->attendances()->whereDate('tanggal', Carbon::today())->first();
            $todayStatus = 'Belum Absen';
            
            if ($todayAttendance) {
                $todayStatus = $todayAttendance->status;
            } else {
                $todayLeave = $intern->leaveRequests()
                    ->where('status_approval', 'Approved')
                    ->whereDate('tanggal_mulai', '<=', Carbon::today())
                    ->whereDate('tanggal_selesai', '>=', Carbon::today())
                    ->first();
                if ($todayLeave) {
                    $todayStatus = $todayLeave->jenis;
                }
            }

            // Calculations
            $totalPresent = $intern->attendances()->whereIn('status', ['Hadir', 'Terlambat'])->count();
            $totalLogbook = $intern->logbooks()->where('status_approval', 'Approved')->count();

            // Calculate attendance rate
            $totalRecords = $intern->attendances()->count();
            $attendanceRate = $totalRecords > 0 ? round(($totalPresent / $totalRecords) * 100) : 0;

            $intern->today_status = $todayStatus;
            $intern->total_present = $totalPresent;
            $intern->total_logbook = $totalLogbook;
            $intern->attendance_rate = $attendanceRate;

            return $intern;
        });

        return view('dashboard.admin.interns.index', compact(
            'interns',
            'totalInternsCount',
            'activeTodayCount',
            'onLeaveTodayCount'
        ));
    }

    /**
     * Display details of a specific guided intern.
     */
    public function show(Request $request, User $intern)
    {
        // Security check: must be guided by current admin
        if ($intern->pembimbing_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke data peserta magang ini.');
        }

        // Stats
        $presentCount = $intern->attendances()->where('status', 'Hadir')->count();
        $lateCount = $intern->attendances()->where('status', 'Terlambat')->count();
        $leaveCount = $intern->leaveRequests()->where('status_approval', 'Approved')->where('jenis', 'Izin')->count();
        $sickCount = $intern->leaveRequests()->where('status_approval', 'Approved')->where('jenis', 'Sakit')->count();
        $approvedLogbooksCount = $intern->logbooks()->where('status_approval', 'Approved')->count();

        // Paginated activities
        $attendances = $intern->attendances()
            ->orderBy('tanggal', 'desc')
            ->paginate(5, ['*'], 'attendance_page')
            ->withQueryString();

        $logbooks = $intern->logbooks()
            ->orderBy('tanggal', 'desc')
            ->paginate(5, ['*'], 'logbook_page')
            ->withQueryString();

        // Calendar variables
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        
        $selectedDate = Carbon::create($year, $month, 1);
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();

        // Fetch all attendance records for the selected month to be keyed by date for calendar
        $calendarAttendances = $intern->attendances()
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function($item) {
                return \Carbon\Carbon::parse($item->tanggal)->toDateString();
            });

        // Fetch work schedule overrides for the selected month to show custom holidays
        $schedules = \App\Models\WorkSchedule::where('type', 'date')
            ->whereBetween('specific_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function($item) {
                return \Carbon\Carbon::parse($item->specific_date)->toDateString();
            });

        return view('dashboard.admin.interns.show', compact(
            'intern',
            'presentCount',
            'lateCount',
            'leaveCount',
            'sickCount',
            'approvedLogbooksCount',
            'attendances',
            'logbooks',
            'month',
            'year',
            'selectedDate',
            'calendarAttendances',
            'schedules'
        ));
    }
}
