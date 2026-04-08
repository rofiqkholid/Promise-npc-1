<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NpcDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\NpcDepartment::updateOrCreate(['name' => 'PUD'], ['full_name' => 'PUD (Painting dll)', 'is_active' => true]);
        \App\Models\NpcDepartment::updateOrCreate(['name' => 'ME'], ['full_name' => 'ME (Stamping, Assy dll)', 'is_active' => true]);
    }
}
