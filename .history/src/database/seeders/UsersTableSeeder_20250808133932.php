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
        'email' => sample01@gmail.com,
        'password' => 'sample0',
        'role' => '一般'
    ];
        DB::table('users')->insert($param);

        $param = [
        'name' => '鈴木 睦月',
        'email' => 35,
        'password' => 'American',
        'role' => '一般'
    ];
        DB::table('users')->insert($param);

        $param = [
        'name' => '高橋 奏',
        'email' => 35,
        'password' => 'American',
        'role' => '一般'
    ];
        DB::table('users')->insert($param);

        $param = [
        'name' => '佐久間 莉子',
        'email' => 35,
        'password' => 'American',
        'role' => '一般'
    ];
        DB::table('users')->insert($param);

        $param = [
        'name' => '向井 照',
        'email' => 35,
        'password' => 'American',
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
