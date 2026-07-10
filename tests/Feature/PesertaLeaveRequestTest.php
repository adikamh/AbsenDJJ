<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PesertaLeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $peserta;
    private Role $rolePeserta;
    private Instansi $instansi;

    protected function setUp(): void
    {
        parent::setUp();

        $roleSuper = Role::create(['nama_role' => 'super_admin']);
        $roleAdmin = Role::create(['nama_role' => 'admin']);
        $this->rolePeserta = Role::create(['nama_role' => 'peserta']);

        $this->instansi = Instansi::create([
            'nama_instansi' => 'Institut Teknologi Bandung',
            'jenis' => 'Universitas',
        ]);

        $pembimbing = User::create([
            'role_id' => $roleAdmin->id,
            'instansi_id' => $this->instansi->id,
            'nip' => '123457',
            'nama_lengkap' => 'Admin Test',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->peserta = User::create([
            'role_id' => $this->rolePeserta->id,
            'instansi_id' => $this->instansi->id,
            'pembimbing_id' => $pembimbing->id,
            'nip' => '123458',
            'nama_lengkap' => 'Peserta Test',
            'email' => 'peserta@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_peserta_can_submit_leave_request_without_file(): void
    {
        $response = $this->actingAs($this->peserta)->post('/peserta/leave-request', [
            'tanggal_mulai' => '2026-07-15',
            'tanggal_selesai' => '2026-07-16',
            'jenis' => 'Izin',
            'alasan' => 'Ada keperluan keluarga penting.',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $this->peserta->id,
            'jenis' => 'Izin',
            'alasan' => 'Ada keperluan keluarga penting.',
            'status_approval' => 'Pending',
        ]);
    }

    public function test_peserta_can_submit_leave_request_with_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('dokter.jpg');

        $response = $this->actingAs($this->peserta)->post('/peserta/leave-request', [
            'tanggal_mulai' => '2026-07-15',
            'tanggal_selesai' => '2026-07-16',
            'jenis' => 'Sakit',
            'alasan' => 'Sakit demam tinggi.',
            'file_bukti' => $file,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $this->peserta->id,
            'jenis' => 'Sakit',
            'alasan' => 'Sakit demam tinggi.',
        ]);
        
        // Clean up uploaded files in public/uploads if any
        $leave = \App\Models\LeaveRequest::where('user_id', $this->peserta->id)->first();
        $this->assertNotNull($leave->file_bukti);
        $this->assertFileExists(public_path($leave->file_bukti));
        
        // Delete test file
        if (file_exists(public_path($leave->file_bukti))) {
            unlink(public_path($leave->file_bukti));
        }
    }

    public function test_leave_request_validation_errors(): void
    {
        // End date before start date
        $response = $this->actingAs($this->peserta)->post('/peserta/leave-request', [
            'tanggal_mulai' => '2026-07-16',
            'tanggal_selesai' => '2026-07-15',
            'jenis' => 'Izin',
            'alasan' => 'Alasan asal.',
        ]);

        $response->assertSessionHasErrors('tanggal_selesai');
    }

    public function test_dashboard_displays_today_approved_leave(): void
    {
        $leave = \App\Models\LeaveRequest::create([
            'user_id' => $this->peserta->id,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'jenis' => 'Sakit',
            'alasan' => 'Demam tinggi.',
            'status_approval' => 'Approved',
        ]);

        $response = $this->actingAs($this->peserta)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('todayLeave');
        $response->assertSee('Sakit');
    }
}
