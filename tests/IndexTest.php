<?php namespace Tests;

use TestCase;

class IndexTest extends TestCase {
	
	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testIndex()
	{
		$response = $this->call('GET', '/');
		
		$this->assertEquals(200, $response->getStatusCode());
	}
}
