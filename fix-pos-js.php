<?php
$file = "resources/views/pos/index.blade.php";
if (!file_exists($file)) {
    // دەتوانێت لەو ڕێگەیەی کە هەیەتی فایلەکە بدۆزێتەوە، با سەرەتا فایلی پۆسەکە لە مۆدیولەکان بپشکنین
}
// با لە ڕێگەی کۆنتڕۆڵەری POSـەوە یان راستەوخۆ لە ڤیوزەکە چاکی بکەین
$reg = Illuminate\Support\Facades\DB::table('registers')->first();
$regId = $reg ? $reg->id : 'e847654e-19a3-4903-b72e-9de8e90d555b';

$viewPath = base_path('resources/views/pos.blade.php');
if (file_exists($viewPath)) {
    $content = file_get_contents($viewPath);
    $oldJs = 'body: JSON.stringify({ opening_cash: parseFloat(val) || 0 })';
    $newJs = 'body: JSON.stringify({ opening_cash: parseFloat(val) || 0, register_id: "' . $regId . '" })';
    
    if (str_contains($content, $oldJs)) {
        $content = str_replace($oldJs, $newJs, $content);
        file_put_contents($viewPath, $content);
        echo "✅ SUCCESS: register_id injected successfully into POS view!\n";
    } else {
        echo "⚠️ Target JS line not found exactly, let's check alternate paths.\n";
    }
} else {
    echo "❌ pos.blade.php not found in resources/views/\n";
}
