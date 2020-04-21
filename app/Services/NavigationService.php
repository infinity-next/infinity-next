<?php

namespace App\Services;

use App\Board;
use Cache;

class NavigationService
{
    public function cachePrimaryNav()
    {
        return Cache::rememberForever(is_hidden_service() ? 'site.nav.primary.tor' : 'site.nav.primary', function() {
            return $this->renderPrimaryNav();
        });
    }

    public function getPrimaryNavBoards()
    {
        if (site_setting('boardListShow', false) && Cache::has('site.boardlist')) {
            $popularBoards = collect();

            $popularBoardArray = Board::getBoardsForBoardlist(0, 20);
            foreach ($popularBoardArray as $popularBoard) {
                $popularBoards->push(new Board($popularBoard));
            }

            return [
                'popular_boards' => Cache::remember('site.gnav.popular_boards', now()->addHour(), function () use ($popularBoards) {
                    return $popularBoards;
                }),
                'recent_boards' => Cache::remember('site.gnav.recent_boards', now()->addMinutes(5), function () use ($popularBoards) {
                    return Board::where('posts_total', '>', 0)
                        ->whereNotNull('last_post_at')
                        ->wherePublic()
                        ->whereNotIn('board_uri', $popularBoards->pluck('board_uri'))
                        ->select('board_uri', 'title')
                        ->orderBy('last_post_at', 'desc')
                        ->take(20)
                        ->get();
                }),
            ];
        }

        return [];
    }

    public function getPrimaryNavLinks()
    {
        $nav = [
            'home' => route('site.home'),
            'boards' => route('site.boardlist'),
            'recent_posts' => route('site.overboard.catalog.all'),
            'panel' => route('panel.home'),
        ];


        if (user()->can('create', Board::class)) {
            $nav['new_board'] = route('panel.boards.create');
        }

        if (site_setting('adventureEnabled', false)) {
            $nav['adventure'] = route('panel.adventure');
        }

        return $nav;
    }

    public function renderPrimaryNav()
    {
        return view('nav.gnav', [
            'navBoards' => $this->getPrimaryNavBoards(),
            'navLinks' => $this->getPrimaryNavLinks(),
            'showBoardList' => site_setting('boardListShow', false),
        ])->render();
    }
}
