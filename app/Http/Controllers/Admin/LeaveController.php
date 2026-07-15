<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    /**
     * Display a listing of all leave requests from guided interns.
     */
    public function index(Request $request)
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

    /**
     * Approve leave request.
     */
    public function approve(Request $request, LeaveRequest $leave)
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

        return redirect()->back()->with('success', 'Pengajuan izin berhasil disetujui.');
    }

    /**
     * Reject leave request.
     */
    public function reject(Request $request, LeaveRequest $leave)
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

        return redirect()->back()->with('success', 'Pengajuan izin berhasil ditolak.');
    }
}
