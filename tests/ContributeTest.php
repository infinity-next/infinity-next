<?php namespace Tests;

use TestCase;

class ContributeTest extends TestCase {
	
	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testContribute()
	{
		$response = $this->call('GET', '/contribute/');
		
		if (env('CONTRIB_ENABLED', false))
		{
			$this->assertEquals(200, $response->getStatusCode());
		}
		else
		{
			$this->assertEquals(200, $response->getStatusCode());
		}
	}
}
