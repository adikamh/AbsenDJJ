<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Logbook;
use App\Models\LeaveRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $peserta;
    protected User $pembimbing;

    protected function setUp(): void
    {
        parent::setUp();

        $rolePeserta = Role::create(['nama_role' => 'peserta']);
        $roleAdmin = Role::create(['nama_role' => 'admin']);

        $this->pembimbing = User::create([
            'nama_lengkap' => 'Pak Hendra Wijaya',
            'email' => 'hendra@absendjj.com',
            'password' => bcrypt('password'),
            'role_id' => $roleAdmin->id,
            'status_aktif' => true,
        ]);

        $this->peserta = User::create([
            'nama_lengkap' => 'Adit Pratama',
            'email' => 'adit@absendjj.com',
            'password' => bcrypt('password'),
            'role_id' => $rolePeserta->id,
            'status_aktif' => true,
        ]);
    }

    public function test_intern_can_get_notifications_via_api(): void
    {
        // Initially, notifications are empty
        $response = $this->actingAs($this->peserta)->get('/peserta/notifications');
        $response->assertStatus(200);
        $response->assertJsonCount(0);

        // Notify intern
        $this->peserta->notify(new \App\Notifications\AbsenNotification(
            'Test Title',
            'Test Message',
            'test_type'
        ));

        // Now, we should have 1 unread notification
        $response = $this->actingAs($this->peserta)->get('/peserta/notifications');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'title' => 'Test Title',
            'message' => 'Test Message',
            'type' => 'test_type',
        ]);
    }

    public function test_intern_can_mark_notifications_as_read(): void
    {
        $this->peserta->notify(new \App\Notifications\AbsenNotification(
            'Test Title',
            'Test Message',
            'test_type'
        ));

        $this->assertEquals(1, $this->peserta->unreadNotifications()->count());

        $response = $this->actingAs($this->peserta)->post('/peserta/notifications/mark-read');
        $response->assertStatus(200);

        $this->assertEquals(0, $this->peserta->unreadNotifications()->count());
    }

    public function test_supervisor_approval_sends_notification(): void
    {
        $logbook = Logbook::create([
            'user_id' => $this->peserta->id,
            'tanggal' => '2026-07-10',
            'kegiatan' => 'Rancang DB',
            'tags' => 'mysql',
            'deskripsi' => 'Pekerjaan database.',
            'status_approval' => 'Pending',
        ]);

        $leave = LeaveRequest::create([
            'user_id' => $this->peserta->id,
            'tanggal_mulai' => '2026-07-10',
            'tanggal_selesai' => '2026-07-12',
            'jenis' => 'Izin',
            'alasan' => 'Ada acara keluarga',
            'status_approval' => 'Pending',
        ]);

        // 1. Approve logbook
        $response = $this->actingAs($this->pembimbing)->post("/admin/logbook/{$logbook->id}/approve");
        $response->assertRedirect();
        
        $this->assertDatabaseHas('logbooks', [
            'id' => $logbook->id,
            'status_approval' => 'Approved',
        ]);
        
        $this->assertEquals(1, $this->peserta->unreadNotifications()->count());
        $this->assertEquals('Logbook Disetujui', $this->peserta->unreadNotifications()->first()->data['title']);

        // 2. Reject leave
        $response = $this->actingAs($this->pembimbing)->post("/admin/leave/{$leave->id}/reject", [
            'catatan_pembimbing' => 'Kurang lengkap buktinya'
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status_approval' => 'Rejected',
            'catatan_pembimbing' => 'Kurang lengkap buktinya',
        ]);

        $this->assertEquals(2, $this->peserta->unreadNotifications()->count());
    }
}
