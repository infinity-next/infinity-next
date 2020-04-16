<?php

namespace Tests\Feature;

use App\Board;
use App\Auth\AnonymousUser;
//use Illuminate\Foundation\Testing\WithoutMiddleware;
//use Illuminate\Foundation\Testing\DatabaseMigrations;
//use Illuminate\Foundation\Testing\DatabaseTransactions;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\SeedDatabase;
use Tests\Testcase;

class UnaccountableTest extends TestCase
{
    use SeedDatabase;

    protected $board;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new AnonymousUser;
        $this->board = factory(Board::class)->create();
    }

    protected function tearDown(): void
    {
        $this->board->forceDelete();
        unset($this->board, $this->user, $this->image);
    }

    public function testUnaccountable()
    {
        $image = UploadedFile::fake()->image('avatar.jpg', 512, 512)->size(100);

        $response = $this->actingAs($this->user)
            ->post(route('board.file.put', [
                'board' => $this->board,
                'files' => [ $image, ]
            ]));
        $response->assertStatus(422);

        //$image = UploadedFile::fake()->image('avatar.jpg', 512, 512)->size(100);
        //$anon->setAccountable(false);
    }
}
