<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->count(10)->create();

        User::factory()->admin()->create([
        'name' => '佐野 清十郎',
        'email' => 'sample07@gmail.com',
        ]);

        User::factory()->create([
            'name' => '天須',
            'email' => 'test_employee@example.com',
            'password' => Hash::make('password'), // 必要であればパスワードを設定
            'role' => 'employee', // 明示的にemployeeを設定（ファクトリーのデフォルトがemployeeなので不要な場合もあるが、明示するとより分かりやすい）
        ]);
    }
}