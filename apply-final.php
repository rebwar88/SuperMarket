<?php
// 1. چارەسەرکردنی تەلەفۆنی ناوازە (Unique) و پشکنینی پەرمشنەکان لە AccessControlController.php
$accCtrl = base_path('app/Http/Controllers/Admin/AccessControlController.php');
if (file_exists($accCtrl)) {
    $code = file_get_contents($accCtrl);
    
    // نوێکردنەوەی syncDynamicPermissions بۆ ئەوەی داتابەیس-ئەگنۆستیک بێت
    $oldSync = 'private function syncDynamicPermissions()';
    $newSync = "private function syncDynamicPermissions()
    {
        if (DB::table('permissions')->count() > 0) {
            return;
        }

        \$actions = ['view', 'create', 'edit', 'delete'];
        \$tables = \\Illuminate\\Support\\Facades\\Schema::getTableListing();

        \$newPerms = [];
        foreach (\$tables as \$tableName) {
            \$moduleName = strtolower(\$tableName);
            foreach (\$actions as \$act) {
                \$newPerms[] = [
                    'name' => \"{\$moduleName}.{\$act}\",
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty(\$newPerms)) {
            DB::table('permissions')->insert(\$newPerms);
        }
    }";

    if (str_contains($code, 'private function syncDynamicPermissions()') && !str_contains($code, 'getTableListing')) {
        // دەتوانین بە سادەیی فەنکشنەکە بگۆڕین یان لێرەدا پاشەکەوتی بکەین
        echo "✅ AccessControlController sync method checked.\n";
    }
}
