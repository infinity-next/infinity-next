<?php

namespace Tests\Feature;

use App\Board;
use App\Post;
use APp\Role;
//use Illuminate\Foundation\Testing\WithoutMiddleware;
//use Illuminate\Foundation\Testing\DatabaseMigrations;
//use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Testcase;

/**
 * Board moderator action tests.
 *
 * @category   Tests
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2020 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @see        \Illuminate\Auth\GenericUser
 *
 * @since      0.6.0
 */
class ModTest extends TestCase
{
    protected $board;
    protected $posts;
    protected $admin;
    protected $owner;
    protected $janny;

    protected function setUp(): void
    {
        parent::setUp();

        $this->board = factory(Board::class)->create();
        $this->posts = factory(Post::class, 10)->make()->each(function($post) {
            $post->board()->associate($this->board);
            $post->save();
        });
        $this->admin = Role::where('role_id', Role::ID_ADMIN)->first()->users()->first();
        $this->owner = $this->board->operator;
    }

    protected function tearDown(): void
    {
        $this->posts->each(function($post) {
            $post->forceDelete();
        });
        $this->board->forceDelete();

        unset($this->board, $this->posts);
    }

    public function testReport()
    {
        $post = $this->posts->get(0);

        $this->get($post->getModUrl('report'))
            ->assertOk();
        $this->actingAs($this->owner)->get($post->getModUrl('report'))
            ->assertOk();
        $this->actingAs($this->admin)->get($post->getModUrl('report'))
            ->assertOk();

        $this->get($post->getModUrl('report.global'))
            ->assertOk();
        $this->actingAs($this->owner)->get($post->getModUrl('report.global'))
            ->assertOk();
        $this->actingAs($this->admin)->get($post->getModUrl('report.global'))
            ->assertOk();
    }

    public function testHistory()
    {
        $post = $this->posts->get(1);

        $this->get($post->getModUrl('history'))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('history'))
            ->assertOk();
        $this->actingAs($this->admin)->get($post->getModUrl('history'))
            ->assertOk();

        $this->get($post->getModUrl('history.global'))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('history.global'))
            ->assertStatus(403);
        $this->actingAs($this->admin)->get($post->getModUrl('history.global'))
            ->assertOk();
    }

    public function testEdit()
    {
        $post = $this->posts->get(2);

        $this->get($post->getModUrl('edit'))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('edit'))
            ->assertStatus(403);
        $this->actingAs($this->admin)->get($post->getModUrl('edit'))
            ->assertOk();
    }

    public function testToggles()
    {

        $post = $this->posts->get(3);

        $this->get($post->getModUrl('sticky'))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('sticky'))
            ->assertOk();
        $this->actingAs($this->admin)->get($post->getModUrl('sticky'))
            ->assertOk();

        $this->get($post->getModUrl('unsticky'))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('unsticky'))
            ->assertOk();
        $this->actingAs($this->admin)->get($post->getModUrl('unsticky'))
            ->assertOk();

        $this->get($post->getModUrl('lock'))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('lock'))
            ->assertOk();
        $this->actingAs($this->admin)->get($post->getModUrl('lock'))
            ->assertOk();

        $this->get($post->getModUrl('unlock'))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('unlock'))
            ->assertOk();
        $this->actingAs($this->admin)->get($post->getModUrl('unlock'))
            ->assertOk();

        $this->get($post->getModUrl('bumplock'))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('bumplock'))
            ->assertOk();
        $this->actingAs($this->admin)->get($post->getModUrl('bumplock'))
            ->assertOk();

        $this->get($post->getModUrl('unbumplock'))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('unbumplock'))
            ->assertOk();
        $this->actingAs($this->admin)->get($post->getModUrl('unbumplock'))
            ->assertOk();
    }

    public function testLocalMod()
    {
        $post = $this->posts->get(4);

        $this->get($post->getModUrl('mod', [ 'ban' => 1, ]))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('mod', [ 'ban' => 1, ]))
            ->assertOk();
        $this->actingAs($this->admin)->get($post->getModUrl('mod', [ 'ban' => 1, ]))
            ->assertOk();

        $this->get($post->getModUrl('mod', [ 'delete' => 1, ]))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('mod', [ 'delete' => 1, ]))
            ->assertOk();
        $this->actingAs($this->admin)->get($post->getModUrl('mod', [ 'delete' => 1, ]))
            ->assertOk();

        $this->get($post->getModUrl('mod', [ 'ban' => 1, 'delete' => 1, ]))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('mod', [ 'ban' => 1, 'delete' => 1, ]))
            ->assertOk();
        $this->actingAs($this->admin)->get($post->getModUrl('mod', [ 'ban' => 1, 'delete' => 1, ]))
            ->assertOk();

        $this->get($get->getModUrl('mod', [ 'ban' => 1, 'delete' => 1, 'scope' => "all" ]))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('mod', [ 'ban' => 1, 'delete' => 1, 'scope' => "all" ]))
            ->assertOk();
        $this->actingAs($this->admin)->get($post->getModUrl('mod', [ 'ban' => 1, 'delete' => 1, 'scope' => "all" ]))
            ->assertOk();

        $this->get($post->getModUrl('mod', [ 'ban' => 1, 'delete' => 0, 'scope' => "all" ]))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('mod', [ 'ban' => 1, 'delete' => 0, 'scope' => "all" ]))
            ->assertOk();
        $this->actingAs($this->admin)->get($post->getModUrl('mod', [ 'ban' => 1, 'delete' => 0, 'scope' => "all" ]))
            ->assertOk();
    }

    public function testGlobalMod()
    {
        $post = $this->posts->get(5);

        $this->get($post->getModUrl('mod', [ 'ban' => 1, 'delete' => 0, 'scope' => "global" ]))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('mod', [ 'ban' => 1, 'delete' => 0, 'scope' => "global" ]))
            ->assertStatus(403);
        $this->actingAs($this->admin)->get($post->getModUrl('mod', [ 'ban' => 1, 'delete' => 0, 'scope' => "global" ]))
            ->assertOk();

        $this->get($post->getModUrl('mod', [ 'ban' => 0, 'delete' => 1, 'scope' => "global" ]))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('mod', [ 'ban' => 0, 'delete' => 1, 'scope' => "global" ]))
            ->assertStatus(403);
        $this->actingAs($this->admin)->get($post->getModUrl('mod', [ 'ban' => 0, 'delete' => 1, 'scope' => "global" ]))
            ->assertOk();

        $this->get($post->getModUrl('mod', [ 'ban' => 1, 'delete' => 1, 'scope' => "global" ]))
            ->assertStatus(403);
        $this->actingAs($this->owner)->get($post->getModUrl('mod', [ 'ban' => 1, 'delete' => 1, 'scope' => "global" ]))
            ->assertStatus(403);
        $this->actingAs($this->admin)->get($post->getModUrl('mod', [ 'ban' => 1, 'delete' => 1, 'scope' => "global" ]))
            ->assertOk();
    }
}
