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
            'nip' => ['required', 'string', 'max:50', 'unique:users,nip'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'no_telepon' => ['required', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'alamat' => ['required', 'string', 'max:1000'],
            'instansi' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'status_aktif' => ['required', 'boolean'],
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
            'nip' => ['required', 'string', 'max:50', 'unique:users,nip,' . $pembimbing->id],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $pembimbing->id],
            'no_telepon' => ['required', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'alamat' => ['required', 'string', 'max:1000'],
            'instansi' => ['required', 'string', 'max:255'],
            'status_aktif' => ['required', 'boolean'],
        ]);

        $instansi = Instansi::firstOrCreate(
            ['nama_instansi' => $validated['instansi']],
            ['jenis' => 'Lainnya']
        );

        $pembimbing->update([
            'instansi_id' => $instansi->id,
            'nip' => $validated['nip'],
            'nama_lengkap' => $validated['nama_lengkap'],
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
