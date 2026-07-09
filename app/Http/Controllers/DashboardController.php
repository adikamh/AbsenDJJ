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

        // Fetch last 7 working days attendance stats
        $attendanceChartData = [];
        $date = Carbon::today();
        $daysCounted = 0;

        while ($daysCounted < 7) {
            if (!$date->isWeekend()) {
                $dateString = $date->toDateString();

                $hadir = Attendance::where('tanggal', $dateString)
                    ->where('status', 'Hadir')
                    ->count();

                $terlambat = Attendance::where('tanggal', $dateString)
                    ->where('status', 'Terlambat')
                    ->count();

                $izin = Attendance::where('tanggal', $dateString)
                    ->whereIn('status', ['Izin', 'Sakit'])
                    ->count();

                $absen = Attendance::where('tanggal', $dateString)
                    ->where('status', 'Tanpa Keterangan')
                    ->count();

                $attendanceChartData[] = [
                    'label' => $date->translatedFormat('d M'),
                    'hadir' => $hadir,
                    'terlambat' => $terlambat,
                    'izin' => $izin,
                    'absen' => $absen,
                ];
                $daysCounted++;
            }
            $date = $date->subDay();
        }

        $attendanceChartData = array_reverse($attendanceChartData);

        return view('dashboard.super_admin', compact(
            'totalUsers',
            'totalInstansi',
            'totalHadirHariIni',
            'recentUsers',
            'attendanceChartData'
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

        $instansi = Instansi::orderBy('nama_instansi')->get();

        return view('dashboard.super_admin_pembimbing', compact('pembimbing', 'instansi'));
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

        return view('dashboard.super_admin_peserta', compact('peserta', 'pembimbing', 'instansi'));
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

    /**
     * Super Admin view for Kelola Instansi.
     */
    public function manageInstansi()
    {
        $instansi = Instansi::withCount('users')
            ->orderBy('nama_instansi')
            ->get();

        return view('dashboard.super_admin_instansi', compact('instansi'));
    }

    /**
     * Store a new instansi.
     */
    public function storeInstansi(Request $request)
    {
        $validated = $request->validateWithBag('storeInstansi', [
            'nama_instansi' => ['required', 'string', 'max:255', 'unique:instansi,nama_instansi'],
            'jenis' => ['required', 'string', 'max:255'],
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
            'nama_instansi' => ['required', 'string', 'max:255', 'unique:instansi,nama_instansi,' . $instansi->id],
            'jenis' => ['required', 'string', 'max:255'],
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

    /**
     * Show general settings form.
     */
    public function editSettings()
    {
        $settings = app(\App\Settings\GeneralSettings::class);
        $dayOverrides = \App\Models\WorkSchedule::where('type', 'day')->get()->keyBy('day_of_week');
        $dateOverrides = \App\Models\WorkSchedule::where('type', 'date')->orderBy('specific_date')->get();

        return view('dashboard.super_admin_settings', compact('settings', 'dayOverrides', 'dateOverrides'));
    }

    /**
     * Update general settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'jam_masuk' => ['required', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'jam_pulang' => ['required', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'batas_keterlambatan' => ['required', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'latitude_kantor' => ['required', 'numeric', 'between:-90,90'],
            'longitude_kantor' => ['required', 'numeric', 'between:-180,180'],
            'radius_meter' => ['required', 'integer', 'min:1'],
        ]);

        $settings = app(\App\Settings\GeneralSettings::class);
        
        $settings->jam_masuk = strlen($validated['jam_masuk']) === 5 ? $validated['jam_masuk'] . ':00' : $validated['jam_masuk'];
        $settings->jam_pulang = strlen($validated['jam_pulang']) === 5 ? $validated['jam_pulang'] . ':00' : $validated['jam_pulang'];
        $settings->batas_keterlambatan = strlen($validated['batas_keterlambatan']) === 5 ? $validated['batas_keterlambatan'] . ':00' : $validated['batas_keterlambatan'];
        
        $settings->latitude_kantor = (string) $validated['latitude_kantor'];
        $settings->longitude_kantor = (string) $validated['longitude_kantor'];
        $settings->radius_meter = (int) $validated['radius_meter'];

        $settings->save();

        return redirect()
            ->route('super-admin.settings')
            ->with('success', 'Pengaturan parameter global berhasil diperbarui.');
    }

    /**
     * Store a new work schedule override (per-day or per-date).
     */
    public function storeScheduleOverride(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:day,date'],
            'day_of_week' => ['required_if:type,day', 'nullable', 'integer', 'between:0,6'],
            'specific_date' => ['required_if:type,date', 'nullable', 'date'],
            'jam_masuk' => ['nullable', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'batas_keterlambatan' => ['nullable', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'jam_pulang' => ['nullable', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'is_holiday' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $isHoliday = $request->boolean('is_holiday');

        // Check for duplicates
        if ($validated['type'] === 'day') {
            $exists = \App\Models\WorkSchedule::where('type', 'day')
                ->where('day_of_week', $validated['day_of_week'])
                ->exists();
            if ($exists) {
                return redirect()->route('super-admin.settings')
                    ->with('error', 'Override untuk hari tersebut sudah ada. Silakan edit yang sudah ada.');
            }
        } else {
            $exists = \App\Models\WorkSchedule::where('type', 'date')
                ->where('specific_date', $validated['specific_date'])
                ->exists();
            if ($exists) {
                return redirect()->route('super-admin.settings')
                    ->with('error', 'Override untuk tanggal tersebut sudah ada. Silakan edit yang sudah ada.');
            }
        }

        $data = [
            'type' => $validated['type'],
            'day_of_week' => $validated['type'] === 'day' ? $validated['day_of_week'] : null,
            'specific_date' => $validated['type'] === 'date' ? $validated['specific_date'] : null,
            'is_holiday' => $isHoliday,
            'keterangan' => $validated['keterangan'] ?? null,
        ];

        if (!$isHoliday) {
            $data['jam_masuk'] = isset($validated['jam_masuk']) ? (strlen($validated['jam_masuk']) === 5 ? $validated['jam_masuk'] . ':00' : $validated['jam_masuk']) : null;
            $data['batas_keterlambatan'] = isset($validated['batas_keterlambatan']) ? (strlen($validated['batas_keterlambatan']) === 5 ? $validated['batas_keterlambatan'] . ':00' : $validated['batas_keterlambatan']) : null;
            $data['jam_pulang'] = isset($validated['jam_pulang']) ? (strlen($validated['jam_pulang']) === 5 ? $validated['jam_pulang'] . ':00' : $validated['jam_pulang']) : null;
        }

        \App\Models\WorkSchedule::create($data);

        return redirect()
            ->route('super-admin.settings')
            ->with('success', 'Jadwal override berhasil ditambahkan.');
    }

    /**
     * Update an existing work schedule override.
     */
    public function updateScheduleOverride(Request $request, \App\Models\WorkSchedule $schedule)
    {
        $validated = $request->validate([
            'jam_masuk' => ['nullable', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'batas_keterlambatan' => ['nullable', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'jam_pulang' => ['nullable', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'is_holiday' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $isHoliday = $request->boolean('is_holiday');

        $data = [
            'is_holiday' => $isHoliday,
            'keterangan' => $validated['keterangan'] ?? $schedule->keterangan,
        ];

        if ($isHoliday) {
            $data['jam_masuk'] = null;
            $data['batas_keterlambatan'] = null;
            $data['jam_pulang'] = null;
        } else {
            $data['jam_masuk'] = isset($validated['jam_masuk']) ? (strlen($validated['jam_masuk']) === 5 ? $validated['jam_masuk'] . ':00' : $validated['jam_masuk']) : $schedule->jam_masuk;
            $data['batas_keterlambatan'] = isset($validated['batas_keterlambatan']) ? (strlen($validated['batas_keterlambatan']) === 5 ? $validated['batas_keterlambatan'] . ':00' : $validated['batas_keterlambatan']) : $schedule->batas_keterlambatan;
            $data['jam_pulang'] = isset($validated['jam_pulang']) ? (strlen($validated['jam_pulang']) === 5 ? $validated['jam_pulang'] . ':00' : $validated['jam_pulang']) : $schedule->jam_pulang;
        }

        $schedule->update($data);

        return redirect()
            ->route('super-admin.settings')
            ->with('success', 'Jadwal override berhasil diperbarui.');
    }

    /**
     * Delete a work schedule override (reverts to default).
     */
    public function destroyScheduleOverride(\App\Models\WorkSchedule $schedule)
    {
        $schedule->delete();

        return redirect()
            ->route('super-admin.settings')
            ->with('success', 'Jadwal override berhasil dihapus, kembali ke default.');
    }

    /**
     * Sync Indonesian national holidays from public API for a given year.
     */
    public function syncHolidays(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'between:2020,2035'],
        ]);

        $year = $validated['year'];
        
        try {
            $response = \Illuminate\Support\Facades\Http::get("https://api-hari-libur.vercel.app/api?year={$year}");

            if ($response->failed()) {
                return redirect()->route('super-admin.settings')
                    ->with('error', 'Gagal menghubungi API Hari Libur. Silakan coba lagi nanti.');
            }

            $body = $response->json();
            if (!isset($body['status']) || $body['status'] !== 'success' || !isset($body['data'])) {
                return redirect()->route('super-admin.settings')
                    ->with('error', 'Format data API Hari Libur tidak valid.');
            }

            $holidays = $body['data'];
            $importedCount = 0;

            foreach ($holidays as $holiday) {
                $dateStr = $holiday['date'];
                $desc = $holiday['description'];

                // Check duplicate
                $exists = \App\Models\WorkSchedule::where('type', 'date')
                    ->where('specific_date', $dateStr)
                    ->exists();

                if (!$exists) {
                    \App\Models\WorkSchedule::create([
                        'type' => 'date',
                        'specific_date' => $dateStr,
                        'is_holiday' => true,
                        'keterangan' => $desc,
                    ]);
                    $importedCount++;
                }
            }

            return redirect()->route('super-admin.settings')
                ->with('success', "Berhasil mengimpor {$importedCount} hari libur nasional untuk tahun {$year}.");
        } catch (\Exception $e) {
            return redirect()->route('super-admin.settings')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}

