<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$plans = App\Models\Plan::all();
foreach ($plans as $p) {
    echo "ID: {$p->id} | Name: {$p->name} | Slug: {$p->slug} | Monthly: {$p->monthly_price} | Yearly: {$p->yearly_price} | Trial Days: {$p->trial_days}\n";
}
