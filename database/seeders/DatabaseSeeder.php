<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'angga.sianipar'],
            [
                'name' => 'Angga Sianipar',
                'email' => 'angga.sianipar@sim-del.test',
                'password' => bcrypt('naanaa'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['username' => 'iss24032'],
            [
                'name' => 'Angga',
                'email' => 'iss24032@sim-del.test',
                'password' => bcrypt('Mahasiswa@2024'),
                'role' => 'mahasiswa',
            ]
        );

        User::updateOrCreate(
            ['username' => 'pustakawan'],
            [
                'name' => 'Pustakawan SIPUS-Del',
                'email' => 'pustakawan@del.ac.id',
                'password' => bcrypt('password123'),
                'role' => 'admin',
            ]
        );

        $this->call([
            KategoriSeeder::class,
            BukuSeeder::class,
            PeminjamanSeeder::class,
        ]);
    }
}
