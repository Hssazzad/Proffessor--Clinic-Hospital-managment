<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo '<pre>';

// 1. Route check
$routes = app('router')->getRoutes();
$found = false;
foreach ($routes as $route) {
    if (str_contains($route->uri(), 'discharge/ajax')) {
        echo "✅ Route found: [" . implode('|', $route->methods()) . "] " . $route->uri() . " → " . $route->getName() . "\n";
        $found = true;
    }
}
if (!$found) {
    echo "❌ discharge/ajax route NOT found in route list!\n";
}

echo "\n";

// 2. Table check
if (\Schema::hasTable('template_discharges')) {
    echo "✅ Table template_discharges EXISTS\n";
    $cols = \Schema::getColumnListing('template_discharges');
    echo "Columns: " . implode(', ', $cols) . "\n";
    $count = \DB::table('template_discharges')->count();
    echo "Total rows: " . $count . "\n";
} else {
    echo "❌ Table template_discharges NOT FOUND!\n";
}

echo "\n";

// 3. Controller method check
$controller = new \App\Http\Controllers\TemplateController();
if (method_exists($controller, 'ajaxAddDischarge')) {
    echo "✅ Method ajaxAddDischarge EXISTS in TemplateController\n";
} else {
    echo "❌ Method ajaxAddDischarge NOT FOUND in TemplateController!\n";
}

echo '</pre>';
