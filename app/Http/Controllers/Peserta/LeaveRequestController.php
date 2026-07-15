<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the leave requests with pagination and search.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = LeaveRequest::where('user_id', $user->id)
            ->orderBy('tanggal_mulai', 'desc');

        // Search filter by reason
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('alasan', 'like', "%{$search}%");
        }

        // Filter by type (jenis)
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->input('jenis'));
        }

        // Filter by approval status
        if ($request->filled('status_approval')) {
            $query->where('status_approval', $request->input('status_approval'));
        }

        $leaves = $query->paginate(5)->withQueryString();

        // Calculate totals for Approved requests
        $approvedIzinCount = LeaveRequest::where('user_id', $user->id)
            ->where('jenis', 'Izin')
            ->where('status_approval', 'Approved')
            ->count();

        $approvedSakitCount = LeaveRequest::where('user_id', $user->id)
            ->where('jenis', 'Sakit')
            ->where('status_approval', 'Approved')
            ->count();

        return view('dashboard.peserta.leave', compact('leaves', 'approvedIzinCount', 'approvedSakitCount'));
    }

    /**
     * Store a new leave request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'jenis' => ['required', 'in:Izin,Sakit'],
            'alasan' => ['required', 'string', 'max:1000'],
            'file_bukti' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'], // max 10MB
        ]);

        $filePath = null;
        if ($request->hasFile('file_bukti')) {
            $file = $request->file('file_bukti');
            $fileName = 'bukti_izin_' . Auth::id() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $dirPath = public_path('uploads/leave_proofs');

            if (!File::isDirectory($dirPath)) {
                File::makeDirectory($dirPath, 0755, true, true);
            }

            $file->move($dirPath, $fileName);
            $filePath = 'uploads/leave_proofs/' . $fileName;
        }

        $leave = LeaveRequest::create([
            'user_id' => Auth::id(),
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'jenis' => $validated['jenis'],
            'alasan' => $validated['alasan'],
            'file_bukti' => $filePath,
            'status_approval' => 'Pending',
        ]);

        $pembimbing = Auth::user()->pembimbing;
        if ($pembimbing) {
            $pembimbing->notify(new \App\Notifications\AbsenNotification(
                'Pengajuan ' . $leave->jenis . ' Baru',
                Auth::user()->nama_lengkap . ' telah mengajukan permohonan ' . strtolower($leave->jenis) . ' mulai tanggal ' . \Carbon\Carbon::parse($leave->tanggal_mulai)->format('d M Y') . '.',
                'info'
            ));
        }

        return redirect()
            ->route('peserta.leave')
            ->with('success', 'Pengajuan ' . $validated['jenis'] . ' berhasil dikirim.');
    }
}
