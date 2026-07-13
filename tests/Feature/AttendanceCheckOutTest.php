<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Role;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Logbook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceCheckOutTest extends TestCase
{
    use RefreshDatabase;

    private User $peserta;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed settings
        $settings = app(\App\Settings\GeneralSettings::class);
        $settings->jam_masuk = '08:00:00';
        $settings->jam_pulang = '17:00:00';
        $settings->batas_keterlambatan = '08:15:00';
        $settings->latitude_kantor = '-6.898800';
        $settings->longitude_kantor = '107.635800';
        $settings->radius_meter = 100;
        $settings->save();

        // Create roles
        $roleSuper = Role::create(['nama_role' => 'super_admin']);
        $roleAdmin = Role::create(['nama_role' => 'admin']);
        $rolePeserta = Role::create(['nama_role' => 'peserta']);

        $instansi = Instansi::create([
            'nama_instansi' => 'Institut Teknologi Bandung',
            'jenis' => 'Universitas',
        ]);

        $pembimbing = User::create([
            'role_id' => $roleAdmin->id,
            'instansi_id' => $instansi->id,
            'nip' => '123457',
            'nama_lengkap' => 'Admin Test',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->peserta = User::create([
            'role_id' => $rolePeserta->id,
            'instansi_id' => $instansi->id,
            'pembimbing_id' => $pembimbing->id,
            'nip' => '123458',
            'nama_lengkap' => 'Peserta Test',
            'email' => 'peserta@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_intern_cannot_check_out_without_check_in_first(): void
    {
        $response = $this->actingAs($this->peserta)->postJson('/peserta/attendance/check-out', [
            'koordinat' => '-6.898800, 107.635800',
            'foto' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Anda harus melakukan absen masuk terlebih dahulu.',
        ]);
    }

    public function test_intern_cannot_check_out_without_logbook(): void
    {
        // First check in
        Attendance::create([
            'user_id' => $this->peserta->id,
            'tanggal' => Carbon::today()->toDateString(),
            'jam_masuk' => '08:00:00',
            'koordinat_masuk' => '-6.898800, 107.635800',
            'foto_masuk' => 'uploads/attendance/test.jpg',
            'status' => 'Hadir',
        ]);

        $response = $this->actingAs($this->peserta)->postJson('/peserta/attendance/check-out', [
            'koordinat' => '-6.898800, 107.635800',
            'foto' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Anda wajib mengisi minimal 1 logbook kegiatan hari ini sebelum melakukan absen pulang.',
        ]);
    }

    public function test_intern_can_check_out_with_logbook_filled(): void
    {
        // First check in
        Attendance::create([
            'user_id' => $this->peserta->id,
            'tanggal' => Carbon::today()->toDateString(),
            'jam_masuk' => '08:00:00',
            'koordinat_masuk' => '-6.898800, 107.635800',
            'foto_masuk' => 'uploads/attendance/test.jpg',
            'status' => 'Hadir',
        ]);

        // Add a logbook entry for today
        Logbook::create([
            'user_id' => $this->peserta->id,
            'tanggal' => Carbon::today()->toDateString(),
            'kegiatan' => 'Test Kegiatan',
            'deskripsi' => 'Deskripsi kegiatan hari ini.',
            'status_approval' => 'Pending',
        ]);

        $response = $this->actingAs($this->peserta)->postJson('/peserta/attendance/check-out', [
            'koordinat' => '-6.898800, 107.635800',
            'foto' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Absen pulang berhasil dilakukan!',
        ]);

        // Clean up photo if created
        $attendance = Attendance::where('user_id', $this->peserta->id)->first();
        if ($attendance && $attendance->foto_pulang && file_exists(public_path($attendance->foto_pulang))) {
            unlink(public_path($attendance->foto_pulang));
        }
    }
}
