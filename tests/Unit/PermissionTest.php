<?php

namespace Tests\Unit;

use App\Auth\AnonymousUser;
use App\Board;
use App\User;
use Tests\SeedDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionTest extends TestCase
{
    use SeedDatabase;

    public function testUnaccountable()
    {
        $anon = new AnonymousUser;

        $this->assertTrue($anon->can('create-attachment'));

        $anon->setAccountable(false);

        $this->assertFalse($anon->can('create-attachment'));
    }
}
