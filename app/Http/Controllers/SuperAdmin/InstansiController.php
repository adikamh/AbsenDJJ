<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Instansi;
use Illuminate\Http\Request;

class InstansiController extends Controller
{
    /**
     * Super Admin view for Kelola Instansi.
     */
    public function manageInstansi()
    {
        $instansi = Instansi::withCount('users')
            ->orderBy('nama_instansi')
            ->get();

        return view('dashboard.super_admin.instansi', compact('instansi'));
    }

    /**
     * Store a new instansi.
     */
    public function storeInstansi(Request $request)
    {
        $validated = $request->validateWithBag('storeInstansi', [
            'nama_instansi' => [
                'required',
                'string',
                'max:170',
                'unique:instansi,nama_instansi',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ],
            'jenis' => ['required', 'string', 'in:Universitas,SMK,Lainnya'],
        ], [
            'nama_instansi.regex' => 'Nama instansi hanya boleh diisi huruf, angka, spasi, dan karakter: / \\ " \' & ( ) . , -',
            'nama_instansi.max' => 'Nama instansi tidak boleh lebih dari 170 karakter.',
            'nama_instansi.unique' => 'Nama instansi ini sudah terdaftar.',
            'jenis.in' => 'Jenis instansi tidak valid.',
        ]);

        Instansi::create($validated);

        return redirect()
            ->route('super-admin.instansi')
            ->with('success', 'Instansi berhasil ditambahkan.');
    }

    /**
     * Update an existing instansi.
     */
    public function updateInstansi(Request $request, Instansi $instansi)
    {
        $validated = $request->validateWithBag('updateInstansi', [
            'nama_instansi' => [
                'required',
                'string',
                'max:170',
                'unique:instansi,nama_instansi,' . $instansi->id,
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ],
            'jenis' => ['required', 'string', 'in:Universitas,SMK,Lainnya'],
        ], [
            'nama_instansi.regex' => 'Nama instansi hanya boleh diisi huruf, angka, spasi, dan karakter: / \\ " \' & ( ) . , -',
            'nama_instansi.max' => 'Nama instansi tidak boleh lebih dari 170 karakter.',
            'nama_instansi.unique' => 'Nama instansi ini sudah terdaftar.',
            'jenis.in' => 'Jenis instansi tidak valid.',
        ]);

        $instansi->update($validated);

        return redirect()
            ->route('super-admin.instansi')
            ->with('success', 'Instansi berhasil diperbarui.');
    }

    /**
     * Delete an instansi.
     */
    public function destroyInstansi(Instansi $instansi)
    {
        if ($instansi->users()->exists()) {
            return redirect()
                ->route('super-admin.instansi')
                ->with('error', 'Instansi tidak dapat dihapus karena masih digunakan oleh pembimbing atau peserta.');
        }

        $instansi->delete();

        return redirect()
            ->route('super-admin.instansi')
            ->with('success', 'Instansi berhasil dihapus.');
    }
}
