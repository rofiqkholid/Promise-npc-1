<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MgmCheckpointSeeder extends Seeder
{
    public function run(): void
    {
        // Use upsert to safely insert/update without breaking FK constraints
        $items = [
            // ── History Problem ──────────────────────────────────────────
            ['point_number' => 1,  'check_item' => '[History Problem] Item 1', 'standard' => null,          'method' => 'Visual / Manual', 'is_active' => true],
            ['point_number' => 2,  'check_item' => '[History Problem] Item 2', 'standard' => null,          'method' => 'Visual / Manual', 'is_active' => true],
            ['point_number' => 3,  'check_item' => '[History Problem] Item 3', 'standard' => null,          'method' => 'Visual / Manual', 'is_active' => true],
            ['point_number' => 4,  'check_item' => '[History Problem] Item 4', 'standard' => null,          'method' => 'Visual / Manual', 'is_active' => true],

            // ── Quality ───────────────────────────────────────────────────
            ['point_number' => 5,  'check_item' => 'Material Spec',            'standard' => 'Follow Spec child part',            'method' => 'Check Document',  'is_active' => true],
            ['point_number' => 6,  'check_item' => 'Part treatment',           'standard' => 'No Treatment',                      'method' => 'Visual',          'is_active' => true],
            ['point_number' => 7,  'check_item' => 'Marking on Part',          'standard' => '53Q0',                              'method' => 'Visual',          'is_active' => true],
            ['point_number' => 8,  'check_item' => 'Hole Qty',                 'standard' => '10 Pcs',                            'method' => 'Count',           'is_active' => true],
            ['point_number' => 9,  'check_item' => 'Spot Qty',                 'standard' => '0 Point',                           'method' => 'Count',           'is_active' => true],
            ['point_number' => 10, 'check_item' => 'Spot Position',            'standard' => 'No spot',                           'method' => 'Visual',          'is_active' => true],
            ['point_number' => 11, 'check_item' => 'Nut Qty',                  'standard' => '1 Pcs',                             'method' => 'Count',           'is_active' => true],
            ['point_number' => 12, 'check_item' => 'Bolt Qty',                 'standard' => '1 Pcs',                             'method' => 'Count',           'is_active' => true],
            ['point_number' => 13, 'check_item' => 'Pin Qty',                  'standard' => '0 Pcs',                             'method' => 'Count',           'is_active' => true],
            ['point_number' => 14, 'check_item' => 'Stud Qty',                 'standard' => '3 Pcs',                             'method' => 'Count',           'is_active' => true],
            ['point_number' => 15, 'check_item' => 'Scratch',                  'standard' => 'Not Scratch',                       'method' => 'Visual',          'is_active' => true],
            ['point_number' => 16, 'check_item' => 'Crack',                    'standard' => 'Not Crack',                         'method' => 'Visual',          'is_active' => true],
            ['point_number' => 17, 'check_item' => 'Burry',                    'standard' => 'Not Burry',                         'method' => 'Visual',          'is_active' => true],
            ['point_number' => 18, 'check_item' => 'Deform',                   'standard' => 'Not Deform',                        'method' => 'Visual',          'is_active' => true],
            ['point_number' => 19, 'check_item' => 'Rusty',                    'standard' => 'Not Rusty',                         'method' => 'Visual',          'is_active' => true],

            // ── Packaging ─────────────────────────────────────────────────
            ['point_number' => 20, 'check_item' => 'Pallet Usage',             'standard' => 'Temporary (T0-T1) / Standard (T2)', 'method' => 'Visual',          'is_active' => true],
            ['point_number' => 21, 'check_item' => 'Part Quantity Order',      'standard' => 'Follow Qty PO',                     'method' => 'Count',           'is_active' => true],
            ['point_number' => 22, 'check_item' => 'Part Tag Label',           'standard' => '1 Label / Part',                    'method' => 'Visual',          'is_active' => true],
            ['point_number' => 23, 'check_item' => 'QC Marking Check',        'standard' => 'Red color (Don\'t use permanent marking)', 'method' => 'Visual',   'is_active' => true],
            ['point_number' => 24, 'check_item' => 'Harigami',                 'standard' => 'Sticking in every pallet',          'method' => 'Visual',          'is_active' => true],
        ];

        foreach ($items as &$item) {
            $item['created_at'] = now();
            $item['updated_at'] = now();
        }

        // Upsert: insert or update if point_number already exists
        DB::table('npc_master_checkpoints')->upsert(
            $items,
            ['point_number'],
            ['check_item', 'standard', 'method', 'is_active', 'updated_at']
        );

        $this->command->info('✅ MGM Checksheet: ' . count($items) . ' items seeded successfully.');
    }
}
