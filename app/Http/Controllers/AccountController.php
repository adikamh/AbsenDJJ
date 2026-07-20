<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Instansi;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    /**
     * Show the profile edit form for the logged-in user.
     */
    public function edit()
    {
        $user = auth()->user();
        $instansi = Instansi::orderBy('nama_instansi')->get();

        return view('dashboard.account', compact('user', 'instansi'));
    }

    /**
     * Update the logged-in user profile details.
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        $rules = [];
        $messages = [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 170 karakter.',
            'nama_lengkap.regex' => 'Nama lengkap mengandung karakter yang tidak diperbolehkan.',
            'no_telepon.required' => 'No telepon wajib diisi.',
            'no_telepon.max' => 'No telepon maksimal 15 karakter.',
            'no_telepon.regex' => 'No telepon hanya boleh berisi angka.',
            'alamat.required' => 'Alamat wajib diisi.',
            'alamat.max' => 'Alamat maksimal 224 karakter.',
            'alamat.regex' => 'Alamat mengandung karakter yang tidak diperbolehkan.',
            
            'nip.required' => 'NIP wajib diisi.',
            'nip.max' => 'NIP maksimal 24 karakter.',
            'nip.regex' => 'NIP hanya boleh berisi angka.',
            'nip.unique' => 'NIP sudah digunakan oleh pengguna lain.',

            'instansi.required' => 'Nama instansi wajib diisi.',
            'instansi.max' => 'Nama instansi maksimal 170 karakter.',
            'instansi.regex' => 'Nama instansi mengandung karakter yang tidak diperbolehkan.',

            'email.required' => 'Email wajib diisi.',
            'email.max' => 'Email maksimal 254 karakter.',
            'email.email' => 'Format email tidak valid (harus mengandung @ dan domain yang memiliki titik).',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',

            'nama_darurat_1.required' => 'Nama kontak darurat 1 wajib diisi.',
            'nama_darurat_1.max' => 'Nama kontak darurat 1 maksimal 170 karakter.',
            'nama_darurat_1.regex' => 'Nama kontak darurat 1 mengandung karakter yang tidak diperbolehkan.',
            'no_darurat_1.required' => 'No telepon darurat 1 wajib diisi.',
            'no_darurat_1.max' => 'No telepon darurat 1 maksimal 15 karakter.',
            'no_darurat_1.regex' => 'No telepon darurat 1 hanya boleh berisi angka.',
            'no_darurat_1.different' => 'No telepon darurat 1 tidak boleh sama dengan no telepon Anda.',
            'hubungan_darurat_1.required' => 'Hubungan darurat 1 wajib dipilih.',
            'hubungan_darurat_1.in' => 'Pilihan hubungan darurat 1 tidak valid.',

            'nama_darurat_2.max' => 'Nama kontak darurat 2 maksimal 170 karakter.',
            'nama_darurat_2.regex' => 'Nama kontak darurat 2 mengandung karakter yang tidak diperbolehkan.',
            'nama_darurat_2.required_with' => 'Nama kontak darurat 2 wajib diisi jika no darurat atau hubungan diisi.',
            'no_darurat_2.max' => 'No telepon darurat 2 maksimal 15 karakter.',
            'no_darurat_2.regex' => 'No telepon darurat 2 hanya boleh berisi angka.',
            'no_darurat_2.different' => 'No telepon darurat 2 tidak boleh sama dengan no telepon Anda.',
            'no_darurat_2.required_with' => 'No telepon darurat 2 wajib diisi jika nama atau hubungan diisi.',
            'hubungan_darurat_2.required_with' => 'Hubungan darurat 2 wajib dipilih jika nama atau no darurat diisi.',
            'hubungan_darurat_2.in' => 'Pilihan hubungan darurat 2 tidak valid.',

            'current_password.required' => 'Password lama wajib diisi untuk mengubah password.',
            'current_password.current_password' => 'Password lama Anda tidak cocok.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi password baru wajib diisi.',
            'jabatan.max' => 'Jabatan maksimal 170 karakter.',
            'jabatan.regex' => 'Jabatan mengandung karakter yang tidak diperbolehkan.',
        ];

        // 1. Password change validation if password field is filled
        if ($request->filled('password')) {
            $rules['current_password'] = ['required', 'current_password'];
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
            $rules['password_confirmation'] = ['required', 'string', 'min:8'];
        }

        // 2. Base rules depending on User Role
        if ($user->isSuperAdmin()) {
            $rules['nama_lengkap'] = ['required', 'string', 'max:170', 'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'];
            $rules['email'] = ['required', 'string', 'email:rfc', 'max:254', 'unique:users,email,' . $user->id];
            $rules['no_telepon'] = ['required', 'string', 'max:15', 'regex:/^[0-9]+$/'];
            $rules['alamat'] = ['required', 'string', 'max:224', 'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&#@:\n\r]*$/'];
        } elseif ($user->isAdmin()) {
            // Pembimbing: Email NOT editable
            $rules['nama_lengkap'] = ['required', 'string', 'max:170', 'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'];
            $rules['no_telepon'] = ['required', 'string', 'max:15', 'regex:/^[0-9]+$/'];
            $rules['alamat'] = ['required', 'string', 'max:224', 'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&#@:\n\r]*$/'];
            $rules['nip'] = ['required', 'string', 'max:24', 'regex:/^[0-9]+$/', 'unique:users,nip,' . $user->id];
            $rules['instansi'] = ['required', 'string', 'max:170', 'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'];
            $rules['jabatan'] = ['nullable', 'string', 'max:170', 'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'];
        } elseif ($user->isPeserta()) {
            // Peserta: Email, Instansi, Pembimbing NOT editable
            $rules['nama_lengkap'] = ['required', 'string', 'max:170', 'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'];
            $rules['no_telepon'] = ['required', 'string', 'max:15', 'regex:/^[0-9]+$/'];
            $rules['alamat'] = ['required', 'string', 'max:224', 'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&#@:\n\r]*$/'];
            $rules['nip'] = ['required', 'string', 'max:24', 'regex:/^[0-9]+$/', 'unique:users,nip,' . $user->id];
            $rules['jabatan'] = ['nullable', 'string', 'max:170', 'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'];
 
            // Emergency contact 1 (Wajib)
            $rules['nama_darurat_1'] = ['required', 'string', 'max:170', 'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'];
            $rules['no_darurat_1'] = ['required', 'string', 'max:15', 'regex:/^[0-9]+$/', 'different:no_telepon'];
            $rules['hubungan_darurat_1'] = ['required', 'string', 'in:Orang Tua,Wali,Saudara,Suami/Istri,Kerabat,Teman,Lainnya'];

            // Emergency contact 2 (Opsional, tapi jika salah satu diisi maka ketiganya wajib)
            $rules['nama_darurat_2'] = [
                'nullable',
                'required_with:no_darurat_2,hubungan_darurat_2',
                'string',
                'max:170',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ];
            $rules['no_darurat_2'] = [
                'nullable',
                'required_with:nama_darurat_2,hubungan_darurat_2',
                'string',
                'max:15',
                'regex:/^[0-9]+$/',
                'different:no_telepon'
            ];
            $rules['hubungan_darurat_2'] = [
                'nullable',
                'required_with:nama_darurat_2,no_darurat_2',
                'string',
                'in:Orang Tua,Wali,Saudara,Suami/Istri,Kerabat,Teman,Lainnya'
            ];
            
            // Signature image upload
            $rules['signature'] = ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'];
        }

        $validated = $request->validate($rules, $messages);

        // 3. Prepare data to update
        $updateData = [];

        if ($user->isSuperAdmin()) {
            $updateData['nama_lengkap'] = $validated['nama_lengkap'];
            $updateData['email'] = $validated['email'];
            $updateData['no_telepon'] = $validated['no_telepon'];
            $updateData['alamat'] = $validated['alamat'];
        } elseif ($user->isAdmin()) {
            $updateData['nama_lengkap'] = $validated['nama_lengkap'];
            $updateData['no_telepon'] = $validated['no_telepon'];
            $updateData['alamat'] = $validated['alamat'];
            $updateData['nip'] = $validated['nip'];
            $updateData['jabatan'] = $validated['jabatan'] ?? null;

            // Get or create instansi
            $instansi = Instansi::firstOrCreate(
                ['nama_instansi' => $validated['instansi']],
                ['jenis' => 'Lainnya']
            );
            $updateData['instansi_id'] = $instansi->id;
        } elseif ($user->isPeserta()) {
            $updateData['nama_lengkap'] = $validated['nama_lengkap'];
            $updateData['no_telepon'] = $validated['no_telepon'];
            $updateData['alamat'] = $validated['alamat'];
            $updateData['nip'] = $validated['nip'];
            $updateData['jabatan'] = $validated['jabatan'] ?? null;

            $updateData['nama_darurat_1'] = $validated['nama_darurat_1'];
            $updateData['no_darurat_1'] = $validated['no_darurat_1'];
            $updateData['hubungan_darurat_1'] = $validated['hubungan_darurat_1'];

            $updateData['nama_darurat_2'] = $validated['nama_darurat_2'] ?? null;
            $updateData['no_darurat_2'] = $validated['no_darurat_2'] ?? null;
            $updateData['hubungan_darurat_2'] = $validated['hubungan_darurat_2'] ?? null;

            // Process uploaded signature
            if ($request->hasFile('signature')) {
                // Delete old signature if exists
                if ($user->signature_path) {
                    $oldRelative = str_replace('storage/', '', $user->signature_path);
                    if (Storage::disk('public')->exists($oldRelative)) {
                        Storage::disk('public')->delete($oldRelative);
                    }
                }
                $path = $request->file('signature')->store('signatures', 'public');
                $updateData['signature_path'] = 'storage/' . $path;
            }
        }

        // Apply password change if filled
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()
            ->route('account.edit')
            ->with('success', 'Akun Anda berhasil diperbarui.');
    }
}
