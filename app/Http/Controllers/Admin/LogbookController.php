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

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->input('tanggal'));
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->input('bulan'));
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->input('tahun'));
        }

        // Get overall stats counts (unfiltered)
        $pendingLogbooksCount = Logbook::whereIn('user_id', $internIds)->where('status_approval', 'Pending')->count();
        $approvedLogbooksCount = Logbook::whereIn('user_id', $internIds)->where('status_approval', 'Approved')->count();
        $rejectedLogbooksCount = Logbook::whereIn('user_id', $internIds)->where('status_approval', 'Rejected')->count();

        $logbooks = $query->paginate(5)->withQueryString();
        
        $guidedInterns = $pembimbing->anakBimbingan()->orderBy('nama_lengkap', 'asc')->get();
        $activeGuidedInterns = $pembimbing->anakBimbingan()->where('status_aktif', true)->orderBy('nama_lengkap', 'asc')->get();

        return view('dashboard.admin.logbooks.index', compact(
            'logbooks',
            'pendingLogbooksCount',
            'approvedLogbooksCount',
            'rejectedLogbooksCount',
            'guidedInterns',
            'activeGuidedInterns'
        ));
    }

    /**
     * Approve logbook.
     */
    public function approve(Request $request, Logbook $logbook)
    {
        if ($logbook->status_approval === 'Revisi') {
            return redirect()->back()->with('error', 'Logbook sedang dalam proses revisi oleh peserta.');
        }

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
        if ($logbook->status_approval === 'Revisi') {
            return redirect()->back()->with('error', 'Logbook sedang dalam proses revisi oleh peserta.');
        }

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

    /**
     * Toggle global auto approve setting for supervisor.
     */
    public function toggleGlobalAutoApprove(Request $request)
    {
        $pembimbing = auth()->user();
        $pembimbing->update([
            'auto_approve_logbook_global' => (bool) $request->input('enabled')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan auto approve global berhasil diperbarui.'
        ]);
    }

    /**
     * Toggle individual auto approve setting for guided intern.
     */
    public function toggleInternAutoApprove(Request $request, \App\Models\User $intern)
    {
        $pembimbing = auth()->user();
        
        // Verify the intern is indeed guided by this supervisor
        if ((int) $intern->pembimbing_id !== (int) $pembimbing->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $intern->update([
            'auto_approve_logbook' => (bool) $request->input('enabled')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan auto approve peserta berhasil diperbarui.'
        ]);
    }

    /**
     * Toggle global photo requirement setting for supervisor.
     */
    public function toggleGlobalPhotoRequirement(Request $request)
    {
        $pembimbing = auth()->user();
        $pembimbing->update([
            'require_photo_attendance_global' => (bool) $request->input('enabled')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan wajib foto global berhasil diperbarui.'
        ]);
    }

    /**
     * Toggle individual photo requirement setting for guided intern.
     */
    public function toggleInternPhotoRequirement(Request $request, \App\Models\User $intern)
    {
        $pembimbing = auth()->user();
        
        // Verify the intern is indeed guided by this supervisor
        if ((int) $intern->pembimbing_id !== (int) $pembimbing->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $intern->update([
            'require_photo_attendance' => (bool) $request->input('enabled')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan wajib foto peserta berhasil diperbarui.'
        ]);
    }

    /**
     * Request revision for logbook.
     */
    public function revision(Request $request, Logbook $logbook)
    {
        if ($logbook->status_approval === 'Revisi') {
            return redirect()->back()->with('error', 'Logbook sudah dalam status memerlukan revisi.');
        }

        $request->validate([
            'catatan_pembimbing' => ['required', 'string', 'max:1000']
        ]);

        $catatan = $request->input('catatan_pembimbing');

        $logbook->update([
            'status_approval' => 'Revisi',
            'catatan_pembimbing' => $catatan
        ]);

        $intern = $logbook->user;
        $intern->notify(new \App\Notifications\AbsenNotification(
            'Logbook Memerlukan Revisi',
            'Logbook kegiatan Anda pada tanggal ' . $logbook->tanggal->format('d M Y') . ' memerlukan revisi. Catatan: ' . $catatan,
            'logbook_revision'
        ));

        return redirect()->back()->with('success', 'Permintaan revisi logbook berhasil dikirim.');
    }
}
