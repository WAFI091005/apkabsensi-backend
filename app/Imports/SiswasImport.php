<?php

// app/Imports/SiswasImport.php
namespace App\Imports;

use App\Models\User;
use App\Models\Siswa;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswasImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['email']) || empty($row['nis'])) return null;

        $user = User::updateOrCreate(
            ['email' => $row['email']],
            [
                'name'     => $row['nama'] ?? 'Nama Kosong',
                'role'     => 'siswa',
                'password' => Hash::make($row['password'] ?? Str::random(10)),
            ]
        );

        Siswa::updateOrCreate(
            ['nis' => $row['nis']],
            [
                'user_id'    => $user->id,
                'nama'       => $row['nama'] ?? $user->name,
                'kelas'      => $row['kelas'] ?? '-',
                'no_hp_ortu' => $row['no_hp_ortu'] ?? null,
            ]
        );

        return $user;
    }
}
