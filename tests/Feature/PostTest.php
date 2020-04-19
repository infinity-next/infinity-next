<?php

namespace Tests\Feature;

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
        $this->posts = factory(Post::class, 10)->make();

        foreach ($this->posts as $post) {
            $post->board_uri = $this->board->board_uri;

            if (is_null($this->thread)) {
                $this->thread = $post;
            }
            else {
                $post->reply_to = $this->thread->post_id;
            }

            $post->save();
        }
    }

    protected function tearDown(): void
    {
        $this->posts->each(function($post) {
            $post->forceDelete();
        });
        $this->board->forceDelete();

        unset($this->board, $this->posts, $this->thread);
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
