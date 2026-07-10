<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthSingleSessionTest extends TestCase
{
    use RefreshDatabase;

    private Role $roleSuperAdmin;
    private Role $roleAdmin;
    private Instansi $instansi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleSuperAdmin = Role::create(['nama_role' => 'super_admin']);
        $this->roleAdmin = Role::create(['nama_role' => 'admin']);

        $this->instansi = Instansi::create([
            'nama_instansi' => 'Institut Teknologi Bandung',
            'jenis' => 'Universitas',
        ]);
    }

    public function test_non_super_admin_login_invalidates_other_sessions(): void
    {
        $pembimbing = User::create([
            'role_id' => $this->roleAdmin->id,
            'instansi_id' => $this->instansi->id,
            'nip' => '111222',
            'nama_lengkap' => 'Pembimbing Test',
            'email' => 'pembimbing@example.com',
            'password' => bcrypt('password123'),
            'status_aktif' => true,
        ]);

        // Insert multiple sessions for this user
        DB::table('sessions')->insert([
            [
                'id' => 'other_session_1',
                'user_id' => $pembimbing->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0',
                'payload' => 'dummy',
                'last_activity' => time()
            ],
            [
                'id' => 'other_session_2',
                'user_id' => $pembimbing->id,
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Chrome/100',
                'payload' => 'dummy',
                'last_activity' => time()
            ]
        ]);

        // Verify sessions are in the DB before login
        $this->assertEquals(2, DB::table('sessions')->where('user_id', $pembimbing->id)->count());

        $response = $this->post('/login', [
            'email' => 'pembimbing@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($pembimbing);

        // Verify other sessions are deleted
        // Note: the current session ID will be excluded from deletion, but since Laravel's array driver is used in testing,
        // the new session won't be written to database during the request lifecycle.
        // Thus, total sessions in DB for this user should now be 0.
        $this->assertEquals(0, DB::table('sessions')->where('user_id', $pembimbing->id)->count());
    }

    public function test_super_admin_login_does_not_invalidate_other_sessions(): void
    {
        $superAdmin = User::create([
            'role_id' => $this->roleSuperAdmin->id,
            'instansi_id' => $this->instansi->id,
            'nip' => '999999',
            'nama_lengkap' => 'Super Admin Test',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password123'),
            'status_aktif' => true,
        ]);

        // Insert multiple sessions for this user
        DB::table('sessions')->insert([
            [
                'id' => 'sa_session_1',
                'user_id' => $superAdmin->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0',
                'payload' => 'dummy',
                'last_activity' => time()
            ],
            [
                'id' => 'sa_session_2',
                'user_id' => $superAdmin->id,
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Chrome/100',
                'payload' => 'dummy',
                'last_activity' => time()
            ]
        ]);

        // Verify sessions are in the DB before login
        $this->assertEquals(2, DB::table('sessions')->where('user_id', $superAdmin->id)->count());

        $response = $this->post('/login', [
            'email' => 'superadmin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($superAdmin);

        // Verify other sessions are NOT deleted for super admin
        $this->assertEquals(2, DB::table('sessions')->where('user_id', $superAdmin->id)->count());
    }
}
