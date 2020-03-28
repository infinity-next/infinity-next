@spaceless
<div class="post-action-bar action-bar-mod">
    @section('post-actions')
    @set('postActions', false)
    <div class="post-action-tab action-tab-actions" data-no-instant>
        <span class="post-action-label post-action-open"><span class="post-action-text">@lang('board.action.open')</span></span>

        <ul class="post-action-groups">
            <li class="post-action-group">

                <!-- Board specific content management actions -->
                <ul class="post-actions">
                    @can('report', $post)
                    @set('postActions', true)
                    <li class="post-action">
                        <a class="post-action-link action-link-report"
                            href="{!! $post->getModUrl('report') !!}">
                            @lang('board.action.report')
                        </a>
                    </li>
                    @endcan

                    @can('report-globally', $post)
                    @set('postActions', true)
                    <li class="post-action">
                        <a class="post-action-link action-link-report-global"
                            href="{!! $post->getModUrl('report.global') !!}">
                            @lang('board.action.report_global')
                        </a>
                    </li>
                    @endcan

                    @can('history', $post)
                    @set('postActions', true)
                    <li class="post-action">
                        <a class="post-action-link action-link-report"
                            href="{!! $post->getUrl('history') !!}">
                            @lang('board.action.history', [
                                'board_uri' => $details['board_uri'],
                            ])
                        </a>
                    </li>
                    @endcan

                    @can('global-history', $post)
                    @set('postActions', true)
                    <li class="post-action">
                        <a class="post-action-link action-link-report"
                            href="{!! route('panel.history.global', [
                                'ip' => (new \App\Support\IP\IP($details['author_ip']))->toText(),
                            ]) !!}">
                            @lang('board.action.history_global')
                        </a>
                    </li>
                    @endcan

                    @can('edit', $post)
                    @set('postActions', true)
                    <li class="post-action">
                        <a class="post-action-link action-link-edit"
                            href="{!! $post->getModUrl('edit') !!}">
                            @lang('board.action.edit')
                        </a>
                    </li>
                    @endcan

                    @can('sticky', $post)
                    @set('postActions', true)
                    <li class="post-action">
                        @if (!$post->stickied_at)
                        <a class="post-action-link action-link-sticky"
                            href="{!! $post->getModUrl('sticky') !!}">
                            @lang('board.action.sticky')
                        </a>
                        @else
                        <a class="post-action-link action-link-unsticky"
                            href="{!! $post->getModUrl('unsticky') !!}">
                            @lang('board.action.unsticky')
                        </a>
                        @endif
                    </li>
                    @endcan

                    @can('lock', $post)
                    @set('postActions', true)
                    <li class="post-action">
                        @if (!$post->locked_at)
                        <a class="post-action-link action-link-lock"
                            href="{!! $post->getModUrl('lock') !!}">
                            @lang('board.action.lock')
                        </a>
                        @else
                        <a class="post-action-link action-link-unlock"
                            href="{!! $post->getModUrl('unlock') !!}">
                            @lang('board.action.unlock')
                        </a>
                        @endif
                    </li>
                    @endcan

                    @can('bumplock', $post)
                    @set('postActions', true)
                    <li class="post-action">
                        @if (!$post->bumplocked_at)
                        <a class="post-action-link action-link-bumplock"
                            href="{!! $post->getModUrl('bumplock') !!}">
                            @lang('board.action.bumplock')
                        </a>
                        @else
                        <a class="post-action-link action-link-unbumplock"
                            href="{!! $post->getModUrl('unbumplock') !!}">
                            @lang('board.action.unbumplock')
                        </a>
                        @endif
                    </li>
                    @endcan
                </ul>

                <!-- Broad sweeping user & board actions -->
                <ul class="post-actions">
                    @can('ban', $post)
                    @set('postActions', true)
                    <li class="post-action">
                        <a class="post-action-link action-link-ban"
                            href="{!! $post->getModUrl('mod', [
                                'ban'    => 1,
                            ]) !!}">
                            @lang('board.action.ban')
                        </a>
                    </li>
                    @endcan

                    @can('delete', $post)
                    @set('postActions', true)
                        <li class="post-action">
                            <a class="post-action-link action-link-delete"
                                href="{!! $post->getModUrl('mod', [
                                    'delete' => 1,
                                ]) !!}">
                                @lang('board.action.delete')
                            </a>
                        </li>

                        @can('delete-history', $post)
                        <li class="post-action">
                            <a class="post-action-link action-link-delete-all"
                                href="{!! $post->getModUrl('mod', [
                                    'delete' => 1,
                                    'scope'  => 'all',
                                ]) !!}">
                                @lang('board.action.delete_board')
                            </a>
                        </li>
                        @endcan

                        @can('ban', $board)
                        <li class="post-action">
                            <a class="post-action-link action-link-ban-delete"
                                href="{!! $post->getModUrl('mod', [
                                    'delete' => 1,
                                    'ban'    => 1,
                                ]) !!}">
                                @lang('board.action.ban_delete')
                            </a>
                        </li>

                        <li class="post-action">
                            <a class="post-action-link action-link-ban-delete-all"
                                href="{!! $post->getModUrl('mod', [
                                    'delete' => 1,
                                    'ban'    => 1,
                                    'scope'  => 'all',
                                ]) !!}">
                                @lang('board.action.ban_delete_board')
                            </a>
                        </li>
                        @endcan
                    @endcan
                </ul>

                <ul class="post-actions">
                    @can('feature', $post)
                    @set('postActions', true)
                        <li class="post-action">
                            <a class="post-action-link action-link-feature-global"
                                href="{!! $post->getModUrl('feature') !!}">
                                @lang(isset($details['featured_at']) && $details['featured_at']
                                    ? 'board.action.refeature'
                                    : 'board.action.feature'
                                )
                            </a>
                        </li>

                        @if (isset($details['featured_at']) && $details['featured_at'])
                        <li class="post-action">
                            <a class="post-action-link action-link-feature-global"
                                href="{!! $post->getModUrl('feature') !!}">
                                @lang('board.action.feature')
                            </a>
                        </li>
                        @endif
                    @endcan

                    @can('global-delete', $post)
                        @set('postActions', true)
                        <li class="post-action">
                            <a class="post-action-link action-link-delete-global"
                                href="{!! $post->getModUrl('mod', [
                                    'delete' => 1,
                                    'scope'  => 'global',
                                ]) !!}">
                                @lang('board.action.delete_global')
                            </a>
                        </li>

                        @can('global-ban', $post)
                        <li class="post-action">
                            <a class="post-action-link action-link-ban-delete-global"
                                href="{!! $post->getModUrl('mod', [
                                    'delete' => 1,
                                    'ban'    => 1,
                                    'scope'  => 'global',
                                ]) !!}">
                                @lang('board.action.ban_delete_global')
                            </a>
                        </li>
                        @endcan
                    @endcan
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
