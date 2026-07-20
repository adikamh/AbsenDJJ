<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Instansi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PesertaController extends Controller
{
    /**
     * Super Admin management view for interns.
     */
    public function managePeserta()
    {
        $peserta = User::with('role', 'instansi', 'pembimbing')
            ->whereHas('role', function ($query) {
                $query->where('nama_role', 'peserta');
            })
            ->orderBy('nama_lengkap')
            ->get();

        $pembimbing = User::with('role')
            ->whereHas('role', function ($query) {
                $query->where('nama_role', 'admin');
            })
            ->orderBy('nama_lengkap')
            ->get();

        $instansi = Instansi::orderBy('nama_instansi')->get();

        return view('dashboard.super_admin.peserta', compact('peserta', 'pembimbing', 'instansi'));
    }

    /**
     * Store a new intern from the Super Admin management view.
     */
    public function storePeserta(Request $request)
    {
        $validated = $request->validateWithBag('storePeserta', [
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
            'password' => ['required', 'string', 'min:8'],
            'nama_darurat_1' => [
                'required',
                'string',
                'max:170',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ],
            'no_darurat_1' => [
                'required',
                'string',
                'max:15',
                'regex:/^[0-9]+$/',
                'different:no_telepon'
            ],
            'hubungan_darurat_1' => [
                'required',
                'string',
                'in:Orang Tua,Wali,Saudara,Suami/Istri,Kerabat,Teman,Lainnya'
            ],
            'nama_darurat_2' => [
                'nullable',
                'required_with:no_darurat_2,hubungan_darurat_2',
                'string',
                'max:170',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ],
            'no_darurat_2' => [
                'nullable',
                'required_with:nama_darurat_2,hubungan_darurat_2',
                'string',
                'max:15',
                'regex:/^[0-9]+$/',
                'different:no_telepon'
            ],
            'hubungan_darurat_2' => [
                'nullable',
                'required_with:nama_darurat_2,no_darurat_2',
                'string',
                'in:Orang Tua,Wali,Saudara,Suami/Istri,Kerabat,Teman,Lainnya'
            ],
            'instansi' => [
                'required',
                'string',
                'max:170',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ],
            'pembimbing_id' => ['required', 'integer', 'exists:users,id'],
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
            'nama_darurat_1.required' => 'Nama kontak darurat 1 wajib diisi.',
            'nama_darurat_1.max' => 'Nama kontak darurat 1 maksimal 170 karakter.',
            'nama_darurat_1.regex' => 'Nama kontak darurat 1 mengandung karakter yang tidak diperbolehkan.',
            'no_darurat_1.regex' => 'No darurat 1 hanya boleh berisi angka.',
            'no_darurat_1.max' => 'No darurat 1 maksimal 15 karakter.',
            'no_darurat_1.different' => 'No darurat 1 tidak boleh sama dengan no telepon peserta.',
            'hubungan_darurat_1.in' => 'Pilihan hubungan darurat 1 tidak valid.',
            'nama_darurat_2.required_with' => 'Nama kontak darurat 2 wajib diisi jika no darurat atau hubungan diisi.',
            'nama_darurat_2.max' => 'Nama kontak darurat 2 maksimal 170 karakter.',
            'nama_darurat_2.regex' => 'Nama kontak darurat 2 mengandung karakter yang tidak diperbolehkan.',
            'no_darurat_2.required_with' => 'No darurat 2 wajib diisi jika nama atau hubungan diisi.',
            'no_darurat_2.regex' => 'No darurat 2 hanya boleh berisi angka.',
            'no_darurat_2.max' => 'No darurat 2 maksimal 15 karakter.',
            'no_darurat_2.different' => 'No darurat 2 tidak boleh sama dengan no telepon peserta.',
            'hubungan_darurat_2.required_with' => 'Hubungan darurat 2 wajib diisi jika nama atau no darurat diisi.',
            'hubungan_darurat_2.in' => 'Pilihan hubungan darurat 2 tidak valid.',
            'instansi.regex' => 'Nama instansi mengandung karakter yang tidak diperbolehkan.',
            'instansi.max' => 'Nama instansi maksimal 170 karakter.',
            'jabatan.max' => 'Jabatan maksimal 170 karakter.',
            'jabatan.regex' => 'Jabatan mengandung karakter yang tidak diperbolehkan.',
        ]);

        $pembimbing = User::whereKey($validated['pembimbing_id'])
            ->whereHas('role', function ($query) {
                $query->where('nama_role', 'admin');
            })
            ->first();

        if (! $pembimbing) {
            return back()
                ->withErrors(['pembimbing_id' => 'Pembimbing yang dipilih tidak valid.'], 'storePeserta')
                ->withInput();
        }

        $rolePeserta = Role::where('nama_role', 'peserta')->firstOrFail();
        $instansi = Instansi::firstOrCreate(
            ['nama_instansi' => $validated['instansi']],
            ['jenis' => 'Lainnya']
        );

        User::create([
            'role_id' => $rolePeserta->id,
            'instansi_id' => $instansi->id,
            'pembimbing_id' => $pembimbing->id,
            'nip' => $validated['nip'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'jabatan' => $validated['jabatan'] ?? null,
            'email' => $validated['email'],
            'no_telepon' => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
            'nama_darurat_1' => $validated['nama_darurat_1'],
            'no_darurat_1' => $validated['no_darurat_1'],
            'hubungan_darurat_1' => $validated['hubungan_darurat_1'],
            'nama_darurat_2' => $validated['nama_darurat_2'],
            'no_darurat_2' => $validated['no_darurat_2'],
            'hubungan_darurat_2' => $validated['hubungan_darurat_2'],
            'password' => Hash::make($validated['password']),
            'status_aktif' => (bool) $validated['status_aktif'],
        ]);

        return redirect()
            ->route('super-admin.peserta')
            ->with('success', 'Peserta berhasil ditambahkan.');
    }

    /**
     * Update an existing intern from the Super Admin management view.
     */
    public function updatePeserta(Request $request, User $peserta)
    {
        abort_unless($peserta->isPeserta(), 404);

        $validated = $request->validateWithBag('updatePeserta', [
            'edit_id' => ['required', 'string', 'max:60'],
            'nip' => [
                'required',
                'string',
                'max:24',
                'unique:users,nip,' . $peserta->id,
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
                'unique:users,email,' . $peserta->id
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
            'nama_darurat_1' => [
                'required',
                'string',
                'max:170',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ],
            'no_darurat_1' => [
                'required',
                'string',
                'max:15',
                'regex:/^[0-9]+$/',
                'different:no_telepon'
            ],
            'hubungan_darurat_1' => [
                'required',
                'string',
                'in:Orang Tua,Wali,Saudara,Suami/Istri,Kerabat,Teman,Lainnya'
            ],
            'nama_darurat_2' => [
                'nullable',
                'required_with:no_darurat_2,hubungan_darurat_2',
                'string',
                'max:170',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ],
            'no_darurat_2' => [
                'nullable',
                'required_with:nama_darurat_2,hubungan_darurat_2',
                'string',
                'max:15',
                'regex:/^[0-9]+$/',
                'different:no_telepon'
            ],
            'hubungan_darurat_2' => [
                'nullable',
                'required_with:nama_darurat_2,no_darurat_2',
                'string',
                'in:Orang Tua,Wali,Saudara,Suami/Istri,Kerabat,Teman,Lainnya'
            ],
            'instansi' => [
                'required',
                'string',
                'max:170',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ],
            'pembimbing_id' => ['required', 'integer', 'exists:users,id'],
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
            'nama_darurat_1.required' => 'Nama kontak darurat 1 wajib diisi.',
            'nama_darurat_1.max' => 'Nama kontak darurat 1 maksimal 170 karakter.',
            'nama_darurat_1.regex' => 'Nama kontak darurat 1 mengandung karakter yang tidak diperbolehkan.',
            'no_darurat_1.regex' => 'No darurat 1 hanya boleh berisi angka.',
            'no_darurat_1.max' => 'No darurat 1 maksimal 15 karakter.',
            'no_darurat_1.different' => 'No darurat 1 tidak boleh sama dengan no telepon peserta.',
            'hubungan_darurat_1.in' => 'Pilihan hubungan darurat 1 tidak valid.',
            'nama_darurat_2.required_with' => 'Nama kontak darurat 2 wajib diisi jika no darurat atau hubungan diisi.',
            'nama_darurat_2.max' => 'Nama kontak darurat 2 maksimal 170 karakter.',
            'nama_darurat_2.regex' => 'Nama kontak darurat 2 mengandung karakter yang tidak diperbolehkan.',
            'no_darurat_2.required_with' => 'No darurat 2 wajib diisi jika nama atau hubungan diisi.',
            'no_darurat_2.regex' => 'No darurat 2 hanya boleh berisi angka.',
            'no_darurat_2.max' => 'No darurat 2 maksimal 15 karakter.',
            'no_darurat_2.different' => 'No darurat 2 tidak boleh sama dengan no telepon peserta.',
            'hubungan_darurat_2.required_with' => 'Hubungan darurat 2 wajib diisi jika nama atau no darurat diisi.',
            'hubungan_darurat_2.in' => 'Pilihan hubungan darurat 2 tidak valid.',
            'instansi.regex' => 'Nama instansi mengandung karakter yang tidak diperbolehkan.',
            'instansi.max' => 'Nama instansi maksimal 170 karakter.',
            'jabatan.max' => 'Jabatan maksimal 170 karakter.',
            'jabatan.regex' => 'Jabatan mengandung karakter yang tidak diperbolehkan.',
        ]);

        $pembimbing = User::whereKey($validated['pembimbing_id'])
            ->whereHas('role', function ($query) {
                $query->where('nama_role', 'admin');
            })
            ->first();

        if (! $pembimbing) {
            return back()
                ->withErrors(['pembimbing_id' => 'Pembimbing yang dipilih tidak valid.'], 'updatePeserta')
                ->withInput();
        }

        $instansi = Instansi::firstOrCreate(
            ['nama_instansi' => $validated['instansi']],
            ['jenis' => 'Lainnya']
        );

        $peserta->update([
            'instansi_id' => $instansi->id,
            'pembimbing_id' => $pembimbing->id,
            'nip' => $validated['nip'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'jabatan' => $validated['jabatan'] ?? null,
            'email' => $validated['email'],
            'no_telepon' => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
            'nama_darurat_1' => $validated['nama_darurat_1'],
            'no_darurat_1' => $validated['no_darurat_1'],
            'hubungan_darurat_1' => $validated['hubungan_darurat_1'],
            'nama_darurat_2' => $validated['nama_darurat_2'],
            'no_darurat_2' => $validated['no_darurat_2'],
            'hubungan_darurat_2' => $validated['hubungan_darurat_2'],
            'status_aktif' => (bool) $validated['status_aktif'],
        ]);

        return redirect()
            ->route('super-admin.peserta')
            ->with('success', 'Data peserta berhasil diperbarui.');
    }

    /**
     * Reset an intern password from the Super Admin management view.
     */
    public function resetPesertaPassword(Request $request, User $peserta)
    {
        abort_unless($peserta->isPeserta(), 404);

        $validated = $request->validateWithBag('resetPesertaPassword', [
            'reset_id' => ['required', 'string', 'max:60'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ]);

        $peserta->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('super-admin.peserta')
            ->with('success', 'Password peserta berhasil direset.');
    }

    /**
     * Delete an intern from the Super Admin management view.
     */
    public function destroyPeserta(User $peserta)
    {
        abort_unless($peserta->isPeserta(), 404);

        $peserta->delete();

        return redirect()
            ->route('super-admin.peserta')
            ->with('success', 'Data peserta berhasil dihapus.');
    }
}
