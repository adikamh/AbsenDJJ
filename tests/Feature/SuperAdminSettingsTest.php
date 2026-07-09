<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $admin;
    private User $peserta;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
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

    public function test_super_admin_can_access_settings_page(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/settings');
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Parameter Global');
    }

    public function test_non_super_admin_cannot_access_settings_page(): void
    {
        // Admin
        $response = $this->actingAs($this->admin)->get('/super-admin/settings');
        $response->assertStatus(403);

        // Peserta
        $response = $this->actingAs($this->peserta)->get('/super-admin/settings');
        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_from_settings(): void
    {
        $response = $this->get('/super-admin/settings');
        $response->assertRedirect('/login');
    }

    public function test_super_admin_can_update_settings(): void
    {
        $data = [
            'jam_masuk' => '07:30',
            'jam_pulang' => '16:30',
            'batas_keterlambatan' => '07:45',
            'latitude_kantor' => '-6.9175',
            'longitude_kantor' => '107.6191',
            'radius_meter' => 150,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->put('/super-admin/settings', $data);

        $response->assertRedirect('/super-admin/settings');
        $response->assertSessionHas('success');

        $settings = app(\App\Settings\GeneralSettings::class);
        $this->assertEquals('07:30:00', $settings->jam_masuk);
        $this->assertEquals('16:30:00', $settings->jam_pulang);
        $this->assertEquals('07:45:00', $settings->batas_keterlambatan);
        $this->assertEquals('-6.9175', $settings->latitude_kantor);
        $this->assertEquals('107.6191', $settings->longitude_kantor);
        $this->assertEquals(150, $settings->radius_meter);
    }

    public function test_update_settings_validation_errors(): void
    {
        $invalidData = [
            'jam_masuk' => 'invalid-time',
            'jam_pulang' => '16:30',
            'batas_keterlambatan' => '07:45',
            'latitude_kantor' => 'not-numeric',
            'longitude_kantor' => '107.6191',
            'radius_meter' => -50, // invalid negative radius
        ];

        $response = $this->actingAs($this->superAdmin)
            ->put('/super-admin/settings', $invalidData);

        $response->assertSessionHasErrors(['jam_masuk', 'latitude_kantor', 'radius_meter']);
    }
}
