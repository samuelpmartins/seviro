<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;

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
        // Best-effort apenas: putenv() aqui NÃO resolve a geração/assinatura de chaves EC
        // do Web Push (VAPID) neste build de PHP no Windows — a extensão OpenSSL lê
        // OPENSSL_CONF do processo do SO antes do PHP iniciar, não via putenv() em runtime.
        // Definir a variável no shell antes de "php artisan serve" é o que realmente funciona.
        // Sem efeito em produção (Linux).
        if (PHP_OS_FAMILY === 'Windows' && !getenv('OPENSSL_CONF')) {
            $opensslConf = PHP_BINDIR . DIRECTORY_SEPARATOR . 'extras' . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'openssl.cnf';
            if (file_exists($opensslConf)) {
                putenv('OPENSSL_CONF=' . $opensslConf);
            }
        }

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

        Paginator::useBootstrapFive();
    }
}
