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
     * Store a new leave request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'jenis' => ['required', 'in:Izin,Sakit'],
            'alasan' => ['required', 'string', 'max:1000'],
            'file_bukti' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'], // max 2MB
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

        LeaveRequest::create([
            'user_id' => Auth::id(),
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'jenis' => $validated['jenis'],
            'alasan' => $validated['alasan'],
            'file_bukti' => $filePath,
            'status_approval' => 'Pending',
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Pengajuan ' . $validated['jenis'] . ' berhasil dikirim.');
    }
}
