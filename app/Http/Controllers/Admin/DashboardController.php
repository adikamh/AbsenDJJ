<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Logbook;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Field Supervisor (Admin) Dashboard.
     */
    public function index(User $user)
    {
        // Get list of guided interns
        $internIds = $user->anakBimbingan()->pluck('id');
        $totalInternsCount = $internIds->count();
        $interns = $user->anakBimbingan()->with('instansi')->limit(3)->get();

        // Get logbooks pending approval for these interns
        $pendingLogbooksQuery = Logbook::with('user')
            ->whereIn('user_id', $internIds)
            ->where('status_approval', 'Pending')
            ->orderBy('tanggal', 'desc');
        $totalPendingLogbooksCount = $pendingLogbooksQuery->count();
        $pendingLogbooks = $pendingLogbooksQuery->limit(3)->get();

        // Get leave requests pending approval for these interns
        $pendingLeavesQuery = LeaveRequest::with('user')
            ->whereIn('user_id', $internIds)
            ->where('status_approval', 'Pending')
            ->orderBy('tanggal_mulai', 'desc');
        $totalPendingLeavesCount = $pendingLeavesQuery->count();
        $pendingLeaves = $pendingLeavesQuery->limit(3)->get();

        // Attendance stats for today
        $today = Carbon::today()->toDateString();
        $hadirTodayCount = Attendance::whereIn('user_id', $internIds)
            ->where('tanggal', $today)
            ->where('status', 'Hadir')
            ->count();

        $terlambatTodayCount = Attendance::whereIn('user_id', $internIds)
            ->where('tanggal', $today)
            ->where('status', 'Terlambat')
            ->count();

        $izinSakitTodayCount = LeaveRequest::whereIn('user_id', $internIds)
            ->where('status_approval', 'Approved')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->count();

        $checkedInIds = Attendance::whereIn('user_id', $internIds)->where('tanggal', $today)->pluck('user_id');
        $onLeaveIds = LeaveRequest::whereIn('user_id', $internIds)->where('status_approval', 'Approved')->whereDate('tanggal_mulai', '<=', $today)->whereDate('tanggal_selesai', '>=', $today)->pluck('user_id');
        $alfaTodayCount = User::whereIn('id', $internIds)
            ->whereNotIn('id', $checkedInIds)
            ->whereNotIn('id', $onLeaveIds)
            ->count();

        // Calculate attendance rates per intern
        $internAttendanceData = User::whereIn('id', $internIds)
            ->withCount(['attendances as present_count' => function($q) {
                $q->whereIn('status', ['Hadir', 'Terlambat']);
            }])
            ->withCount('attendances')
            ->get()
            ->map(function($i) {
                $rate = $i->attendances_count > 0 ? round(($i->present_count / $i->attendances_count) * 100) : 100;
                return [
                    'name' => $i->nama_lengkap,
                    'rate' => $rate
                ];
            });

        // Combined count for stat card header
        $totalHadirToday = $hadirTodayCount + $terlambatTodayCount;

        return view('dashboard.admin.dashboard', compact(
            'interns',
            'pendingLogbooks',
            'pendingLeaves',
            'hadirTodayCount',
            'terlambatTodayCount',
            'izinSakitTodayCount',
            'alfaTodayCount',
            'internAttendanceData',
            'totalHadirToday',
            'totalInternsCount',
            'totalPendingLogbooksCount',
            'totalPendingLeavesCount'
        ));
    }

    public function approveLogbook(Request $request, Logbook $logbook)
    {
        $catatan = $request->input('catatan_pembimbing') ?: null;
        $logbook->update([
            'status_approval' => 'Approved',
            'catatan_pembimbing' => $catatan
        ]);
        
        $intern = $logbook->user;
        $intern->notify(new \App\Notifications\AbsenNotification(
            'Logbook Disetujui',
            'Logbook kegiatan Anda pada tanggal ' . $logbook->tanggal->format('d M Y') . ' telah disetujui.' . ($catatan ? ' Catatan: ' . $catatan : ''),
            'logbook_approved'
        ));

        return redirect()->back()->with('success', 'Logbook berhasil disetujui.');
    }

    public function rejectLogbook(Request $request, Logbook $logbook)
    {
        $catatan = $request->input('catatan_pembimbing') ?: null;
        $logbook->update([
            'status_approval' => 'Rejected',
            'catatan_pembimbing' => $catatan
        ]);

        $intern = $logbook->user;
        $intern->notify(new \App\Notifications\AbsenNotification(
            'Logbook Ditolak',
            'Logbook kegiatan Anda pada tanggal ' . $logbook->tanggal->format('d M Y') . ' ditolak. Catatan: ' . ($catatan ?? '-'),
            'logbook_rejected'
        ));

        return redirect()->back()->with('success', 'Logbook berhasil ditolak.');
    }

    public function approveLeave(Request $request, LeaveRequest $leave)
    {
        $catatan = $request->input('catatan_pembimbing') ?: null;
        $leave->update([
            'status_approval' => 'Approved',
            'catatan_pembimbing' => $catatan
        ]);

        $intern = $leave->user;
        $intern->notify(new \App\Notifications\AbsenNotification(
            'Izin/Sakit Disetujui',
            'Pengajuan ' . $leave->jenis . ' Anda mulai tanggal ' . $leave->tanggal_mulai->format('d M Y') . ' telah disetujui.' . ($catatan ? ' Catatan: ' . $catatan : ''),
            'leave_approved'
        ));

        return redirect()->back()->with('success', 'Pengajuan izin/sakit berhasil disetujui.');
    }

    public function rejectLeave(Request $request, LeaveRequest $leave)
    {
        $catatan = $request->input('catatan_pembimbing') ?: null;
        $leave->update([
            'status_approval' => 'Rejected',
            'catatan_pembimbing' => $catatan
        ]);
        
        $intern = $leave->user;
        $intern->notify(new \App\Notifications\AbsenNotification(
            'Izin/Sakit Ditolak',
            'Pengajuan ' . $leave->jenis . ' Anda mulai tanggal ' . $leave->tanggal_mulai->format('d M Y') . ' ditolak. Catatan: ' . ($catatan ?? '-'),
            'leave_rejected'
        ));

        return redirect()->back()->with('success', 'Pengajuan izin/sakit berhasil ditolak.');
    }
}
