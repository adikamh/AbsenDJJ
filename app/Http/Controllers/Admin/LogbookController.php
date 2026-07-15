<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use Illuminate\Http\Request;

class LogbookController extends Controller
{
    /**
     * Display a listing of all logbooks from guided interns.
     */
    public function index(Request $request)
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
        
        $guidedInterns = $pembimbing->anakBimbingan()->orderBy('nama_lengkap', 'asc')->get();

        return view('dashboard.admin.logbooks.index', compact(
            'logbooks',
            'pendingLogbooksCount',
            'approvedLogbooksCount',
            'rejectedLogbooksCount',
            'guidedInterns'
        ));
    }

    /**
     * Approve logbook.
     */
    public function approve(Request $request, Logbook $logbook)
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

    /**
     * Reject logbook.
     */
    public function reject(Request $request, Logbook $logbook)
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
}
