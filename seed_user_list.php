<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userMgmt = \App\Models\NpcMenu::where('title', 'User Management')->first();

if ($userMgmt) {
    if (!\App\Models\NpcMenu::where('title', 'User List')->exists()) {
        $userList = \App\Models\NpcMenu::create([
            'title' => 'User List',
            'route_name' => 'master.users.index',
            'parent_id' => $userMgmt->id,
            'order' => 2,
            'is_active' => true
        ]);
        
        $admin = \App\Models\NpcRole::where('code', 'admin')->first();
        if ($admin) {
            $admin->menus()->attach($userList->id, [
                'can_view' => true,
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true,
                'can_approve' => true
            ]);
        }
        echo "User List menu added.\n";
    } else {
        echo "User List already exists.\n";
    }
} else {
    echo "Parent not found.\n";
}
