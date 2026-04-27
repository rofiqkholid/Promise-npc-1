<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userMgmt = \App\Models\NpcMenu::where('title', 'User Management')->first();

if ($userMgmt) {
    // 1. Rename existing "User List" to "All Promise Users"
    $userList = \App\Models\NpcMenu::where('title', 'User List')->first();
    if ($userList) {
        $userList->update([
            'title' => 'All Promise Users',
            'route_name' => 'master.promise-users.index',
            'order' => 2
        ]);
        echo "Renamed User List to All Promise Users.\n";
    }

    // 2. Add new "NPC Users"
    if (!\App\Models\NpcMenu::where('title', 'NPC Users')->exists()) {
        $npcUsers = \App\Models\NpcMenu::create([
            'title' => 'NPC Users',
            'route_name' => 'master.npc-users.index',
            'parent_id' => $userMgmt->id,
            'order' => 3,
            'is_active' => true
        ]);
        
        $admin = \App\Models\NpcRole::where('code', 'admin')->first();
        if ($admin) {
            $admin->menus()->attach($npcUsers->id, [
                'can_view' => true,
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true,
                'can_approve' => true
            ]);
        }
        echo "NPC Users menu added.\n";
    } else {
        echo "NPC Users menu already exists.\n";
    }
} else {
    echo "Parent not found.\n";
}
