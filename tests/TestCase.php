<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{

	/**
	 * The URL to be used by the webcrawlers.
	 *
	 * @var string
	 */
	protected $baseUrl;

	/**
	 * Creates the application.
	 *
	 * @return \Illuminate\Foundation\Application
	 */
	public function createApplication()
	{
		$this->baseUrl = env('APP_URL', "localhost");


		$app = require __DIR__.'/../bootstrap/app.php';

		$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

		return $app;
	}
}
