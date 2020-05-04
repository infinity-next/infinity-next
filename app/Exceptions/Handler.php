<?php

namespace App\Exceptions;

use App\Exceptions\TorClearnet;
use App\Mail\SiteError;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Exception;
use ErrorException;
use Mail;
use Response;
use Request;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        TorClearnet::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $e
     */
    public function report(Throwable $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $e
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        $errorView = false;
        $errorEmail = true;

        switch (get_class($e)) {
            case TorClearnet::class:
                $errorView = 'errors.403_tor_clearnet';
                $errorEmail = false;
                break;

            case Swift_TransportException::class:
            case PDOException::class:
                $errorView = 'errors.500_config';
                $errorEmail = true;
                break;

            case ErrorException::class:
            case Symfony\Component\Debug\Exception\FatalThrowableError::class:
                $errorView = 'errors.500';
                $errorEmail = true;
                break;

            case Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class:
                return abort(400);

            case Predis\Connection\ConnectionException::class:
                $errorView = 'errors.500_predis';
                $errorEmail = true;
                break;
        }

        if (config('app.debug', false)) {
            $errorView = false;
        }

        $errorEmail = $errorEmail && env('MAIL_ADDR_ADMIN', false) && env('MAIL_ADMIN_SERVER_ERRORS', false);

        if ($errorEmail) {
            Mail::queue(new SiteError($e, Request::url()));
        }

        if ($errorView) {
            // Duplicating logic in $errorEmail because output is completely
            // diffrent without app.debug enabled. I always want a stack trace
            // in my emails!
            $SymfonyDisplayer = new HtmlErrorRenderer(config('app.debug'));
            $FlattenException = isset($FlattenException) ? $FlattenException : FlattenException::createFromThrowable($e);

            $SymfonyCss = $SymfonyDisplayer->getStylesheet($FlattenException);
            $SymfonyHtml = $SymfonyDisplayer->getBody($FlattenException);

            $response = response()->view($errorView, [
                'exception' => $e,
                'error_class' => get_class($e),
                'error_css' => $SymfonyCss,
                'error_html' => $SymfonyHtml,
            ], 500);

            return $this->toIlluminateResponse($response, $e);
        }

        return parent::render($request, $e);
    }
}
