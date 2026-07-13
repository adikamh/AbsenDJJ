<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Instansi;
use App\Models\Attendance;
use App\Models\Logbook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInternsTest extends TestCase
{
    use RefreshDatabase;

    protected User $supervisor1;
    protected User $supervisor2;
    protected User $intern1;
    protected User $intern2;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['nama_role' => 'admin']);
        $rolePeserta = Role::create(['nama_role' => 'peserta']);

        $instansi = Instansi::create([
            'nama_instansi' => 'Institut Teknologi Bandung',
            'jenis' => 'Universitas',
        ]);

        // Create supervisors
        $this->supervisor1 = User::create([
            'nama_lengkap' => 'Supervisor Satu',
            'email' => 'supervisor1@absendjj.com',
            'password' => bcrypt('password'),
            'role_id' => $roleAdmin->id,
            'status_aktif' => true,
        ]);

        $this->supervisor2 = User::create([
            'nama_lengkap' => 'Supervisor Dua',
            'email' => 'supervisor2@absendjj.com',
            'password' => bcrypt('password'),
            'role_id' => $roleAdmin->id,
            'status_aktif' => true,
        ]);

        // Create interns and assign to supervisors
        $this->intern1 = User::create([
            'nama_lengkap' => 'Intern Satu ITB',
            'email' => 'intern1@absendjj.com',
            'password' => bcrypt('password'),
            'role_id' => $rolePeserta->id,
            'pembimbing_id' => $this->supervisor1->id,
            'instansi_id' => $instansi->id,
            'status_aktif' => true,
        ]);

        $this->intern2 = User::create([
            'nama_lengkap' => 'Intern Dua ITB',
            'email' => 'intern2@absendjj.com',
            'password' => bcrypt('password'),
            'role_id' => $rolePeserta->id,
            'pembimbing_id' => $this->supervisor2->id,
            'instansi_id' => $instansi->id,
            'status_aktif' => true,
        ]);
    }

    public function test_supervisor_can_view_their_interns_list(): void
    {
        $response = $this->actingAs($this->supervisor1)->get('/admin/interns');
        $response->assertStatus(200);
        $response->assertSee('Intern Satu ITB');
        $response->assertDontSee('Intern Dua ITB');
    }

    public function test_supervisor_can_search_interns_by_name(): void
    {
        // Search matching
        $response = $this->actingAs($this->supervisor1)->get('/admin/interns?search=Satu');
        $response->assertStatus(200);
        $response->assertSee('Intern Satu ITB');

        // Search mismatching
        $response = $this->actingAs($this->supervisor1)->get('/admin/interns?search=Bukan');
        $response->assertStatus(200);
        $response->assertSee('Tidak ada anak bimbingan yang ditemukan.');
    }

    public function test_supervisor_can_view_own_intern_details(): void
    {
        // Seed attendance & logbook
        Attendance::create([
            'user_id' => $this->intern1->id,
            'tanggal' => '2026-07-13',
            'jam_masuk' => '07:25:00',
            'status' => 'Hadir',
        ]);

        Logbook::create([
            'user_id' => $this->intern1->id,
            'tanggal' => '2026-07-13',
            'kegiatan' => 'Implementasi Fitur Admin',
            'deskripsi' => 'Menambahkan fitur manajemen anak bimbingan.',
            'status_approval' => 'Pending',
        ]);

        $response = $this->actingAs($this->supervisor1)->get("/admin/interns/{$this->intern1->id}");
        $response->assertStatus(200);
        $response->assertSee('Intern Satu ITB');
        $response->assertSee('Implementasi Fitur Admin');
        $response->assertSee('Hadir');
    }

    public function test_supervisor_cannot_view_other_supervisor_intern_details(): void
    {
        $response = $this->actingAs($this->supervisor1)->get("/admin/interns/{$this->intern2->id}");
        $response->assertStatus(403);
    }
}
