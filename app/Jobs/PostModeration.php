<?php

namespace App\Jobs;

use App\Ban;
use App\Post;
use App\Contracts\Auth\Permittable;
use App\Events\BanWasCreated;
use App\Events\PostsWereModerated;
use App\Events\ThreadWasDeleted;
use App\Support\IP;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PostModeration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $input;
    protected $post;
    protected $user;
    protected $ip;

    protected $ban;
    protected $delete;
    protected $deleteAll;
    protected $global;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Permittable $user, IP $ip, Post $post, array $input)
    {
        $this->user = $user;
        $this->post = $post;
        $this->input = $input;

        $this->ban = ($input['ban'] ?? 0) == 1;
        $this->delete = ($input['delete'] ?? 0) > 0;
        $this->deleteAll = ($input['delete'] ?? 0) == 2;
        $this->global = $input['scope'] == '_global';

        $this->ip = $ip;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $posts = $this->preparePosts();
        $ban = null;

        if ($this->ban) {
            $ban = $this->prepareBan();
            $ban->save();
        }

        if ($this->delete) {
            foreach ($posts as $post) {
                $post->delete();
            }

            $event = new PostsWereModerated($posts);
            $event->user = $this->user;
            $event->ip = $this->ip;
            event($event);
        }
        else {
            foreach ($posts as $post) {
                $post->touch(); // triggers modified event and changes updated_at time
            }
        }
    }

    protected function prepareBan()
    {
        $banLengthStr = [];
        if ($this->input['expires_days'] > 0) {
            $banLengthStr[] = "{$this->input['expires_days']}d";
        }
        if ($this->input['expires_hours'] > 0) {
            $banLengthStr[] = "{$this->input['expires_hours']}h";
        }
        if ($this->input['expires_minutes'] > 0) {
            $banLengthStr[] = "{$this->input['expires_minutes']}m";
        }
        if (empty($banLengthStr)) {
            $banLengthStr[] = '&Oslash;';
        }
        $banLengthStr = implode(' ', $banLengthStr);

        // The CIDR is passed from our post parameters. By default, it is 32/128 for IPv4/IPv6 respectively.
        $banCidr = $this->input['ban_ip_range'];
        // This generates a range from start to finish. I.E. 192.168.1.3/22 becomes [192.168.0.0, 192.168.3.255].
        // If we just pass the CDIR into the construct, we get 192.168.1.3-129.168.3.255 for some reason.
        $banCidrRange = IP::cidr_to_range("{$this->post->author_ip}/{$banCidr}");
        // We then pass this range into the construct method.
        $banIp = new IP($banCidrRange[0], $banCidrRange[1]);

        $banModel = new Ban;
        $banModel->ban_ip_start = $banIp->getStart();
        $banModel->ban_ip_end = $banIp->getEnd();
        $banModel->seen = false;
        $banModel->created_at = $banModel->freshTimestamp();
        $banModel->updated_at = clone $banModel->created_at;
        $banModel->expires_at = clone $banModel->created_at;
        $banModel->expires_at = $banModel->expires_at->addDays($this->input['expires_days']);
        $banModel->expires_at = $banModel->expires_at->addHours($this->input['expires_hours']);
        $banModel->expires_at = $banModel->expires_at->addMinutes($this->input['expires_minutes']);
        $banModel->mod_id = $this->user->user_id;
        $banModel->post_id = $this->post->post_id;
        $banModel->ban_reason_id = null;
        $banModel->justification = $this->input['justification'] ?: "No reason given.";
        $banModel->board_uri = $this->global ? null : $this->post->board_uri;

        return $banModel;
    }

    protected function preparePosts()
    {
        // Delete all posts globally.
        if ($this->global) {
            $posts = Post::whereAuthorIP($this->post->author_ip)
                ->with('reports')
                ->get();
        }
        // Delete posts locally
        else if ($this->deleteAll) {
            $posts = Post::whereAuthorIP($this->post->author_ip)
                ->where('board_uri', $this->post->board_uri)
                ->with('reports')
                ->get();
        }
        // Delete just this posts_total
        else {
            $this->post->load('reports');
            $posts = collect([ $this->post ]);
        }

        return $posts;
    }
}
