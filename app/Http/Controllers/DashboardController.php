<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Instansi;
use App\Models\Attendance;
use App\Models\Logbook;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the corresponding role-based dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return $this->superAdminDashboard();
        } elseif ($user->isAdmin()) {
            return $this->adminDashboard($user);
        } else {
            return $this->pesertaDashboard($user);
        }
    }

    /**
     * Super Admin Dashboard.
     */
    private function superAdminDashboard()
    {
        $totalUsers = User::count();
        $totalInstansi = Instansi::count();
        $totalHadirHariIni = Attendance::where('tanggal', Carbon::today()->toDateString())
            ->whereIn('status', ['Hadir', 'Terlambat'])
            ->count();

        $recentUsers = User::with('role', 'instansi')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.super_admin', compact(
            'totalUsers',
            'totalInstansi',
            'totalHadirHariIni',
            'recentUsers'
        ));
    }

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

        return view('dashboard.super_admin_pembimbing', compact('pembimbing'));
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
            'no_telepon' => ['required', 'string', 'max:30'],
            'instansi' => ['required', 'string', 'max:255'],
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
            'password' => Hash::make('password'),
            'status_aktif' => (bool) $validated['status_aktif'],
        ]);

        return redirect()
            ->route('super-admin.pembimbing')
            ->with('success', 'Pembimbing berhasil ditambahkan. Password awal: password');
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
            'no_telepon' => ['required', 'string', 'max:30'],
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
            'status_aktif' => (bool) $validated['status_aktif'],
        ]);

        return redirect()
            ->route('super-admin.pembimbing')
            ->with('success', 'Data pembimbing berhasil diperbarui.');
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

        return view('dashboard.super_admin_peserta', compact('peserta'));
    }

    /**
     * Field Supervisor (Admin) Dashboard.
     */
    private function adminDashboard(User $user)
    {
        // Get list of guided interns
        $interns = $user->anakBimbingan()->with('instansi')->get();
        $internIds = $interns->pluck('id');

        // Get logbooks pending approval for these interns
        $pendingLogbooks = Logbook::with('user')
            ->whereIn('user_id', $internIds)
            ->where('status_approval', 'Pending')
            ->orderBy('tanggal', 'desc')
            ->get();

        // Get leave requests pending approval for these interns
        $pendingLeaves = LeaveRequest::with('user')
            ->whereIn('user_id', $internIds)
            ->where('status_approval', 'Pending')
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        // Attendance stats for today
        $hadirTodayCount = Attendance::whereIn('user_id', $internIds)
            ->where('tanggal', Carbon::today()->toDateString())
            ->whereIn('status', ['Hadir', 'Terlambat'])
            ->count();

        return view('dashboard.admin', compact(
            'interns',
            'pendingLogbooks',
            'pendingLeaves',
            'hadirTodayCount'
        ));
    }

    /**
     * Intern (Peserta) Dashboard.
     */
    private function pesertaDashboard(User $user)
    {
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('tanggal', Carbon::today()->toDateString())
            ->first();

        $recentLogbooks = Logbook::where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->limit(5)
            ->get();

        $recentLeaves = LeaveRequest::where('user_id', $user->id)
            ->orderBy('tanggal_mulai', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.peserta', compact(
            'todayAttendance',
            'recentLogbooks',
            'recentLeaves'
        ));
    }
}
