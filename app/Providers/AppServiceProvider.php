<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        DB::listen(function ($query) {
            // Abaikan query internal sesi dan cache agar log bersih
            if (str_contains($query->sql, 'sessions') || str_contains($query->sql, 'cache')) {
                return;
            }

            Log::info(sprintf('[SQL AUDIT] (%.2f ms) %s', $query->time, $query->sql));
        });
    }
}