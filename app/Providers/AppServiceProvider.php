<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

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
        $this->loadApiResponseMacros();
    }

    /**
     * Load API response macros for success and error responses.
     */
    protected function loadApiResponseMacros(): void
    {
        // Success macro
        Response::macro('success', function ($data = null, ?string $message = null, int $code = 200, array $extra = []) {
            $response = [
                'data' => $data,
                'message' => $message,
                'code' => $code,
                'success' => true,
                ...$extra,
            ];

            return Response::json($response, $code);
        });

        // Error macro
        Response::macro('error', function ($data = null, ?string $message = null, int $code = 400, array $extra = []) {
            $response = [
                'data' => $data,
                'message' => $message,
                'code' => $code,
                'success' => false,
                ...$extra,
            ];

            return Response::json($response, $code);
        });
    }
}
