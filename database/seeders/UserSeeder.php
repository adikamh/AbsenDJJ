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
                'nip' => '198001012005011001',
                'no_telepon' => '081111111111',
                'alamat' => 'Jl. Melati No. 12, Bandung',
                'status_aktif' => true,
            ]
        );

        $pembimbing2 = User::firstOrCreate(
            ['email' => 'ratna.pembimbing@absendjj.com'],
            [
                'role_id' => $roleAdmin->id,
                'nama_lengkap' => 'Ratna Sari, S.T.',
                'password' => Hash::make('password'),
                'nip' => '198502022008022002',
                'no_telepon' => '082222222222',
                'alamat' => 'Jl. Dahlia No. 34, Jakarta',
                'status_aktif' => true,
            ]
        );

        $pembimbing3 = User::firstOrCreate(
            ['email' => 'budi.pembimbing@absendjj.com'],
            [
                'role_id' => $roleAdmin->id,
                'nama_lengkap' => 'Budi Setiawan, S.Kom.',
                'password' => Hash::make('password'),
                'nip' => '199003032015031003',
                'no_telepon' => '083333333333',
                'alamat' => 'Jl. Kenanga No. 56, Surabaya',
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
            $cities = ['Bandung', 'Jakarta', 'Surabaya', 'Semarang'];

            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'role_id' => $rolePeserta->id,
                    'instansi_id' => $instansi->id,
                    'pembimbing_id' => $pembimbing->id,
                    'nama_lengkap' => $data['nama_lengkap'],
                    'password' => Hash::make('password'),
                    'nip' => '2026000' . ($index + 1),
                    'no_telepon' => '0812345678' . $index,
                    'alamat' => 'Jl. Flamboyan No. ' . ($index + 1) . ', Kota ' . $cities[$index % 4],
                    'no_darurat_1' => '089876543' . $index,
                    'hubungan_darurat_1' => 'Orang Tua',
                    'no_darurat_2' => '089987654' . $index,
                    'hubungan_darurat_2' => 'Saudara',
                    'status_aktif' => true,
                ]
            );
        }

        // Ensure user_code is generated for all users if not already set
        $generator = app(\App\Services\UniqueCodeGenerator::class);
        $usersWithoutCode = User::whereNull('user_code')->orWhere('user_code', '')->get();
        foreach ($usersWithoutCode as $u) {
            $u->updateQuietly(['user_code' => $generator->generateUserCode($u->id)]);
        }
    }
}
