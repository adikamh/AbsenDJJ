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
        $query = $pembimbing->anakBimbingan()->with('instansi');

        if ($request->filled('search')) {
            $query->where('nama_lengkap', 'like', '%' . $request->input('search') . '%');
        }

        $interns = $query->get()->map(function ($intern) {
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

            // Calculate attendance rate (e.g. out of total attendance records or standard 20 days max)
            $totalRecords = $intern->attendances()->count();
            $attendanceRate = $totalRecords > 0 ? round(($totalPresent / $totalRecords) * 100) : 0;

            $intern->today_status = $todayStatus;
            $intern->total_present = $totalPresent;
            $intern->total_logbook = $totalLogbook;
            $intern->attendance_rate = $attendanceRate;

            return $intern;
        });

        // Overall stats for cards
        $totalInternsCount = $interns->count();
        $activeTodayCount = $interns->whereIn('today_status', ['Hadir', 'Terlambat'])->count();
        $onLeaveTodayCount = $interns->whereIn('today_status', ['Izin', 'Sakit'])->count();

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
    public function show(User $intern)
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

        return view('dashboard.admin.interns.show', compact(
            'intern',
            'presentCount',
            'lateCount',
            'leaveCount',
            'sickCount',
            'approvedLogbooksCount',
            'attendances',
            'logbooks'
        ));
    }

    /**
     * Display a listing of all logbooks from guided interns.
     */
    public function logbooks(Request $request)
    {
        $pembimbing = auth()->user();
        
        // Get user IDs of guided interns
        $internIds = $pembimbing->anakBimbingan()->pluck('id');

        $query = Logbook::whereIn('user_id', $internIds)
            ->with('user')
            ->orderBy('tanggal', 'desc');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('kegiatan', 'like', '%' . $search . '%')
                  ->orWhere('deskripsi', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($qu) use ($search) {
                      $qu->where('nama_lengkap', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('status_approval')) {
            $query->where('status_approval', $request->input('status_approval'));
        }

        $logbooks = $query->paginate(5)->withQueryString();

        return view('dashboard.admin.logbooks.index', compact('logbooks'));
    }
}
