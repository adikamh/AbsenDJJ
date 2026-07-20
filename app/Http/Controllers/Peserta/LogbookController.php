<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Logbook::where('user_id', $user->id)
            ->orderBy('tanggal', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use($search) {
                $q->where('kegiatan', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
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

        $logbooks = $query->paginate(5)->withQueryString();

        $approvedLogbooksCount = Logbook::where('user_id', $user->id)
            ->where('status_approval', 'Approved')
            ->count();

        $existingDates = Logbook::where('user_id', $user->id)
            ->pluck('tanggal')
            ->map(function($date) {
                return \Carbon\Carbon::parse($date)->toDateString();
            })
            ->toArray();

        $todayLogbook = Logbook::where('user_id', $user->id)
            ->whereDate('tanggal', \Carbon\Carbon::today())
            ->first();

        return view('dashboard.peserta.logbook', compact('logbooks', 'approvedLogbooksCount', 'existingDates', 'todayLogbook'));
    }

    /**
     * Store a new logbook entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'kegiatan' => ['required', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:2000'],
        ]);

        // Check if logbook already exists for that user and that date
        $exists = Logbook::where('user_id', Auth::id())
            ->whereDate('tanggal', $validated['tanggal'])
            ->exists();

        if ($exists) {
            return redirect()
                ->route('peserta.logbook')
                ->with('error', 'Anda hanya diperbolehkan menulis satu logbook per hari.');
        }

        $status = $request->input('action') === 'draft' ? 'Draft' : 'Pending';

        if ($status === 'Pending') {
            $pembimbing = Auth::user()->pembimbing;
            if ($pembimbing) {
                if ($pembimbing->auto_approve_logbook_global || Auth::user()->auto_approve_logbook) {
                    $status = 'Approved';
                }
            }
        }

        $logbook = Logbook::create([
            'user_id' => Auth::id(),
            'tanggal' => $validated['tanggal'],
            'kegiatan' => $validated['kegiatan'],
            'tags' => $validated['tags'] ?? null,
            'deskripsi' => $validated['deskripsi'],
            'status_approval' => $status,
        ]);

        if ($status === 'Pending') {
            $pembimbing = Auth::user()->pembimbing;
            if ($pembimbing) {
                $pembimbing->notify(new \App\Notifications\AbsenNotification(
                    'Pengisian Logbook Baru',
                    Auth::user()->nama_lengkap . ' telah mengisi logbook baru untuk tanggal ' . \Carbon\Carbon::parse($validated['tanggal'])->format('d M Y') . '.',
                    'info'
                ));
            }
        }

        $msg = $status === 'Draft' ? 'Logbook berhasil disimpan sebagai draft sementara.' : ($status === 'Approved' ? 'Logbook baru berhasil ditambahkan dan disetujui otomatis.' : 'Logbook baru berhasil ditambahkan.');

        $redirectTo = $request->input('redirect_to') === 'dashboard' ? 'dashboard' : 'peserta.logbook';

        return redirect()
            ->route($redirectTo)
            ->with('success', $msg);
    }

    /**
     * Update an existing logbook entry.
     */
    public function update(Request $request, Logbook $logbook)
    {
        abort_unless((int) $logbook->user_id === (int) Auth::id(), 403);
        
        $previousStatus = $logbook->status_approval;

        if ($logbook->status_approval !== 'Draft' && $logbook->status_approval !== 'Revisi') {
            return redirect()
                ->route('peserta.logbook')
                ->with('error', 'Hanya logbook berstatus Draft atau Revisi yang dapat diubah.');
        }

        $validated = $request->validate([
            'kegiatan' => ['required', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:2000'],
        ]);

        $status = $request->input('action') === 'draft' ? 'Draft' : 'Pending';

        if ($status === 'Pending') {
            // Revisions must go to Pending for manual review, even if auto-approve is active
            if ($previousStatus !== 'Revisi') {
                $pembimbing = Auth::user()->pembimbing;
                if ($pembimbing) {
                    if ($pembimbing->auto_approve_logbook_global || Auth::user()->auto_approve_logbook) {
                        $status = 'Approved';
                    }
                }
            }
        }

        $logbook->update([
            'kegiatan' => $validated['kegiatan'],
            'tags' => $validated['tags'] ?? null,
            'deskripsi' => $validated['deskripsi'],
            'status_approval' => $status,
            'catatan_pembimbing' => ($status === 'Pending' || $status === 'Approved') ? null : $logbook->catatan_pembimbing,
        ]);

        if ($status === 'Pending') {
            $pembimbing = Auth::user()->pembimbing;
            if ($pembimbing) {
                $pembimbing->notify(new \App\Notifications\AbsenNotification(
                    'Pengisian Logbook Baru',
                    Auth::user()->nama_lengkap . ' telah mengisi logbook baru untuk tanggal ' . \Carbon\Carbon::parse($logbook->tanggal)->format('d M Y') . '.',
                    'info'
                ));
            }
        }

        $msg = $status === 'Draft' ? 'Logbook berhasil disimpan sebagai draft sementara.' : ($status === 'Approved' ? 'Logbook berhasil diperbarui dan disetujui otomatis.' : ($previousStatus === 'Revisi' ? 'Logbook revisi berhasil dikirim untuk ditinjau.' : 'Logbook berhasil diperbarui.'));

        return redirect()
            ->route('peserta.logbook')
            ->with('success', $msg);
    }

    /**
     * Delete an existing logbook entry.
     */
    public function destroy(Logbook $logbook)
    {
        abort_unless((int) $logbook->user_id === (int) Auth::id(), 403);

        if ($logbook->status_approval !== 'Draft') {
            return redirect()
                ->route('peserta.logbook')
                ->with('error', 'Hanya logbook berstatus Draft yang dapat dihapus.');
        }

        $logbook->delete();

        return redirect()
            ->route('peserta.logbook')
            ->with('success', 'Logbook berhasil dihapus.');
    }

    /**
     * Export logbooks to a printable layout.
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        if (($user->isAdmin() || $user->isSuperAdmin()) && $request->has('user_id')) {
            $targetUser = \App\Models\User::where('user_code', $request->input('user_id'))
                ->orWhere('id', $request->input('user_id'))
                ->firstOrFail();
            if ($user->isAdmin() && (int) $targetUser->pembimbing_id !== (int) $user->id) {
                abort(403, 'Anda tidak memiliki akses ke data logbook peserta magang ini.');
            }
            $user = $targetUser;
        }
        
        $query = Logbook::where('user_id', $user->id)->orderBy('tanggal', 'asc');
        
        if ($request->filled('month') && $request->filled('year')) {
            $month = (int) $request->input('month');
            $year = (int) $request->input('year');
            $selectedDate = \Carbon\Carbon::create($year, $month, 1);
            $query->whereBetween('tanggal', [$selectedDate->startOfMonth()->toDateString(), $selectedDate->endOfMonth()->toDateString()]);
        }
        
        $logbooks = $query->get();

        return view('dashboard.peserta.logbook_pdf', compact('logbooks', 'user'));
    }

    /**
     * Export logbooks as a CSV file.
     */
    public function exportCsv(Request $request)
    {
        $user = Auth::user();
        if (($user->isAdmin() || $user->isSuperAdmin()) && $request->has('user_id')) {
            $targetUser = \App\Models\User::where('user_code', $request->input('user_id'))
                ->orWhere('id', $request->input('user_id'))
                ->firstOrFail();
            if ($user->isAdmin() && (int) $targetUser->pembimbing_id !== (int) $user->id) {
                abort(403, 'Anda tidak memiliki akses ke data logbook peserta magang ini.');
            }
            $user = $targetUser;
        }
        
        $query = Logbook::where('user_id', $user->id)->orderBy('tanggal', 'asc');
        
        if ($request->filled('month') && $request->filled('year')) {
            $month = (int) $request->input('month');
            $year = (int) $request->input('year');
            $selectedDate = \Carbon\Carbon::create($year, $month, 1);
            $query->whereBetween('tanggal', [$selectedDate->startOfMonth()->toDateString(), $selectedDate->endOfMonth()->toDateString()]);
        }
        
        $logbooks = $query->get();

        $filename = "Logbook_" . str_replace(' ', '_', $user->nama_lengkap) . "_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['No', 'Tanggal', 'Judul Kegiatan', 'Tags', 'Uraian / Deskripsi', 'Status Approval', 'Catatan Pembimbing'];

        $callback = function() use($logbooks, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fwrite($file, "sep=;\n"); // Force MS Excel to open using semicolon separator
            fputcsv($file, $columns, ';'); // Use semicolon for better MS Excel local support

            foreach ($logbooks as $index => $logbook) {
                $row['No'] = $index + 1;
                $row['Tanggal'] = $logbook->tanggal->format('Y-m-d');
                $row['Judul Kegiatan'] = $logbook->kegiatan;
                $row['Tags'] = $logbook->tags ?? '-';
                $row['Uraian / Deskripsi'] = $logbook->deskripsi;
                $row['Status Approval'] = $logbook->status_approval;
                $row['Catatan Pembimbing'] = $logbook->catatan_pembimbing ?? '-';

                fputcsv($file, array_values($row), ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
