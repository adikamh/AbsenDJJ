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
        $interns = $user->anakBimbingan()->with('instansi')->get();
        $internIds = $interns->pluck('id');

        // Get logbooks pending approval for these interns
        $pendingLogbooks = Logbook::with('user')
            ->whereIn('user_id', $internIds)
            ->where('status_approval', 'Pending')
            ->orderBy('tanggal', 'desc')
            ->get();

        // Get leave requests pending approval for these interns
        $pendingLeaves = LeaveRequest::with('user')
            ->whereIn('user_id', $internIds)
            ->where('status_approval', 'Pending')
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        // Attendance stats for today
        $hadirTodayCount = Attendance::whereIn('user_id', $internIds)
            ->where('tanggal', Carbon::today()->toDateString())
            ->whereIn('status', ['Hadir', 'Terlambat'])
            ->count();

        return view('dashboard.admin.dashboard', compact(
            'interns',
            'pendingLogbooks',
            'pendingLeaves',
            'hadirTodayCount'
        ));
    }

    public function approveLogbook(Logbook $logbook)
    {
        $logbook->update(['status_approval' => 'Approved']);
        
        $intern = $logbook->user;
        $intern->notify(new \App\Notifications\AbsenNotification(
            'Logbook Disetujui',
            'Logbook kegiatan Anda pada tanggal ' . $logbook->tanggal->format('d M Y') . ' telah disetujui.',
            'logbook_approved'
        ));

        return redirect()->back()->with('success', 'Logbook berhasil disetujui.');
    }

    public function rejectLogbook(Request $request, Logbook $logbook)
    {
        $catatan = $request->input('catatan_pembimbing');
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

    public function approveLeave(LeaveRequest $leave)
    {
        $leave->update(['status_approval' => 'Approved']);

        $intern = $leave->user;
        $intern->notify(new \App\Notifications\AbsenNotification(
            'Izin/Sakit Disetujui',
            'Pengajuan ' . $leave->jenis . ' Anda mulai tanggal ' . $leave->tanggal_mulai->format('d M Y') . ' telah disetujui.',
            'leave_approved'
        ));

        return redirect()->back()->with('success', 'Pengajuan izin/sakit berhasil disetujui.');
    }

    public function rejectLeave(Request $request, LeaveRequest $leave)
    {
        $catatan = $request->input('catatan_pembimbing');
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
