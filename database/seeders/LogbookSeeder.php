<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Logbook;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LogbookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolePeserta = Role::where('nama_role', 'peserta')->first();
        $interns = User::where('role_id', $rolePeserta->id)->get();

        $kegiatanTemplates = [
            'Analisis Data Geometris Jalan',
            'Survei Kondisi Perkerasan Jalan',
            'Penginputan Data Jembatan ke Sistem',
            'Penyusunan Laporan Harian Proyek',
            'Rapat Evaluasi Bersama Tim Pembimbing',
            'Pemetaan Kerusakan Jalan Nasional',
            'Uji Lab Sampel Aspal',
            'Studi Dokumen Gambar Teknis Jembatan',
        ];

        $deskripsiTemplates = [
            'Melakukan perhitungan kelengkapan data geometris jalan raya lintas provinsi sektor selatan.',
            'Membantu tim survei mengumpulkan data International Roughness Index (IRI) jalan raya kota.',
            'Melakukan entri data atribut jembatan (bentang, lebar, kondisi fondasi) ke dalam database aplikasi AbsenDJJ.',
            'Menyusun log progres pengerjaan survei lapangan mingguan beserta dokumentasi foto pendukung.',
            'Mendiskusikan hasil temuan lapangan mengenai retak buaya di kilometer 12 dan alternatif solusinya.',
            'Menggambar peta sebaran lubang jalan menggunakan aplikasi GIS berdasarkan titik koordinat survei.',
            'Melakukan pengujian penetrasi aspal di laboratorium untuk menentukan kualitas campuran beraspal panas.',
            'Mempelajari detail tulangan beton jembatan girder pasca-tegang untuk perbaikan bagian gelagar.',
        ];

        $approvals = ['Approved', 'Approved', 'Approved', 'Pending', 'Rejected'];
        $catatanCat = [
            'Approved' => ['Laporan sangat detail dan terstruktur. Lanjutkan.', 'Bagus sekali, data yang diinput sudah lengkap.', 'Pekerjaan sesuai dengan target harian.'],
            'Rejected' => ['Tolong deskripsinya diperjelas lagi, sebutkan sektor jalannya.', 'Data koordinat masih kurang akurat. Tolong direvisi.', 'Laporan terlalu singkat, mohon jabarkan pengerjaan Anda.'],
            'Pending' => [null],
        ];

        // Seed logbooks for any date that has attendance record
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            // Only seed if present (Hadir or Terlambat)
            if (!in_array($attendance->status, ['Hadir', 'Terlambat'])) {
                continue;
            }

            $idx = array_rand($kegiatanTemplates);
            $status = $approvals[array_rand($approvals)];
            
            $catatan = null;
            if ($status !== 'Pending') {
                $opts = $catatanCat[$status];
                $catatan = $opts[array_rand($opts)];
            }

            Logbook::updateOrCreate(
                [
                    'user_id' => $attendance->user_id,
                    'tanggal' => $attendance->tanggal->format('Y-m-d'),
                ],
                [
                    'kegiatan' => $kegiatanTemplates[$idx],
                    'deskripsi' => $deskripsiTemplates[$idx],
                    'status_approval' => $status,
                    'catatan_pembimbing' => $catatan,
                ]
            );
        }
    }
}
