<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NpcRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = \App\Models\NpcRole::create([
            'code' => 'admin',
            'name' => 'Administrator',
            'description' => 'Super User with full access'
        ]);

        $qc = \App\Models\NpcRole::create([
            'code' => 'qc',
            'name' => 'Quality Control',
            'description' => 'QC Staff'
        ]);

        $operator = \App\Models\NpcRole::create([
            'code' => 'operator',
            'name' => 'Production Operator',
            'description' => 'Production line operator'
        ]);

        // Berikan semua menu ke admin dengan full access
        $allMenus = \App\Models\NpcMenu::all();
        $adminPivot = [];
        foreach ($allMenus as $menu) {
            $adminPivot[$menu->id] = [
                'can_view' => true,
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true,
                'can_approve' => true,
            ];
        }
        $admin->menus()->sync($adminPivot);

        // Berikan akses tracking.index ke QC
        $trackingMenu = \App\Models\NpcMenu::where('route_name', 'tracking.index')->first();
        if ($trackingMenu) {
            $qc->menus()->attach($trackingMenu->id, [
                'can_view' => true,
                'can_create' => false,
                'can_update' => true,
                'can_delete' => false,
                'can_approve' => true,
            ]);
        }

        // Set user pertama sebagai admin jika ada
        $user = \App\Models\User::first();
        if ($user) {
            $user->roles()->sync([$admin->id]);
        }
    }
}
