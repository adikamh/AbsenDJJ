<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Role;
use App\Models\User;
use App\Models\Logbook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesertaLogbookTest extends TestCase
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

    public function test_peserta_can_create_logbook(): void
    {
        $response = $this->actingAs($this->peserta)->post('/peserta/logbook', [
            'tanggal' => '2026-07-10',
            'kegiatan' => 'Mengerjakan Rancangan Fitur',
            'deskripsi' => 'Detail pengerjaan fitur logbook magang.',
        ]);

        $response->assertRedirect('/peserta/logbook');
        $this->assertDatabaseHas('logbooks', [
            'user_id' => $this->peserta->id,
            'kegiatan' => 'Mengerjakan Rancangan Fitur',
            'status_approval' => 'Pending',
        ]);
    }

    public function test_peserta_can_edit_pending_logbook(): void
    {
        $logbook = Logbook::create([
            'user_id' => $this->peserta->id,
            'tanggal' => '2026-07-10',
            'kegiatan' => 'Rancangan Fitur',
            'deskripsi' => 'Detail awal',
            'status_approval' => 'Pending',
        ]);

        $response = $this->actingAs($this->peserta)->put('/peserta/logbook/' . $logbook->id, [
            'kegiatan' => 'Rancangan Fitur Update',
            'deskripsi' => 'Detail baru yang diperbarui',
        ]);

        $response->assertRedirect('/peserta/logbook');
        $this->assertDatabaseHas('logbooks', [
            'id' => $logbook->id,
            'kegiatan' => 'Rancangan Fitur Update',
            'deskripsi' => 'Detail baru yang diperbarui',
        ]);
    }

    public function test_peserta_cannot_edit_approved_logbook(): void
    {
        $logbook = Logbook::create([
            'user_id' => $this->peserta->id,
            'tanggal' => '2026-07-10',
            'kegiatan' => 'Rancangan Fitur',
            'deskripsi' => 'Detail awal',
            'status_approval' => 'Approved',
        ]);

        $response = $this->actingAs($this->peserta)->put('/peserta/logbook/' . $logbook->id, [
            'kegiatan' => 'Rancangan Fitur Update',
            'deskripsi' => 'Detail baru yang diperbarui',
        ]);

        $response->assertRedirect('/peserta/logbook');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('logbooks', [
            'id' => $logbook->id,
            'kegiatan' => 'Rancangan Fitur', // still original
        ]);
    }

    public function test_peserta_can_delete_pending_logbook(): void
    {
        $logbook = Logbook::create([
            'user_id' => $this->peserta->id,
            'tanggal' => '2026-07-10',
            'kegiatan' => 'Rancangan Fitur',
            'deskripsi' => 'Detail awal',
            'status_approval' => 'Pending',
        ]);

        $response = $this->actingAs($this->peserta)->delete('/peserta/logbook/' . $logbook->id);

        $response->assertRedirect('/peserta/logbook');
        $this->assertDatabaseMissing('logbooks', ['id' => $logbook->id]);
    }

    public function test_peserta_cannot_delete_approved_logbook(): void
    {
        $logbook = Logbook::create([
            'user_id' => $this->peserta->id,
            'tanggal' => '2026-07-10',
            'kegiatan' => 'Rancangan Fitur',
            'deskripsi' => 'Detail awal',
            'status_approval' => 'Approved',
        ]);

        $response = $this->actingAs($this->peserta)->delete('/peserta/logbook/' . $logbook->id);

        $response->assertRedirect('/peserta/logbook');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('logbooks', ['id' => $logbook->id]);
    }

    public function test_peserta_can_create_draft_logbook_with_tags(): void
    {
        $response = $this->actingAs($this->peserta)->post('/peserta/logbook', [
            'tanggal' => '2026-07-10',
            'kegiatan' => 'Rancang DB',
            'tags' => 'mysql, schema',
            'deskripsi' => 'Pekerjaan database schema.',
            'action' => 'draft',
        ]);

        $response->assertRedirect('/peserta/logbook');
        $this->assertDatabaseHas('logbooks', [
            'user_id' => $this->peserta->id,
            'kegiatan' => 'Rancang DB',
            'tags' => 'mysql, schema',
            'status_approval' => 'Draft',
        ]);
    }

    public function test_peserta_can_edit_draft_logbook_and_publish_it(): void
    {
        $logbook = Logbook::create([
            'user_id' => $this->peserta->id,
            'tanggal' => '2026-07-10',
            'kegiatan' => 'Rancang DB',
            'tags' => 'mysql',
            'deskripsi' => 'Draft description',
            'status_approval' => 'Draft',
        ]);

        $response = $this->actingAs($this->peserta)->put('/peserta/logbook/' . $logbook->id, [
            'kegiatan' => 'Rancang DB Final',
            'tags' => 'mysql, index',
            'deskripsi' => 'Final description',
            'action' => 'submit',
        ]);

        $response->assertRedirect('/peserta/logbook');
        $this->assertDatabaseHas('logbooks', [
            'id' => $logbook->id,
            'kegiatan' => 'Rancang DB Final',
            'tags' => 'mysql, index',
            'status_approval' => 'Pending', // published
        ]);
    }

    public function test_peserta_can_delete_draft_logbook(): void
    {
        $logbook = Logbook::create([
            'user_id' => $this->peserta->id,
            'tanggal' => '2026-07-10',
            'kegiatan' => 'Draft Kegiatan',
            'deskripsi' => 'Draft desc',
            'status_approval' => 'Draft',
        ]);

        $response = $this->actingAs($this->peserta)->delete('/peserta/logbook/' . $logbook->id);

        $response->assertRedirect('/peserta/logbook');
        $this->assertDatabaseMissing('logbooks', ['id' => $logbook->id]);
    }
}
