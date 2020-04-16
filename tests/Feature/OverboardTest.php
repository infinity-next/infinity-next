<?php

namespace Tests\Feature;

use App\Board;
use App\Post;
//use Illuminate\Foundation\Testing\WithoutMiddleware;
//use Illuminate\Foundation\Testing\DatabaseMigrations;
//use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Testcase;

class OverboardTest extends TestCase
{
    protected $boards;
    protected $threads;

    protected function setUp(): void
    {
        parent::setUp();

        $this->boards = factory(Board::class, 3)->create();
        $this->threads = factory(Post::class, 3)->make()->each(function($post) {
            $post->board()->associate($this->boards->random());
            $post->save();
        });
    }

    protected function tearDown(): void
    {
        $this->boards->each(function($post) {
            $post->forceDelete();
        });
        $this->threads->each(function($post) {
            $post->forceDelete();
        });

        $this->boards = null;
        $this->threads = null;

        parent::tearDown();
    }

    public function testCatalog()
    {
        $this->get(route('site.overboard.catalog.all'))
            ->assertOk();
    }

    public function testIndex()
    {
        $this->get(route('site.overboard'))
            ->assertOk();
    }
}
