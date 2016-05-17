<?php namespace App\Exceptions;

use Exception;
use ErrorException;
use Mail;
use Response;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Debug\Exception\FlattenException as FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyDisplayer;

class Handler extends ExceptionHandler {

	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		'Symfony\Component\HttpKernel\Exception\HttpException'
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	public function report(Exception $e)
	{
		return parent::report($e);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $e
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $e)
	{
		$errorView = false;
		$errorEmail = false;

		switch (get_class($e))
		{
			case "App\Exceptions\TorClearnet" :
				$errorView = "errors.403_tor_clearnet";
				break;

			case "Swift_TransportException" :
			case "PDOException" :
				$errorView = "errors.500_config";
				$errorEmail = true;
				break;

			case "ErrorException" :
			case "Symfony\Component\Debug\Exception\FatalThrowableError" :
				$errorView = "errors.500";
				$errorEmail = true;
				break;

			case "Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException" :
				return abort(400);

			case "Predis\Connection\ConnectionException" :
				$errorView = "errors.500_predis";
				$errorEmail = true;
				break;

			default :
				$errorView = false;
				break;
		}

		if (env('APP_DEBUG', false))
		{
			$errorView = false;
		}

		$errorEmail = $errorEmail && env('MAIL_ADDR_ADMIN', false) && env('MAIL_ADMIN_SERVER_ERRORS', false);

		if ($errorEmail || $errorView)
		{
			// This makes use of a Symfony error handler to make pretty traces.
			$SymfonyDisplayer = new SymfonyDisplayer(config('app.debug'));
			$FlattenException = isset($FlattenException) ? $FlattenException : FlattenException::create($e);

			$SymfonyCss       = $SymfonyDisplayer->getStylesheet($FlattenException);
			$SymfonyHtml      = $SymfonyDisplayer->getContent($FlattenException);
		}

		if ($errorEmail)
		{
			$data = [
				'exception'   => $e,
				'error_class' => get_class($e),
				'error_css'   => $SymfonyCss,
				'error_html'  => $SymfonyHtml,
			];

			Mail::send('emails.error', $data, function($message)
			{
				$message->to(env('MAIL_ADDR_ADMIN', false), env('SITE_NAME', 'Infinity Next') . " Webaster");
				$message->subject(env('SITE_NAME', 'Infinity Next') . " Error");
			});
		}

		if ($errorView)
		{
			$response = response()->view($errorView, [
				'exception'   => $e,
				'error_class' => get_class($e),
				'error_css'   => $SymfonyCss,
				'error_html'  => $SymfonyHtml,
			], 500);

			return $this->toIlluminateResponse($response, $e);
		}

		return parent::render($request, $e);
	}
}
