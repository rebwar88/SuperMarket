<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Route;

echo "--- [ POS ROUTE & VIEW AUDIT ] ---\n";
$posRoute = null;
foreach (Route::getRoutes() as $r) {
    if ($r->uri() === 'pos' || str_contains($r->uri(), 'pos')) {
        echo "URI: [{$r->uri()}] -> Action: {$r->getActionName()}\n";
    }
}

$viewCandidates = [
    'resources/views/pos/index.blade.php',
    'resources/views/admin/pos/index.blade.php',
    'resources/views/pos.blade.php',
];

foreach ($viewCandidates as $vc) {
    if (file_exists($vc)) {
        echo "Found View: {$vc} (" . filesize($vc) . " bytes)\n";
        $lines = file($vc);
        echo "First 10 lines of {$vc}:\n";
        for ($i = 0; $i < min(10, count($lines)); $i++) {
            echo "   " . $lines[$i];
        }
    }
}
