<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\WithoutMiddleware;
//use Illuminate\Foundation\Testing\DatabaseMigrations;
//use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PanelTest extends TestCase
{
    /**
     * Test /cp/ redirect to login.
     *
     * @return void
     */
    public function testPanel()
    {
        $this->get('/cp/')
            ->assertRedirect('/cp/login');
    }

    /**
     * Test /cp/password/email/ response.
     *
     * @return void
     */
    public function testPasswordEmail()
    {
        $this->get('/cp/password/reset')
            ->assertOk();
    }
}
