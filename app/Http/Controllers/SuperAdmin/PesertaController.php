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
            'nip' => ['required', 'string', 'max:50', 'unique:users,nip'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'no_telepon' => ['required', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'alamat' => ['required', 'string', 'max:1000'],
            'password' => ['required', 'string', 'min:8'],
            'no_darurat_1' => ['required', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'hubungan_darurat_1' => ['required', 'string', 'max:100'],
            'no_darurat_2' => ['required', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'hubungan_darurat_2' => ['required', 'string', 'max:100'],
            'instansi' => ['required', 'string', 'max:255'],
            'pembimbing_id' => ['required', 'integer', 'exists:users,id'],
            'status_aktif' => ['required', 'boolean'],
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
            'email' => $validated['email'],
            'no_telepon' => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
            'no_darurat_1' => $validated['no_darurat_1'],
            'hubungan_darurat_1' => $validated['hubungan_darurat_1'],
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
            'edit_id' => ['required', 'integer'],
            'nip' => ['required', 'string', 'max:50', 'unique:users,nip,' . $peserta->id],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $peserta->id],
            'no_telepon' => ['required', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'alamat' => ['required', 'string', 'max:1000'],
            'no_darurat_1' => ['required', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'hubungan_darurat_1' => ['required', 'string', 'max:100'],
            'no_darurat_2' => ['required', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'hubungan_darurat_2' => ['required', 'string', 'max:100'],
            'instansi' => ['required', 'string', 'max:255'],
            'pembimbing_id' => ['required', 'integer', 'exists:users,id'],
            'status_aktif' => ['required', 'boolean'],
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
            'email' => $validated['email'],
            'no_telepon' => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
            'no_darurat_1' => $validated['no_darurat_1'],
            'hubungan_darurat_1' => $validated['hubungan_darurat_1'],
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
            'reset_id' => ['required', 'integer'],
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
