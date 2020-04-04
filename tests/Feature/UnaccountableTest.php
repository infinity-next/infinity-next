<?php

namespace Tests\Routes\Panel;

use App\Board;
use App\Auth\AnonymousUser;
//use Illuminate\Foundation\Testing\WithoutMiddleware;
//use Illuminate\Foundation\Testing\DatabaseMigrations;
//use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\SeedDatabase;
use Tests\Testcase;

class UnaccountableTest extends TestCase
{
    use SeedDatabase,
        RefreshDatabase;

    public function testUnaccountable()
    {
        $anon = new AnonymousUser;
        $board = factory(Board::class)->create();

        $image = UploadedFile::fake()->image('avatar.jpg', 512, 512)->size(100);

        $response = $this->actingAs($anon)
            ->post(route('board.file.put', [
                'board' => $board,
                'files' => [ $image, ]
            ]));
        $response->assertStatus(422);

        //$image = UploadedFile::fake()->image('avatar.jpg', 512, 512)->size(100);
        //$anon->setAccountable(false);
    }
}
