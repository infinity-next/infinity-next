<?php

// use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase {
	
	/**
	 * Test /cp/auth/login/ response.
	 *
	 * @return void
	 */
	public function testLogin()
	{
		$response = $this->call('GET', '/cp/auth/login/');
		
		$this->assertEquals(200, $response->getStatusCode());
	}
	
	/**
	 * Test /cp/auth/logout/ redirect to index.
	 *
	 * @return void
	 */
	public function testLogout()
	{
		$response = $this->call('GET', '/cp/auth/logout/');
		
		$this->assertRedirectedTo('/');
	}
	
	/**
	 * Test /cp/auth/register/ response.
	 *
	 * @return void
	 */
	public function testRegister()
	{
		$response = $this->call('GET', '/cp/auth/register/');
		
		$this->assertEquals(200, $response->getStatusCode());
	}
}
