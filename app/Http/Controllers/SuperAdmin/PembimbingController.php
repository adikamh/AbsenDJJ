<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Instansi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PembimbingController extends Controller
{
    /**
     * Super Admin management view for field supervisors.
     */
    public function managePembimbing()
    {
        $pembimbing = User::with('role', 'instansi')
            ->whereHas('role', function ($query) {
                $query->where('nama_role', 'admin');
            })
            ->orderBy('nama_lengkap')
            ->get();

        $instansi = Instansi::orderBy('nama_instansi')->get();

        return view('dashboard.super_admin.pembimbing', compact('pembimbing', 'instansi'));
    }

    /**
     * Store a new field supervisor from the Super Admin management view.
     */
    public function storePembimbing(Request $request)
    {
        $validated = $request->validateWithBag('storePembimbing', [
            'nip' => [
                'required',
                'string',
                'max:24',
                'unique:users,nip',
                'regex:/^[0-9]+$/'
            ],
            'nama_lengkap' => [
                'required',
                'string',
                'max:170',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ],
            'jabatan' => [
                'nullable',
                'string',
                'max:170',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:254',
                'unique:users,email'
            ],
            'no_telepon' => [
                'required',
                'string',
                'max:15',
                'regex:/^[0-9]+$/'
            ],
            'alamat' => [
                'required',
                'string',
                'max:224',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&#@:\n\r]*$/'
            ],
            'instansi' => [
                'required',
                'string',
                'max:170',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ],
            'password' => ['required', 'string', 'min:8'],
            'status_aktif' => ['required', 'boolean'],
        ], [
            'nip.regex' => 'NIP hanya boleh berisi angka.',
            'nip.max' => 'NIP maksimal 24 karakter.',
            'nama_lengkap.regex' => 'Nama lengkap mengandung karakter yang tidak diperbolehkan.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 170 karakter.',
            'email.email' => 'Format email tidak valid (harus mengandung @ dan domain yang memiliki titik).',
            'email.max' => 'Email maksimal 254 karakter.',
            'no_telepon.regex' => 'No telepon hanya boleh berisi angka.',
            'no_telepon.max' => 'No telepon maksimal 15 karakter.',
            'alamat.max' => 'Alamat maksimal 224 karakter.',
            'alamat.regex' => 'Alamat mengandung karakter yang tidak diperbolehkan.',
            'instansi.regex' => 'Nama instansi mengandung karakter yang tidak diperbolehkan.',
            'instansi.max' => 'Nama instansi maksimal 170 karakter.',
            'jabatan.max' => 'Jabatan maksimal 170 karakter.',
            'jabatan.regex' => 'Jabatan mengandung karakter yang tidak diperbolehkan.',
        ]);

        $roleAdmin = Role::where('nama_role', 'admin')->firstOrFail();
        $instansi = Instansi::firstOrCreate(
            ['nama_instansi' => $validated['instansi']],
            ['jenis' => 'Lainnya']
        );

        User::create([
            'role_id' => $roleAdmin->id,
            'instansi_id' => $instansi->id,
            'nip' => $validated['nip'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'jabatan' => $validated['jabatan'] ?? null,
            'email' => $validated['email'],
            'no_telepon' => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
            'password' => Hash::make($validated['password']),
            'status_aktif' => (bool) $validated['status_aktif'],
        ]);

        return redirect()
            ->route('super-admin.pembimbing')
            ->with('success', 'Pembimbing berhasil ditambahkan.');
    }

    /**
     * Update an existing field supervisor from the Super Admin management view.
     */
    public function updatePembimbing(Request $request, User $pembimbing)
    {
        abort_unless($pembimbing->isAdmin(), 404);

        $validated = $request->validateWithBag('updatePembimbing', [
            'edit_id' => ['required', 'integer'],
            'nip' => [
                'required',
                'string',
                'max:24',
                'unique:users,nip,' . $pembimbing->id,
                'regex:/^[0-9]+$/'
            ],
            'nama_lengkap' => [
                'required',
                'string',
                'max:170',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ],
            'jabatan' => [
                'nullable',
                'string',
                'max:170',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:254',
                'unique:users,email,' . $pembimbing->id
            ],
            'no_telepon' => [
                'required',
                'string',
                'max:15',
                'regex:/^[0-9]+$/'
            ],
            'alamat' => [
                'required',
                'string',
                'max:224',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&#@:\n\r]*$/'
            ],
            'instansi' => [
                'required',
                'string',
                'max:170',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ],
            'status_aktif' => ['required', 'boolean'],
        ], [
            'nip.regex' => 'NIP hanya boleh berisi angka.',
            'nip.max' => 'NIP maksimal 24 karakter.',
            'nama_lengkap.regex' => 'Nama lengkap mengandung karakter yang tidak diperbolehkan.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 170 karakter.',
            'email.email' => 'Format email tidak valid (harus mengandung @ dan domain yang memiliki titik).',
            'email.max' => 'Email maksimal 254 karakter.',
            'no_telepon.regex' => 'No telepon hanya boleh berisi angka.',
            'no_telepon.max' => 'No telepon maksimal 15 karakter.',
            'alamat.max' => 'Alamat maksimal 224 karakter.',
            'alamat.regex' => 'Alamat mengandung karakter yang tidak diperbolehkan.',
            'instansi.regex' => 'Nama instansi mengandung karakter yang tidak diperbolehkan.',
            'instansi.max' => 'Nama instansi maksimal 170 karakter.',
            'jabatan.max' => 'Jabatan maksimal 170 karakter.',
            'jabatan.regex' => 'Jabatan mengandung karakter yang tidak diperbolehkan.',
        ]);

        $instansi = Instansi::firstOrCreate(
            ['nama_instansi' => $validated['instansi']],
            ['jenis' => 'Lainnya']
        );

        $pembimbing->update([
            'instansi_id' => $instansi->id,
            'nip' => $validated['nip'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'jabatan' => $validated['jabatan'] ?? null,
            'email' => $validated['email'],
            'no_telepon' => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
            'status_aktif' => (bool) $validated['status_aktif'],
        ]);

        return redirect()
            ->route('super-admin.pembimbing')
            ->with('success', 'Data pembimbing berhasil diperbarui.');
    }

    /**
     * Reset a field supervisor password from the Super Admin management view.
     */
    public function resetPembimbingPassword(Request $request, User $pembimbing)
    {
        abort_unless($pembimbing->isAdmin(), 404);

        $validated = $request->validateWithBag('resetPembimbingPassword', [
            'reset_id' => ['required', 'integer'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ]);

        $pembimbing->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('super-admin.pembimbing')
            ->with('success', 'Password pembimbing berhasil direset.');
    }

    /**
     * Delete a field supervisor from the Super Admin management view.
     */
    public function destroyPembimbing(User $pembimbing)
    {
        abort_unless($pembimbing->isAdmin(), 404);

        $pembimbing->delete();

        return redirect()
            ->route('super-admin.pembimbing')
            ->with('success', 'Data pembimbing berhasil dihapus.');
    }
}
