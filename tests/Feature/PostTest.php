<?php

namespace Tests\Routes\Panel;

use App\Board;
use App\Post;
use Illuminate\Support\Facades\Cache;
use Tests\Testcase;

class PostTest extends TestCase
{
    protected $board;
    protected $posts;
    protected $thread;

    protected function setUp(): void
    {
        parent::setUp();

        $this->board = factory(Board::class)->create();
        $this->thread = null;
        $this->posts = factory(Post::class, 10)->make()->each(function($post) {
            $post->board()->associate($this->board);

            if (is_null($this->thread)) {
                $post->save();
                $this->thread = $post;
            }
            else {
                $post->thread()->associate($this->thread);
                $post->save();
            }
        });
    }

    protected function tearDown(): void
    {
        $this->posts->each(function($post) {
            $post->forceDelete();
        });
        $this->board->forceDelete();

        $this->board = null;
        $this->posts = null;
        $this->thread = null;
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

    public function testReply()
    {
        $this->get($this->posts[5]->getUrl())
            ->assertOk();
    }

    public function testThread()
    {
        $this->get($this->thread->getUrl())
            ->assertOk();
    }
}
