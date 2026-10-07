<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@smarttask.test');

        $admin = User::firstOrNew(['email' => $email]);

        // forceFill karena 'role' sengaja tidak ada di $fillable
        $admin->forceFill([
            'name'              => $admin->name ?: 'Administrator',
            'password'          => Hash::make(env('ADMIN_PASSWORD', 'GantiPasswordIni123!')),
            'role'              => 'admin',
            'email_verified_at' => $admin->email_verified_at ?: now(),
        ])->save();
    }
}