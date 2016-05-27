<?php

namespace App\Exceptions;

use Exception;
use ErrorException;
use Mail;
use Response;
use Request;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Debug\Exception\FlattenException as FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyDisplayer;

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
        Symfony\Component\HttpKernel\Exception\HttpException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $e
     */
    public function report(Exception $e)
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
    public function render($request, Exception $e)
    {
        $errorView = false;
        $errorEmail = false;

        switch (get_class($e)) {
            case App\Exceptions\TorClearnet::class:
                $errorView = 'errors.403_tor_clearnet';
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

            default:
                $errorView = false;
                break;
        }

        if (config('app.debug', false)) {
            $errorView = false;
        }

        $errorEmail = $errorEmail && env('MAIL_ADDR_ADMIN', false) && env('MAIL_ADMIN_SERVER_ERRORS', false);

        if ($errorEmail) {
            // This makes use of a Symfony error handler to make pretty traces.
            $SymfonyDisplayer = new SymfonyDisplayer(true);
            $FlattenException = isset($FlattenException) ? $FlattenException : FlattenException::create($e);

            $SymfonyCss = $SymfonyDisplayer->getStylesheet($FlattenException);
            $SymfonyHtml = $SymfonyDisplayer->getContent($FlattenException);

            $data = [
                'exception' => $e,
                'error_class' => get_class($e),
                'error_css' => $SymfonyCss,
                'error_html' => $SymfonyHtml,
            ];

            Mail::send('emails.error', $data, function ($message) {
                $to = env('SITE_NAME', 'Infinity Next').' Webmaster';
                $subject = env('SITE_NAME', 'Infinity Next').' Error';
                $subject .= ' '.Request::url() ?: '';

                $message->to(env('MAIL_ADDR_ADMIN', false), $to);
                $message->subject($subject);
            });
        }

        if ($errorView) {
            // Duplicating logic in $errorEmail because output is completely
            // diffrent without app.debug enabled. I always want a stack trace
            // in my emails!
            $SymfonyDisplayer = new SymfonyDisplayer(config('app.debug'));
            $FlattenException = isset($FlattenException) ? $FlattenException : FlattenException::create($e);

            $SymfonyCss = $SymfonyDisplayer->getStylesheet($FlattenException);
            $SymfonyHtml = $SymfonyDisplayer->getContent($FlattenException);

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
