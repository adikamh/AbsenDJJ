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

        \Illuminate\Support\Facades\Http::fake([
            'api-hari-libur.vercel.app/*' => \Illuminate\Support\Facades\Http::response([
                'status' => 'success',
                'code' => 200,
                'data' => [
                    [
                        'date' => '2026-08-17',
                        'description' => 'Hari Kemerdekaan RI'
                    ]
                ],
                'message' => 'Holidays Found'
            ], 200)
        ]);

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

    // ===== Schedule Override CRUD Tests =====

    public function test_super_admin_can_create_day_override(): void
    {
        $data = [
            'type' => 'day',
            'day_of_week' => 5, // Jumat
            'jam_masuk' => '07:00',
            'batas_keterlambatan' => '07:30',
            'jam_pulang' => '11:30',
            'keterangan' => 'Jadwal Jumat',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post('/super-admin/schedules', $data);

        $response->assertRedirect('/super-admin/settings');
        $this->assertDatabaseHas('work_schedules', [
            'type' => 'day',
            'day_of_week' => 5,
            'jam_masuk' => '07:00:00',
        ]);
    }

    public function test_super_admin_can_create_date_override(): void
    {
        $data = [
            'type' => 'date',
            'specific_date' => '2026-08-17',
            'is_holiday' => 1,
            'keterangan' => 'Hari Kemerdekaan RI',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post('/super-admin/schedules', $data);

        $response->assertRedirect('/super-admin/settings');
        $this->assertDatabaseHas('work_schedules', [
            'type' => 'date',
            'specific_date' => '2026-08-17 00:00:00',
            'is_holiday' => 1,
        ]);
    }

    public function test_super_admin_can_update_schedule_override(): void
    {
        $schedule = \App\Models\WorkSchedule::create([
            'type' => 'day',
            'day_of_week' => 5,
            'jam_masuk' => '07:00:00',
            'batas_keterlambatan' => '07:30:00',
            'jam_pulang' => '11:30:00',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->put('/super-admin/schedules/' . $schedule->id, [
                'jam_masuk' => '07:15',
                'batas_keterlambatan' => '07:45',
                'jam_pulang' => '12:00',
            ]);

        $response->assertRedirect('/super-admin/settings');
        $this->assertDatabaseHas('work_schedules', [
            'id' => $schedule->id,
            'jam_masuk' => '07:15:00',
        ]);
    }

    public function test_super_admin_can_delete_schedule_override(): void
    {
        $schedule = \App\Models\WorkSchedule::create([
            'type' => 'day',
            'day_of_week' => 0,
            'is_holiday' => true,
            'keterangan' => 'Minggu Libur',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->delete('/super-admin/schedules/' . $schedule->id);

        $response->assertRedirect('/super-admin/settings');
        $this->assertDatabaseMissing('work_schedules', ['id' => $schedule->id]);
    }

    public function test_duplicate_day_override_is_rejected(): void
    {
        \App\Models\WorkSchedule::create([
            'type' => 'day',
            'day_of_week' => 5,
            'jam_masuk' => '07:00:00',
            'batas_keterlambatan' => '07:30:00',
            'jam_pulang' => '11:30:00',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post('/super-admin/schedules', [
                'type' => 'day',
                'day_of_week' => 5,
                'jam_masuk' => '08:00',
                'batas_keterlambatan' => '08:15',
                'jam_pulang' => '12:00',
            ]);

        $response->assertRedirect('/super-admin/settings');
        $response->assertSessionHas('error');
    }

    public function test_super_admin_can_sync_holidays_from_api(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'api-hari-libur.vercel.app/*' => \Illuminate\Support\Facades\Http::response([
                'status' => 'success',
                'code' => 200,
                'data' => [
                    [
                        'date' => '2026-08-17',
                        'description' => 'Hari Kemerdekaan RI'
                    ]
                ],
                'message' => 'Holidays Found'
            ], 200)
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post('/super-admin/schedules/sync-holidays', [
                'year' => 2026,
            ]);

        $response->assertRedirect('/super-admin/settings');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('work_schedules', [
            'type' => 'date',
            'specific_date' => '2026-08-17 00:00:00',
            'is_holiday' => 1,
            'keterangan' => 'Hari Kemerdekaan RI',
        ]);
    }
}

