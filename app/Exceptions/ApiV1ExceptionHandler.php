<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Throwable;

class ApiV1ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Throwable $e)
    {
        return $this->handleApiException($request, $e);
    }

    /**
     * Handle API exception and return a custom JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    protected function handleApiException($request, Throwable $exception): JsonResponse
    {
        $statusCode = $this->getStatusCode($exception);
        $message = $this->getMessage($exception);
        $exceptionClass = get_class($exception);
        $data = [];
        switch ($exceptionClass) {
            case 'Illuminate\\Auth\\AuthenticationException':
                $statusCode = 401;
                $message = __('Unauthenticated.');
                $data = [];
                break;

            case 'Illuminate\\Validation\\ValidationException':
                $statusCode = 422;
                $message = __('Validation Error');
                $data = $exception->validator->errors();
                break;

            case 'Illuminate\\Database\\Eloquent\\ModelNotFoundException':
                $statusCode = 404;
                $message = $message ?: __('Resource not found.');
                $data = [];
                break;

            case 'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException':
                $statusCode = 404;
                $message = $message ?: __('Not Found.');
                $data = [];
                break;
            case 'Symfony\\Component\\HttpKernel\\Exception\\HttpException':
                $statusCode = $exception->getStatusCode();
                if ($statusCode == 403) {
                    $message = $message ?: __('Forbidden.');
                    $data = [];
                    break;
                }
            default:
                $data = [
                    'exception' => $exceptionClass,
                    // 'trace' => $this->limitTrace($exception->getTrace()),
                ];
                break;
        }

        return response()->error($data, $message, $statusCode);
    }

    /**
     * Get the status code based on the exception type.
     */
    public function getStatusCode(Throwable $exception): string
    {
        // Customize status codes for specific exceptions
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return 422;
        }

        return $exception->getCode() ?: 500;
    }

    /**
     * Get the error message based on the exception type.
     */
    public function getMessage(Throwable $exception): string
    {
        // Customize messages for specific exceptions
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return $exception->validator->errors()->first();
        }

        return $exception->getMessage();
    }

    /**
     * Report or log an exception.
     */
    protected function limitTrace(array $trace): array
    {
        // Limit the trace array to 5 entries for simplicity
        return array_slice($trace, 0, 5);
    }
}
