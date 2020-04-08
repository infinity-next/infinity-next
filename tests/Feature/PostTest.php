<?php

namespace Tests\Routes\Panel;

use App\Board;
use App\Post;
use Tests\Testcase;

class PostTest extends TestCase
{
    protected $board;
    protected $posts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->board = factory(Board::class)->create();
        $this->posts = factory(Post::class, 50)->make()->each(function($post) {
            $post->board()->associate($this->board);
            $post->save();
        });
    }

    public function testIndex()
    {
        $this->get($this->board->getUrl('index'))
            ->assertOk();
    }

    public function testCatalog()
    {
        $this->get($this->board->getUrl())
            ->assertOk();

        $this->get($this->board->getUrl('catalog'))
            ->assertOk();
    }

    public function testThread()
    {
        $this->get($this->posts->random()->getUrl())
            ->assertOk();
    }
}
