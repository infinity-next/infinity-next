<?php

namespace Tests\Unit;

use App\Board;
use App\User;
use Tests\Testcase;

class BoardTest extends TestCase
{
    public function testBoardOwner()
    {
        $user = factory(User::class)->create();
        $board = factory(Board::class)->create();

        $this->assertNotSame($user, $board->operator);
        $this->assertSame($user, $board->creator);
    }
}
