<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        $dbStatus = 'ok';
    } catch (\Throwable $e) {
        $dbStatus = 'error';
    }

    return response()->json(
        [
            'app' => 'OK',
            'database' => strtoupper($dbStatus),
            'time' => now()->toDateTimeString(),
        ],
        $dbStatus === 'ok' ? 200 : 500
    );
});
