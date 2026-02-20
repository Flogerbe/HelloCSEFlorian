<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $previous = set_error_handler(null);

        set_error_handler(
            static function (int $errno, string $errstr, string $errfile, int $errline) use ($previous): bool {
                if (
                    str_contains($errfile, 'sebastian' . DIRECTORY_SEPARATOR . 'version')
                    && str_contains($errstr, 'proc_open')
                ) {
                    return true;
                }

                if ($previous !== null) {
                    return (bool) call_user_func($previous, $errno, $errstr, $errfile, $errline);
                }

                return false;
            },
            E_WARNING
        );
    }

    public function boot(): void
    {
        // Aucun bootstrap nécessaire pour cette application.
    }
}
