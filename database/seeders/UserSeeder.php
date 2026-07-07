<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Instansi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleSuperAdmin = Role::where('nama_role', 'super_admin')->first();
        $roleAdmin = Role::where('nama_role', 'admin')->first();
        $rolePeserta = Role::where('nama_role', 'peserta')->first();

        $instansis = Instansi::all();

        // 1. Create Super Admin
        User::firstOrCreate(
            ['email' => 'superadmin@absendjj.com'],
            [
                'role_id' => $roleSuperAdmin->id,
                'nama_lengkap' => 'Super Admin AbsenDJJ',
                'password' => Hash::make('password'),
                'status_aktif' => true,
            ]
        );

        // 2. Create Field Supervisors (Admins)
        $pembimbing1 = User::firstOrCreate(
            ['email' => 'hendra.pembimbing@absendjj.com'],
            [
                'role_id' => $roleAdmin->id,
                'nama_lengkap' => 'Ir. Hendra Wijaya, M.T.',
                'password' => Hash::make('password'),
                'status_aktif' => true,
            ]
        );

        $pembimbing2 = User::firstOrCreate(
            ['email' => 'ratna.pembimbing@absendjj.com'],
            [
                'role_id' => $roleAdmin->id,
                'nama_lengkap' => 'Ratna Sari, S.T.',
                'password' => Hash::make('password'),
                'status_aktif' => true,
            ]
        );

        $pembimbing3 = User::firstOrCreate(
            ['email' => 'budi.pembimbing@absendjj.com'],
            [
                'role_id' => $roleAdmin->id,
                'nama_lengkap' => 'Budi Setiawan, S.Kom.',
                'password' => Hash::make('password'),
                'status_aktif' => true,
            ]
        );

        $pembimbingList = [$pembimbing1, $pembimbing2, $pembimbing3];

        // 3. Create Interns (Peserta)
        $pesertaData = [
            ['nama_lengkap' => 'Adit Pratama', 'email' => 'adit.peserta@absendjj.com'],
            ['nama_lengkap' => 'Bunga Citra', 'email' => 'bunga.peserta@absendjj.com'],
            ['nama_lengkap' => 'Candra Wijaya', 'email' => 'candra.peserta@absendjj.com'],
            ['nama_lengkap' => 'Dina Lestari', 'email' => 'dina.peserta@absendjj.com'],
            ['nama_lengkap' => 'Edo Setiawan', 'email' => 'edo.peserta@absendjj.com'],
            ['nama_lengkap' => 'Fitri Handayani', 'email' => 'fitri.peserta@absendjj.com'],
            ['nama_lengkap' => 'Gilang Ramadhan', 'email' => 'gilang.peserta@absendjj.com'],
            ['nama_lengkap' => 'Hana Pertiwi', 'email' => 'hana.peserta@absendjj.com'],
            ['nama_lengkap' => 'Indra Lesmana', 'email' => 'indra.peserta@absendjj.com'],
            ['nama_lengkap' => 'Joko Susilo', 'email' => 'joko.peserta@absendjj.com'],
        ];

        foreach ($pesertaData as $index => $data) {
            // Assign instansi and pembimbing round-robin
            $instansi = $instansis[$index % $instansis->count()];
            $pembimbing = $pembimbingList[$index % count($pembimbingList)];

            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'role_id' => $rolePeserta->id,
                    'instansi_id' => $instansi->id,
                    'pembimbing_id' => $pembimbing->id,
                    'nama_lengkap' => $data['nama_lengkap'],
                    'password' => Hash::make('password'),
                    'status_aktif' => true,
                ]
            );
        }
    }
}
