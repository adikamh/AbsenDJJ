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

        // Get overall stats counts (unfiltered)
        $pendingLogbooksCount = Logbook::whereIn('user_id', $internIds)->where('status_approval', 'Pending')->count();
        $approvedLogbooksCount = Logbook::whereIn('user_id', $internIds)->where('status_approval', 'Approved')->count();
        $rejectedLogbooksCount = Logbook::whereIn('user_id', $internIds)->where('status_approval', 'Rejected')->count();

        $logbooks = $query->paginate(5)->withQueryString();

        return view('dashboard.admin.logbooks.index', compact(
            'logbooks',
            'pendingLogbooksCount',
            'approvedLogbooksCount',
            'rejectedLogbooksCount'
        ));
    }

    /**
     * Display a listing of all leave requests from guided interns.
     */
    public function leaves(Request $request)
    {
        $pembimbing = auth()->user();
        
        // Get user IDs of guided interns
        $internIds = $pembimbing->anakBimbingan()->pluck('id');

        $query = LeaveRequest::whereIn('user_id', $internIds)
            ->with('user')
            ->orderBy('tanggal_mulai', 'desc');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('alasan', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($qu) use ($search) {
                      $qu->where('nama_lengkap', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('status_approval')) {
            $query->where('status_approval', $request->input('status_approval'));
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->input('jenis'));
        }

        // Get overall stats counts (unfiltered)
        $pendingLeavesCount = LeaveRequest::whereIn('user_id', $internIds)->where('status_approval', 'Pending')->count();
        $approvedLeavesCount = LeaveRequest::whereIn('user_id', $internIds)->where('status_approval', 'Approved')->count();
        $rejectedLeavesCount = LeaveRequest::whereIn('user_id', $internIds)->where('status_approval', 'Rejected')->count();

        $leaves = $query->paginate(5)->withQueryString();

        return view('dashboard.admin.leaves.index', compact(
            'leaves',
            'pendingLeavesCount',
            'approvedLeavesCount',
            'rejectedLeavesCount'
        ));
    }
}
