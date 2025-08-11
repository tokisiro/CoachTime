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
    'name' => 'tony',
    'age' => 35,
    'nationality' => 'American'
  ];
+    DB::table('authors')->insert($param);
    }
}
