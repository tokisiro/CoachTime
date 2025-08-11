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
        'email' => 'sample01@gmail.com',
        'password' => 'sample01',
        'role' => 'user'
    ];
        DB::table('users')->insert($param);

        $param = [
        'name' => '鈴木 睦月',
        'email' => 'sample02@gmail.com',
        'password' => 'sample02',
        'role' => 'user'
    ];
        DB::table('users')->insert($param);

        $param = [
        'name' => '高橋 奏',
        'email' => 'sample03@gmail.com',
        'password' => 'sample03',
        'role' => 'user'
    ];
        DB::table('users')->insert($param);

        $param = [
        'name' => '佐久間 莉子',
        'email' => 'sample04@gmail.com',
        'password' => 'sample04',
        'role' => 'user'
    ];
        DB::table('users')->insert($param);

        $param = [
        'name' => '向井 照',
        'email' => 'sample05@gmail.com',
        'password' => 'sample05',
        'role' => '一般'
    ];
        DB::table('users')->insert($param);

        $param = [
        'name' => '阿部 翔太',
        'email' => 35,
        'password' => 'American',
        'role' => '一般'
    ];
        DB::table('users')->insert($param);

        $param = [
        'name' => '佐野 清十郎',
        'email' => 35,
        'password' => 'American',
        'role' => '一般'
    ];
        DB::table('users')->insert($param);
    }
}
