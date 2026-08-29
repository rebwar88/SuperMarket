<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

echo "======================================================\n";
echo "       📊 Z-REPORT DIAGNOSTIC & STRUCTURE AUDIT        \n";
echo "======================================================\n\n";

// ١. پشکنینی Routeـی Z-Report
echo "--- [ 1. ROUTE INFORMATION ] ---\n";
$routes = Route::getRoutes();
$foundRoute = false;
foreach ($routes as $route) {
    if (str_contains($route->uri(), 'z-report') || str_contains($route->uri(), 'z_report') || str_contains($route->uri(), 'report')) {
        $methods = implode('|', $route->methods());
        $middleware = implode(', ', $route->gatherMiddleware());
        echo "URI: [{$methods}] {$route->uri()} -> {$route->getActionName()}\n";
        echo "Middleware: [{$middleware}]\n";
        $foundRoute = true;
    }
}
if (!$foundRoute) echo "No specific Z-report route found.\n";

// ٢. پشکنینی خشتەکانی پەیوەندیدار بە Shift، Orders، و Z-Report لە داتابەیس
echo "\n--- [ 2. DATABASE TABLES & RECORD COUNTS ] ---\n";
$targetTables = ['shifts', 'registers', 'orders', 'order_items', 'payments', 'expenses', 'z_reports', 'daily_reports'];
foreach ($targetTables as $table) {
    try {
        $count = DB::table($table)->count();
        echo "Table [{$table}]: EXISTS ({$count} records)\n";
        
        // پیشاندانی ستوونەکانی خشتەکە
        $columns = DB::select("PRAGMA table_info({$table})");
        $colNames = array_map(fn($c) => $c->name, $columns);
        echo "   Columns: " . implode(', ', array_slice($colNames, 0, 10)) . (count($colNames) > 10 ? '...' : '') . "\n";
    } catch (\Throwable $e) {
        echo "Table [{$table}]: NOT FOUND\n";
    }
}

// ٣. پشکنینی فایلی Controller
echo "\n--- [ 3. REPORT CONTROLLER CODE ] ---\n";
$controllerFiles = [
    'app/Http/Controllers/Admin/ReportController.php',
    'app/Http/Controllers/Admin/ShiftController.php',
];
foreach ($controllerFiles as $cf) {
    if (file_exists($cf)) {
        echo "Found: {$cf} (" . filesize($cf) . " bytes)\n";
        $content = file_get_contents($cf);
        // نیشاندانی ناوی مێتۆدەکان
        preg_match_all('/public function\s+([a-zA-Z0-9_]+)\s*\(/', $content, $matches);
        if (!empty($matches[1])) {
            echo "   Methods: " . implode(', ', $matches[1]) . "\n";
        }
    }
}

// ٤. پشکنینی فایلی View
echo "\n--- [ 4. BLADE VIEW FILE ] ---\n";
$viewFile = 'resources/views/admin/reports/z_report.blade.php';
if (file_exists($viewFile)) {
    echo "Found View: {$viewFile} (" . filesize($viewFile) . " bytes)\n";
    $lines = file($viewFile);
    echo "   Total lines: " . count($lines) . "\n";
    echo "   First 5 lines:\n";
    for ($i = 0; $i < min(5, count($lines)); $i++) {
        echo "     " . trim($lines[$i]) . "\n";
    }
} else {
    echo "View not found at {$viewFile}\n";
}

echo "\n======================================================\n";
