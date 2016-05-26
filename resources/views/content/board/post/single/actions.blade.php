@spaceless
<div class="post-action-bar action-bar-mod">
    @section('post-actions')
    @set('postActions', false)
    @set('postHasIp', isset($details['author_ip']) && $details['author_ip'] instanceof \App\Support\IP)
    <div class="post-action-tab action-tab-actions" data-no-instant>
        <span class="post-action-label post-action-open"><span class="post-action-text">@lang('board.action.open')</span></span>

        <ul class="post-action-groups">
            <li class="post-action-group">

                <!-- Board specific content management actions -->
                <ul class="post-actions">
                    @if ($post->canReport($user))
                    @set('postActions', true)
                    <li class="post-action">
                        <a class="post-action-link action-link-report" href="{!! $post->url("report") !!}">@lang('board.action.report')</a>
                    </li>
                    @endif

                    @if ($post->canReportGlobally($user))
                    @set('postActions', true)
                    <li class="post-action">
                        <a class="post-action-link action-link-report-global" href="{!! $post->url("report/global") !!}">@lang('board.action.report_global')</a>
                    </li>
                    @endif

                    @if ($postHasIp)
                        @if ($user->canViewHistory($post))
                        @set('postActions', true)
                        <li class="post-action">
                            <a class="post-action-link action-link-report" href="{!! $board->getURL('history/' . $details['board_id']) !!}">@lang('board.action.history', [ 'board_uri' => $details['board_uri'] ])</a>
                        </li>
                        @endif

                        @if ($user->canViewGlobalHistory())
                        @set('postActions', true)
                        <li class="post-action">
                            <a class="post-action-link action-link-report" href="{!! url('cp/history/' . $details['author_ip']->toText()) !!}">@lang('board.action.history_global')</a>
                        </li>
                        @endif
                    @endif

                    @if ($post->canEdit($user))
                    @set('postActions', true)
                    <li class="post-action">
                        <a class="post-action-link action-link-edit" href="{!! $post->url("edit") !!}">@lang('board.action.edit')</a>
                    </li>
                    @endif

                    @if ($post->canSticky($user))
                    @set('postActions', true)
                    <li class="post-action">
                        @if (!$post->stickied_at)
                        <a class="post-action-link action-link-sticky" href="{!! $post->url("sticky") !!}">@lang('board.action.sticky')</a>
                        @else
                        <a class="post-action-link action-link-unsticky" href="{!! $post->url("unsticky") !!}">@lang('board.action.unsticky')</a>
                        @endif
                    </li>
                    @endif

                    @if ($post->canLock($user))
                    @set('postActions', true)
                    <li class="post-action">
                        @if (!$post->locked_at)
                        <a class="post-action-link action-link-lock" href="{!! $post->url("lock") !!}">@lang('board.action.lock')</a>
                        @else
                        <a class="post-action-link action-link-unlock" href="{!! $post->url("unlock") !!}">@lang('board.action.unlock')</a>
                        @endif
                    </li>
                    @endif

                    @if ($post->canBumplock($user))
                    @set('postActions', true)
                    <li class="post-action">
                        @if (!$post->bumplocked_at)
                        <a class="post-action-link action-link-bumplock" href="{!! $post->url("bumplock") !!}">@lang('board.action.bumplock')</a>
                        @else
                        <a class="post-action-link action-link-unbumplock" href="{!! $post->url("unbumplock") !!}">@lang('board.action.unbumplock')</a>
                        @endif
                    </li>
                    @endif

                </ul>

                <!-- Broad sweeping user & board actions -->
                <ul class="post-actions">
                    @if ($board->canBan($user))
                    @set('postActions', true)
                    <li class="post-action">
                        <a class="post-action-link action-link-ban" href="{!! $post->url("mod/ban") !!}">@lang('board.action.ban')</a>
                    </li>
                    @endif

                    @if ($post->canDelete($user))
                    @set('postActions', true)
                        <li class="post-action">
                            <a class="post-action-link action-link-delete" href="{!! $post->url("mod/delete") !!}">@lang('board.action.delete')</a>
                        </li>

                        @if ($postHasIp)
                        @if ($board->canDelete($user))
                        <li class="post-action">
                            <a class="post-action-link action-link-delete-all" href="{!! $post->url("mod/delete/all") !!}">@lang('board.action.delete_board')</a>
                        </li>
                        @endif

                        @if ($board->canBan($user))
                        <li class="post-action">
                            <a class="post-action-link action-link-ban-delete" href="{!! $post->url("mod/ban/delete") !!}">@lang('board.action.ban_delete')</a>
                        </li>

                        <li class="post-action">
                            <a class="post-action-link action-link-ban-delete-all" href="{!! $post->url("mod/ban/delete/all") !!}">@lang('board.action.ban_delete_board')</a>
                        </li>
                        @endif
                        @endif
                    @endif
                </ul>

                <ul class="post-actions">
                    @if ($user->canFeatureGlobally($post))
                    @set('postActions', true)
                        <li class="post-action">
                            <a class="post-action-link action-link-feature-global" href="{!! $post->url("feature/global") !!}">@lang('board.action.feature_global')</a>
                        </li>

                    @endif

                    @if ($user->canDeleteGlobally() && $postHasIp)
                        @set('postActions', true)
                        <li class="post-action">
                            <a class="post-action-link action-link-delete-global" href="{!! $post->url("mod/delete/global") !!}">@lang('board.action.delete_global')</a>
                        </li>

                        @if ($user->canBanGlobally())
                        <li class="post-action">
                            <a class="post-action-link action-link-ban-delete-global" href="{!! $post->url("mod/ban/delete/global") !!}">@lang('board.action.ban_delete_global')</a>
                        </li>
                        @endif
                    @endif
                </ul>
            </li>
        </ul>
    </div>

    @if ($postActions)
        @overwrite
        @yield('post-actions')
    @else
        @stop
    @endif

</div>
@endspaceless
