<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    { 
        
        //admin account   1
        $admin = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => '$Admin@admin1234$', //will be hashed because of using $casts ['password' => 'hashed',]
        ]);
        $admin->assignRole('admin');
        //manager account   2
        $mona = User::factory()->create([
            'name' => 'mona',
            'email' => 'mona@gmail.com',
            'password' => '$MONA@alrayes1234$',
        ]);
        $mona->assignRole('manager');
       //3
        $somar = User::factory()->create([
            'name' => 'somar',
            'email' => 'somar@gmail.com',
            'password' => '$Somar@Kesen1234$',
        ]);
        $somar->assignRole('manager');

        //deveploder accounts  4
        $hani = User::factory()->create([
            'name' => 'hani',
            'email' => 'hani@gmail.com',
            'password' => '$Hani@hani1234$',
        ]);
        $hani->assignRole('developer');
        //5
        $ayham = User::factory()->create([
            'name' => 'ayham',
            'email' => 'ayham@gmail.com',
            'password' => '$AYHAM@ibrahem1234$',
        ]);
        $ayham->assignRole('developer');
        //6
        $yosef = User::factory()->create([
            'name' => 'yosef',
            'email' => 'yosef@gmail.com',
            'password' => '$YOSEF@saleh1234$',
        ]);
        $yosef->assignRole('developer');

    }
    }

