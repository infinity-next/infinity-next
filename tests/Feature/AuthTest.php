<?php

namespace Tests\Routes\Panel;

use Tests\Testcase;
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
        $this->get('/cp/login')
            ->assertOk();
    }

    /**
     * Test /cp/auth/logout/ redirect to index.
     *
     * @return void
     */
    public function testLogout()
    {
        $this->get('/cp/logout')
            ->assertRedirect('/');
    }

    /**
     * Test /cp/auth/register/ response.
     *
     * @return void
     */
    public function testRegister()
    {
        $this->get('/cp/register')
            ->assertOk();
    }
}
