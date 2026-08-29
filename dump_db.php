<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = Illuminate\Support\Facades\DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name ASC;");

echo "\n======================================================\n";
echo "           DATABASE TABLES & FIELDS REPORT            \n";
echo "======================================================\n\n";

foreach ($tables as $t) {
    $tableName = $t->name;
    echo "TABLE: [ " . strtoupper($tableName) . " ]\n";
    $columns = Illuminate\Support\Facades\DB::select("PRAGMA table_info('{$tableName}')");
    foreach ($columns as $col) {
        $pk = $col->pk ? " [PK]" : "";
        $null = $col->notnull ? " [NOT NULL]" : "";
        echo "   ├── {$col->name} ({$col->type}){$pk}{$null}\n";
    }
    echo "\n";
}
echo "======================================================\n";
