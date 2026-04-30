<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NpcMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dashboard = \App\Models\NpcMenu::create([
            'title' => 'Dashboard',
            'route_name' => 'dashboard',
            'icon' => 'fa-solid fa-gauge-high',
            'order' => 1,
            'is_active' => true,
        ]);

        $transaction = \App\Models\NpcMenu::create([
            'title' => 'Transaction',
            'icon' => 'fa-solid fa-right-left',
            'order' => 2,
            'is_active' => true,
        ]);

        $transactionChildren = [
            ['title' => 'Global Tracking', 'route_name' => 'tracking.index', 'order' => 1],
            ['title' => 'Production Routing Setup', 'route_name' => 'tracking.setup', 'order' => 2],
            ['title' => 'Production Process', 'route_name' => 'tracking.production', 'order' => 3],
            ['title' => 'Quality Check (QC)', 'route_name' => 'tracking.qc', 'order' => 4],
            ['title' => 'Management Check', 'route_name' => 'tracking.mgm', 'order' => 5],
            ['title' => 'Finished Goods Stock', 'route_name' => 'tracking.stock', 'order' => 6],
            ['title' => 'Delivery History', 'route_name' => 'tracking.history', 'order' => 7],
        ];

        foreach ($transactionChildren as $child) {
            \App\Models\NpcMenu::create(array_merge($child, [
                'parent_id' => $transaction->id,
                'is_active' => true,
            ]));
        }

        $masterData = \App\Models\NpcMenu::create([
            'title' => 'Master Data',
            'icon' => 'fa-solid fa-database',
            'order' => 3,
            'is_active' => true,
        ]);

        $masterChildren = [
            ['title' => 'Internal Category Master', 'route_name' => 'master.internal-categories.index', 'order' => 1],
            ['title' => 'Customer Mapping Master', 'route_name' => 'master.customer-categories.index', 'order' => 2],
            ['title' => 'Department Master', 'route_name' => 'master.departments.index', 'order' => 3],
            ['title' => 'Process Master', 'route_name' => 'master.processes.index', 'order' => 4],
            ['title' => 'Routing per Part ID', 'route_name' => 'master.routings.index', 'order' => 5],
            ['title' => 'QE Point Master', 'route_name' => 'master.checkpoints.index', 'order' => 6],
            ['title' => 'Part Checksheet Master', 'route_name' => 'master.checksheets.index', 'order' => 7],
            ['title' => 'Delivery Group Master', 'route_name' => 'master.delivery-groups.index', 'order' => 8],
            ['title' => 'Delivery Target Master', 'route_name' => 'master.delivery-targets.index', 'order' => 9],
            ['title' => 'Event Data (PO)', 'route_name' => 'events.index', 'order' => 10],
        ];

        foreach ($masterChildren as $child) {
            \App\Models\NpcMenu::create(array_merge($child, [
                'parent_id' => $masterData->id,
                'is_active' => true,
            ]));
        }

        $userManagement = \App\Models\NpcMenu::create([
            'title' => 'User Management',
            'icon' => 'fa-solid fa-user-shield',
            'order' => 4,
            'is_active' => true,
        ]);

        $userChildren = [
            ['title' => 'All Promise Users', 'route_name' => 'master.promise-users.index', 'order' => 1],
            ['title' => 'NPC User Access', 'route_name' => 'master.npc-users.index', 'order' => 2],
            ['title' => 'NPC Roles', 'route_name' => 'master.roles.index', 'order' => 3],
            ['title' => 'NPC Menus', 'route_name' => 'master.menus.index', 'order' => 4],
        ];

        foreach ($userChildren as $child) {
            \App\Models\NpcMenu::create(array_merge($child, [
                'parent_id' => $userManagement->id,
                'is_active' => true,
            ]));
        }
    }
}
