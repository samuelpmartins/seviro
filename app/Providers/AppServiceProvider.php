<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Prevenir Lazy Loading (N+1 queries)
        Model::preventLazyLoading(!app()->isProduction());
        
        // Monitorar queries e detectar problemas
        DB::listen(function ($query) {
            static $queryCount = 0;
            $queryCount++;
            
            // Em desenvolvimento: detectar queries lentas
            if (!app()->isProduction() && $query->time > 100) {
                \Log::warning('Slow query detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time . 'ms'
                ]);
            }
            
            // Em produção: alertar se houver muitas queries
            if (app()->isProduction() && $queryCount > 500) {
                \Log::error('Too many queries in single request', [
                    'count' => $queryCount,
                    'url' => request()->fullUrl()
                ]);
            }
        });
    }
}
