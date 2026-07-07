<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\LeaveRequest;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolePeserta = Role::where('nama_role', 'peserta')->first();
        $interns = User::where('role_id', $rolePeserta->id)->get();

        // Generate leave requests for 3 random interns
        $selectedInterns = $interns->random(min(3, $interns->count()));

        $reasons = [
            'Sakit' => [
                'Demam tinggi dan disarankan istirahat oleh dokter selama 2 hari.',
                'Sakit gigi parah dan harus kontrol ke dokter gigi.',
            ],
            'Izin' => [
                'Menghadiri wisuda kakak kandung di luar kota.',
                'Ada keperluan keluarga mendesak yang tidak bisa ditinggalkan.',
            ],
        ];

        foreach ($selectedInterns as $index => $intern) {
            $jenis = $index % 2 === 0 ? 'Sakit' : 'Izin';
            $alasanList = $reasons[$jenis];
            $alasan = $alasanList[array_rand($alasanList)];
            
            $startDate = Carbon::now()->addDays($index + 2);
            $endDate = (clone $startDate)->addDays(1);

            $status = $index === 0 ? 'Approved' : ($index === 1 ? 'Pending' : 'Rejected');
            $catatan = null;

            if ($status === 'Approved') {
                $catatan = 'Diizinkan, pastikan untuk beristirahat dengan baik dan hubungi pembimbing jika ada kendala.';
            } elseif ($status === 'Rejected') {
                $catatan = 'Keperluan kurang mendesak, silakan diskusikan kembali jadwal Anda dengan pembimbing.';
            }

            LeaveRequest::create([
                'user_id' => $intern->id,
                'tanggal_mulai' => $startDate->format('Y-m-d'),
                'tanggal_selesai' => $endDate->format('Y-m-d'),
                'jenis' => $jenis,
                'alasan' => $alasan,
                'file_bukti' => $jenis === 'Sakit' ? 'uploads/bukti/surat_sakit_' . $intern->id . '.pdf' : null,
                'status_approval' => $status,
                'catatan_pembimbing' => $catatan,
            ]);
        }
    }
}
