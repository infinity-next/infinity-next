<?php

namespace Tests\Unit;

use App\Board;
use App\User;
use Tests\SeedDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BoardTest extends TestCase
{
    use SeedDatabase,
        RefreshDatabase;

    public function testBoardOwner()
    {
        $user = factory(User::class)->create();
        $board = factory(Board::class)->create();
        $board->load('operator', 'creator');

        $this->assertNotSame($user->user_id, $board->operator->user_id);
        $this->assertNotSame($user->user_id, $board->creator->user_id);
        $this->assertSame($board->creator->user_id, $board->operator->user_id);

        $board->operated_by = $user->user_id;
        $board->save();
        $board->load('operator', 'creator');

        $this->assertSame($user->user_id, $board->operator->user_id);
        $this->assertNotSame($user->user_id, $board->creator->user_id);
        $this->assertNotSame($board->creator->user_id, $board->operator->user_id);
    }

    public function testBoardPermission()
    {
        $user = factory(User::class)->create();
        $boards = factory(Board::class, 5)->create();

        foreach ($boards as $board) {
            $this->assertFalse($user->can('configure', $board));
        }

        foreach ($boards as $board) {
            $this->assertTrue($board->operator->can('configure', $board));
        }
    }
}
