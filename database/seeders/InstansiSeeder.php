<?php

namespace Database\Seeders;

use App\Models\Instansi;
use Illuminate\Database\Seeder;

class InstansiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nama_instansi' => 'Institut Teknologi Nasional (ITENAS)', 'jenis' => 'Universitas'],
            ['nama_instansi' => 'Universitas Pendidikan Indonesia (UPI)', 'jenis' => 'Universitas'],
            ['nama_instansi' => 'Universitas Komputer Indonesia (UNIKOM)', 'jenis' => 'Universitas'],
            ['nama_instansi' => 'SMK Negeri 1 Bandung', 'jenis' => 'SMK'],
            ['nama_instansi' => 'SMK Negeri 4 Bandung', 'jenis' => 'SMK'],
        ];

        foreach ($data as $item) {
            Instansi::firstOrCreate(
                ['nama_instansi' => $item['nama_instansi']],
                ['jenis' => $item['jenis']]
            );
        }
    }
}
