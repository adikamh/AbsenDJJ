<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Instansi;
use App\Models\Attendance;
use App\Models\Logbook;
use App\Models\LeaveRequest;
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
        $response->assertSee('Hadir');
    }

    public function test_supervisor_cannot_view_other_supervisor_intern_details(): void
    {
        $response = $this->actingAs($this->supervisor1)->get("/admin/interns/{$this->intern2->id}");
        $response->assertStatus(403);
    }

    public function test_supervisor_can_view_interns_logbook_page(): void
    {
        Logbook::create([
            'user_id' => $this->intern1->id,
            'tanggal' => '2026-07-13',
            'kegiatan' => 'Implementasi Fitur Admin',
            'deskripsi' => 'Menambahkan fitur manajemen anak bimbingan.',
            'status_approval' => 'Pending',
        ]);

        Logbook::create([
            'user_id' => $this->intern2->id,
            'tanggal' => '2026-07-13',
            'kegiatan' => 'Implementasi Fitur Lain',
            'deskripsi' => 'Mengerjakan fitur lain.',
            'status_approval' => 'Pending',
        ]);

        $response = $this->actingAs($this->supervisor1)->get('/admin/logbooks');
        $response->assertStatus(200);
        $response->assertSee('Implementasi Fitur Admin');
        $response->assertDontSee('Implementasi Fitur Lain');
        $response->assertViewHas('pendingLogbooksCount', 1);
        $response->assertViewHas('approvedLogbooksCount', 0);
        $response->assertViewHas('rejectedLogbooksCount', 0);
    }

    public function test_supervisor_can_filter_interns_logbooks(): void
    {
        Logbook::create([
            'user_id' => $this->intern1->id,
            'tanggal' => '2026-07-13',
            'kegiatan' => 'Fitur A',
            'deskripsi' => 'Kegiatan A.',
            'status_approval' => 'Approved',
        ]);

        Logbook::create([
            'user_id' => $this->intern1->id,
            'tanggal' => '2026-07-13',
            'kegiatan' => 'Fitur B',
            'deskripsi' => 'Kegiatan B.',
            'status_approval' => 'Pending',
        ]);

        $response = $this->actingAs($this->supervisor1)->get('/admin/logbooks?status_approval=Approved');
        $response->assertStatus(200);
        $response->assertSee('Fitur A');
        $response->assertDontSee('Fitur B');
        $response->assertViewHas('pendingLogbooksCount', 1);
        $response->assertViewHas('approvedLogbooksCount', 1);
        $response->assertViewHas('rejectedLogbooksCount', 0);
    }

    public function test_supervisor_can_view_dashboard_with_correct_highlights(): void
    {
        // Let's create 3 more interns (total 4 under supervisor 1)
        User::create([
            'nama_lengkap' => 'Intern A',
            'email' => 'interna@absendjj.com',
            'password' => bcrypt('password'),
            'role_id' => $this->intern1->role_id,
            'pembimbing_id' => $this->supervisor1->id,
            'instansi_id' => $this->intern1->instansi_id,
            'status_aktif' => true,
        ]);
        User::create([
            'nama_lengkap' => 'Intern B',
            'email' => 'internb@absendjj.com',
            'password' => bcrypt('password'),
            'role_id' => $this->intern1->role_id,
            'pembimbing_id' => $this->supervisor1->id,
            'instansi_id' => $this->intern1->instansi_id,
            'status_aktif' => true,
        ]);
        User::create([
            'nama_lengkap' => 'Intern C',
            'email' => 'internc@absendjj.com',
            'password' => bcrypt('password'),
            'role_id' => $this->intern1->role_id,
            'pembimbing_id' => $this->supervisor1->id,
            'instansi_id' => $this->intern1->instansi_id,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->supervisor1)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('4 Orang');
        $response->assertSee('Kelola Semua Intern (4)');
    }

    public function test_supervisor_can_view_interns_leaves_page(): void
    {
        LeaveRequest::create([
            'user_id' => $this->intern1->id,
            'tanggal_mulai' => '2026-07-10',
            'tanggal_selesai' => '2026-07-12',
            'jenis' => 'Izin',
            'alasan' => 'Urusan keluarga penting',
            'status_approval' => 'Pending',
        ]);

        LeaveRequest::create([
            'user_id' => $this->intern2->id,
            'tanggal_mulai' => '2026-07-10',
            'tanggal_selesai' => '2026-07-12',
            'jenis' => 'Sakit',
            'alasan' => 'Sakit kepala hebat',
            'status_approval' => 'Pending',
        ]);

        $response = $this->actingAs($this->supervisor1)->get('/admin/leaves');
        $response->assertStatus(200);
        $response->assertSee('Urusan keluarga penting');
        $response->assertDontSee('Sakit kepala hebat');
        $response->assertViewHas('pendingLeavesCount', 1);
        $response->assertViewHas('approvedLeavesCount', 0);
        $response->assertViewHas('rejectedLeavesCount', 0);
    }

    public function test_supervisor_can_filter_interns_leaves(): void
    {
        LeaveRequest::create([
            'user_id' => $this->intern1->id,
            'tanggal_mulai' => '2026-07-10',
            'tanggal_selesai' => '2026-07-12',
            'jenis' => 'Izin',
            'alasan' => 'Izin keluarga',
            'status_approval' => 'Approved',
        ]);

        LeaveRequest::create([
            'user_id' => $this->intern1->id,
            'tanggal_mulai' => '2026-07-10',
            'tanggal_selesai' => '2026-07-12',
            'jenis' => 'Sakit',
            'alasan' => 'Demam tinggi',
            'status_approval' => 'Pending',
        ]);

        // Filter by Approved status
        $response = $this->actingAs($this->supervisor1)->get('/admin/leaves?status_approval=Approved');
        $response->assertStatus(200);
        $response->assertSee('Izin keluarga');
        $response->assertDontSee('Demam tinggi');
        $response->assertViewHas('pendingLeavesCount', 1);
        $response->assertViewHas('approvedLeavesCount', 1);
        $response->assertViewHas('rejectedLeavesCount', 0);

        // Filter by Sakit jenis
        $response = $this->actingAs($this->supervisor1)->get('/admin/leaves?jenis=Sakit');
        $response->assertStatus(200);
        $response->assertSee('Demam tinggi');
        $response->assertDontSee('Izin keluarga');
        $response->assertViewHas('pendingLeavesCount', 1);
        $response->assertViewHas('approvedLeavesCount', 1);
        $response->assertViewHas('rejectedLeavesCount', 0);
    }
}
