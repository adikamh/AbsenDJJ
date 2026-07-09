<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminInstansiTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $admin;
    private User $peserta;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic roles
        $roleSuper = Role::create(['nama_role' => 'super_admin']);
        $roleAdmin = Role::create(['nama_role' => 'admin']);
        $rolePeserta = Role::create(['nama_role' => 'peserta']);

        $instansi = Instansi::create([
            'nama_instansi' => 'Institut Teknologi Bandung',
            'jenis' => 'Universitas',
        ]);

        $this->superAdmin = User::create([
            'role_id' => $roleSuper->id,
            'instansi_id' => $instansi->id,
            'nip' => '123456',
            'nama_lengkap' => 'Super Admin Test',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->admin = User::create([
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
            'nip' => '123458',
            'nama_lengkap' => 'Peserta Test',
            'email' => 'peserta@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_super_admin_can_access_manage_instansi_page(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/instansi');
        $response->assertStatus(200);
        $response->assertSee('Daftar Instansi');
    }

    public function test_non_super_admin_cannot_access_manage_instansi_page(): void
    {
        // Admin
        $response = $this->actingAs($this->admin)->get('/super-admin/instansi');
        $response->assertStatus(403);

        // Peserta
        $response = $this->actingAs($this->peserta)->get('/super-admin/instansi');
        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/super-admin/instansi');
        $response->assertRedirect('/login');
    }

    public function test_super_admin_can_store_instansi(): void
    {
        $data = [
            'nama_instansi' => 'SMKN 1 Bandung',
            'jenis' => 'SMK',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post('/super-admin/instansi', $data);

        $response->assertRedirect('/super-admin/instansi');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('instansi', $data);
    }

    public function test_super_admin_can_update_instansi(): void
    {
        $inst = Instansi::create([
            'nama_instansi' => 'SMKN 2 Bandung',
            'jenis' => 'SMK',
        ]);

        $updateData = [
            'nama_instansi' => 'SMKN 2 Bandung Edited',
            'jenis' => 'SMK',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->put("/super-admin/instansi/{$inst->id}", $updateData);

        $response->assertRedirect('/super-admin/instansi');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('instansi', $updateData);
    }

    public function test_super_admin_cannot_delete_instansi_with_users(): void
    {
        // ITB is assigned to $superAdmin, $admin, and $peserta
        $inst = Instansi::where('nama_instansi', 'Institut Teknologi Bandung')->firstOrFail();

        $response = $this->actingAs($this->superAdmin)
            ->delete("/super-admin/instansi/{$inst->id}");

        $response->assertRedirect('/super-admin/instansi');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('instansi', ['id' => $inst->id]);
    }

    public function test_super_admin_can_delete_empty_instansi(): void
    {
        $inst = Instansi::create([
            'nama_instansi' => 'Instansi Kosong',
            'jenis' => 'Lainnya',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->delete("/super-admin/instansi/{$inst->id}");

        $response->assertRedirect('/super-admin/instansi');
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('instansi', ['id' => $inst->id]);
    }
}
