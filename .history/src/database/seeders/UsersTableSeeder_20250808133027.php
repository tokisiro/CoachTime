<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
        'name' => '佐藤 蓮',
        'email' => 35,
        'password' => 'American',
        'role' => '一般'
    ];
        DB::table('users')->insert($param);

        $param = [
        'name' => '鈴木 睦月',
        'email' => ,
        'password' => 'American',
        'role' => '一般'
    ];
        DB::table('users')->insert($param);
    }
}
