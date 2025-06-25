<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @return void
     *
     * @throws Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @return Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ModelNotFoundException && $request->wantsJson()) {
            $model = $exception->getModel();
            $model = Str::replaceFirst('App\\Models\\', '', $model);
            $model = preg_split('/(?=[A-Z])/', $model);
            $model = array_filter($model, fn ($value) => ! is_null($value) && $value !== '');

            return response()->json(['message' => implode(' ', $model).' not found'], 404);
        }

        if ($exception instanceof AccessDeniedHttpException && $request->wantsJson()) {
            return response()->json(['message' => 'You do not have the rights to perform this action'], 403);
        }

        if ($exception instanceof AuthorizationException) {
            return response()->json(['message' => 'You do not have the rights to perform this action'], 403);
        }

        if ($exception instanceof PostTooLargeException) {
            ob_get_contents();
            ob_end_clean();

            return response()->json(['message' => 'That file is too large, max size: '.ini_get('upload_max_filesize')], 422);
        }

        if ($exception instanceof InvalidUuidStringException) {
            return response()->json(['message' => 'That uuid is not properly formatted or invalid'], 422);
        }

        return parent::render($request, $exception);
    }
}
