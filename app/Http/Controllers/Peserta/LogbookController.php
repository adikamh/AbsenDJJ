<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    /**
     * Show list of all logbooks for the logged-in intern.
     */
    public function index()
    {
        $user = Auth::user();
        $logbooks = Logbook::where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        $approvedLogbooksCount = Logbook::where('user_id', $user->id)
            ->where('status_approval', 'Approved')
            ->count();

        return view('dashboard.peserta.logbook', compact('logbooks', 'approvedLogbooksCount'));
    }

    /**
     * Store a new logbook entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'kegiatan' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:2000'],
        ]);

        Logbook::create([
            'user_id' => Auth::id(),
            'tanggal' => $validated['tanggal'],
            'kegiatan' => $validated['kegiatan'],
            'deskripsi' => $validated['deskripsi'],
            'status_approval' => 'Pending',
        ]);

        return redirect()
            ->route('peserta.logbook')
            ->with('success', 'Logbook baru berhasil ditambahkan.');
    }

    /**
     * Update an existing logbook entry.
     */
    public function update(Request $request, Logbook $logbook)
    {
        abort_unless($logbook->user_id === Auth::id(), 403);
        
        if ($logbook->status_approval !== 'Pending') {
            return redirect()
                ->route('peserta.logbook')
                ->with('error', 'Logbook yang sudah disetujui atau ditolak tidak dapat diubah.');
        }

        $validated = $request->validate([
            'kegiatan' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:2000'],
        ]);

        $logbook->update([
            'kegiatan' => $validated['kegiatan'],
            'deskripsi' => $validated['deskripsi'],
        ]);

        return redirect()
            ->route('peserta.logbook')
            ->with('success', 'Logbook berhasil diperbarui.');
    }

    /**
     * Delete an existing logbook entry.
     */
    public function destroy(Logbook $logbook)
    {
        abort_unless($logbook->user_id === Auth::id(), 403);

        if ($logbook->status_approval !== 'Pending') {
            return redirect()
                ->route('peserta.logbook')
                ->with('error', 'Logbook yang sudah disetujui atau ditolak tidak dapat dihapus.');
        }

        $logbook->delete();

        return redirect()
            ->route('peserta.logbook')
            ->with('success', 'Logbook berhasil dihapus.');
    }

    /**
     * Export logbooks to a printable layout.
     */
    public function exportPdf()
    {
        $user = Auth::user();
        $logbooks = Logbook::where('user_id', $user->id)
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('dashboard.peserta.logbook_pdf', compact('logbooks', 'user'));
    }
}
