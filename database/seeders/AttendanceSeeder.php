<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolePeserta = Role::where('nama_role', 'peserta')->first();
        $interns = User::where('role_id', $rolePeserta->id)->get();

        // High probability of Hadir, with occasionally Terlambat, Sakit, Izin, etc.
        $statuses = ['Hadir', 'Hadir', 'Hadir', 'Hadir', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Tanpa Keterangan'];

        // Seeding attendance for the last 14 days (excluding weekends)
        for ($i = 14; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($interns as $intern) {
                $status = $statuses[array_rand($statuses)];

                $attendanceData = [
                    'user_id' => $intern->id,
                    'tanggal' => $date->format('Y-m-d'),
                    'status' => $status,
                ];

                if ($status === 'Hadir') {
                    $attendanceData['jam_masuk'] = '07:55:00';
                    $attendanceData['jam_pulang'] = '16:05:00';
                    $attendanceData['koordinat_masuk'] = '-6.8988, 107.6358';
                    $attendanceData['koordinat_pulang'] = '-6.8989, 107.6359';
                    $attendanceData['foto_masuk'] = 'uploads/selfie/masuk_' . $intern->id . '_' . $date->format('Ymd') . '.jpg';
                    $attendanceData['foto_pulang'] = 'uploads/selfie/pulang_' . $intern->id . '_' . $date->format('Ymd') . '.jpg';
                } elseif ($status === 'Terlambat') {
                    $attendanceData['jam_masuk'] = '08:35:00'; // Late check-in
                    $attendanceData['jam_pulang'] = '16:00:00';
                    $attendanceData['koordinat_masuk'] = '-6.8988, 107.6358';
                    $attendanceData['koordinat_pulang'] = '-6.8989, 107.6359';
                    $attendanceData['foto_masuk'] = 'uploads/selfie/masuk_' . $intern->id . '_' . $date->format('Ymd') . '.jpg';
                    $attendanceData['foto_pulang'] = 'uploads/selfie/pulang_' . $intern->id . '_' . $date->format('Ymd') . '.jpg';
                } else {
                    // Izin, Sakit, Tanpa Keterangan
                    $attendanceData['jam_masuk'] = null;
                    $attendanceData['jam_pulang'] = null;
                    $attendanceData['koordinat_masuk'] = null;
                    $attendanceData['koordinat_pulang'] = null;
                    $attendanceData['foto_masuk'] = null;
                    $attendanceData['foto_pulang'] = null;
                }

                Attendance::updateOrCreate(
                    [
                        'user_id' => $intern->id,
                        'tanggal' => $date->format('Y-m-d'),
                    ],
                    $attendanceData
                );
            }
        }
    }
}
