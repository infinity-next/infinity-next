<?php

namespace Tests\Feature;

//use Illuminate\Foundation\Testing\WithoutMiddleware;
//use Illuminate\Foundation\Testing\DatabaseMigrations;
//use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Testcase;

class AuthTest extends TestCase
{
    public function testLogin()
    {
        $this->get('/cp/login')
            ->assertOk();
    }

    public function testLogout()
    {
        $this->get('/cp/logout')
            ->assertRedirect('/');
    }

    public function testRegister()
    {
        $this->get('/cp/register')
            ->assertOk();
    }
}
