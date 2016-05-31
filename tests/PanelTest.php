<?php

// use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PanelTest extends TestCase
{
    /**
     * Test /cp/ redirect to login.
     *
     * @return void
     */
    public function testPanel()
    {
        $response = $this->call('GET', '/cp/');

        $this->assertRedirectedTo('/cp/auth/login/');
    }

    /**
     * Test /cp/donate/ response.
     *
     * @return void
     */
    public function testDonate()
    {
        $httpResponse = $this->call('GET', '/cp/donate/');
        $sslResponse  = $this->callSecure('GET', '/cp/donate/');

        $this->assertEquals(200, $httpResponse->getStatusCode());
        $this->assertEquals(200, $sslResponse->getStatusCode());

        /*
        if (env('APP_DEBUG', false))
        {
            $this->assertEquals($sslResponse->getContent(), $httpResponse->getContent());
        }
        else
        {
            $this->assertNotEquals(md5($sslResponse->getContent()), md5($httpResponse->getContent()));
        }
        */
    }

    /**
     * Test /cp/password/email/ response.
     *
     * @return void
     */
    public function testPasswordEmail()
    {
        $response = $this->call('GET', '/cp/password/email/');

        $this->assertEquals(200, $response->getStatusCode());
    }
}
