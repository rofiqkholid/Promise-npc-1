<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userMgmt = \App\Models\NpcMenu::where('title', 'User Management')->first();
if (!$userMgmt) {
    $userMgmt = \App\Models\NpcMenu::create([
        'title' => 'User Management',
        'route_name' => '#',
        'icon' => 'fa-solid fa-users-gear',
        'order' => 4,
        'is_active' => true
    ]);
}

$roleMenu = null;
if (!\App\Models\NpcMenu::where('title', 'Role Management')->exists()) {
    $roleMenu = \App\Models\NpcMenu::create([
        'title' => 'Role Management',
        'route_name' => 'master.roles.index',
        'parent_id' => $userMgmt->id,
        'order' => 1,
        'is_active' => true
    ]);
} else {
    $roleMenu = \App\Models\NpcMenu::where('title', 'Role Management')->first();
}

$admin = \App\Models\NpcRole::where('code', 'admin')->first();
if ($admin) {
    $admin->menus()->syncWithoutDetaching([
        $userMgmt->id => ['can_view' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => true],
        $roleMenu->id => ['can_view' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => true]
    ]);
}

echo "Menus added and assigned to admin.\n";

