<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'admin@naturalenha.com');
        $password = (string) env('ADMIN_PASSWORD', 'NeBank!ChangeMe2026');
        $name = (string) env('ADMIN_NAME', 'Administrator');

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
            ]
        );
    }
}
