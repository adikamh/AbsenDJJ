<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLoginStatusTest extends TestCase
{
    use RefreshDatabase;

    private Role $roleAdmin;
    private Role $rolePeserta;
    private Instansi $instansi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleAdmin = Role::create(['nama_role' => 'admin']);
        $this->rolePeserta = Role::create(['nama_role' => 'peserta']);

        $this->instansi = Instansi::create([
            'nama_instansi' => 'Institut Teknologi Bandung',
            'jenis' => 'Universitas',
        ]);
    }

    public function test_active_pembimbing_can_login(): void
    {
        $pembimbing = User::create([
            'role_id' => $this->roleAdmin->id,
            'instansi_id' => $this->instansi->id,
            'nip' => '111222',
            'nama_lengkap' => 'Pembimbing Aktif',
            'email' => 'active_pembimbing@example.com',
            'password' => bcrypt('password123'),
            'status_aktif' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'active_pembimbing@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($pembimbing);
    }

    public function test_inactive_pembimbing_cannot_login(): void
    {
        User::create([
            'role_id' => $this->roleAdmin->id,
            'instansi_id' => $this->instansi->id,
            'nip' => '111223',
            'nama_lengkap' => 'Pembimbing Nonaktif',
            'email' => 'inactive_pembimbing@example.com',
            'password' => bcrypt('password123'),
            'status_aktif' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive_pembimbing@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Akun Anda dinonaktifkan. Silakan hubungi administrator.',
            session('errors')->first('email')
        );
        $this->assertGuest();
    }

    public function test_active_peserta_can_login(): void
    {
        $peserta = User::create([
            'role_id' => $this->rolePeserta->id,
            'instansi_id' => $this->instansi->id,
            'nip' => '222111',
            'nama_lengkap' => 'Peserta Aktif',
            'email' => 'active_peserta@example.com',
            'password' => bcrypt('password123'),
            'status_aktif' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'active_peserta@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($peserta);
    }

    public function test_inactive_peserta_cannot_login(): void
    {
        User::create([
            'role_id' => $this->rolePeserta->id,
            'instansi_id' => $this->instansi->id,
            'nip' => '222112',
            'nama_lengkap' => 'Peserta Nonaktif',
            'email' => 'inactive_peserta@example.com',
            'password' => bcrypt('password123'),
            'status_aktif' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive_peserta@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Akun Anda dinonaktifkan. Silakan hubungi administrator.',
            session('errors')->first('email')
        );
        $this->assertGuest();
    }
}
