<div class="actions-anchor actions-post" data-no-instant>
    @section('actions')
    @set('postActions', false)
    <span class="actions-label"><i class="fa fa-angle-down"></i></span>

    {{-- Board specific content management actions --}}
    <div class="actions">
        @can('report', $post)
        @set('postActions', true)
        <div class="action">
            <a class="action-link action-link-report"
                href="{!! $post->getModUrl('report') !!}">
                @lang('board.action.report')
            </a>
        </div>
        @endcan

        @can('report-globally', $post)
        @set('postActions', true)
        <div class="action">
            <a class="action-link action-link-report-global"
                href="{!! $post->getModUrl('report.global') !!}">
                @lang('board.action.report_global')
            </a>
        </div>
        @endcan

        @if (!user()->isAnonymous())
        @can('history', $post)
        @set('postActions', true)
        <div class="action">
            <a class="action-link action-link-report"
                href="{!! $post->getUrl('history') !!}">
                @lang('board.action.history', [
                    'board_uri' => $details['board_uri'],
                ])
            </a>
        </div>
        @endcan

        @can('global-history', $post)
        @set('postActions', true)
        <div class="action">
            <a class="action-link action-link-report"
                href="{!! route('panel.history.global', [
                    'ip' => $details['author_ip'],
                ]) !!}">
                @lang('board.action.history_global')
            </a>
        </div>
        @endcan

        @can('edit', $post)
        @set('postActions', true)
        <div class="action">
            <a class="action-link action-link-edit"
                href="{!! $post->getModUrl('edit') !!}">
                @lang('board.action.edit')
            </a>
        </div>
        @endcan

        @can('sticky', $post)
        @set('postActions', true)
        <div class="action">
            @if (!$post->stickied_at)
            <a class="action-link action-link-sticky"
                href="{!! $post->getModUrl('sticky') !!}">
                @lang('board.action.sticky')
            </a>
            @else
            <a class="action-link action-link-unsticky"
                href="{!! $post->getModUrl('unsticky') !!}">
                @lang('board.action.unsticky')
            </a>
            @endif
        </div>
        @endcan

        @can('lock', $post)
        @set('postActions', true)
        <div class="action">
            @if (!$post->locked_at)
            <a class="action-link action-link-lock"
                href="{!! $post->getModUrl('lock') !!}">
                @lang('board.action.lock')
            </a>
            @else
            <a class="action-link action-link-unlock"
                href="{!! $post->getModUrl('unlock') !!}">
                @lang('board.action.unlock')
            </a>
            @endif
        </div>
        @endcan

        @can('bumplock', $post)
        @set('postActions', true)
        <div class="action">
            @if (!$post->bumplocked_at)
            <a class="action-link action-link-bumplock"
                href="{!! $post->getModUrl('bumplock') !!}">
                @lang('board.action.bumplock')
            </a>
            @else
            <a class="action-link action-link-unbumplock"
                href="{!! $post->getModUrl('unbumplock') !!}">
                @lang('board.action.unbumplock')
            </a>
            @endif
        </div>
        @endcan

        {{-- Broad sweeping user & board actions --}}
        @can('ban', $post)
        @set('postActions', true)
        <div class="action">
            <a class="action-link action-link-ban"
                href="{!! $post->getModUrl('mod', [
                    'ban'    => 1,
                ]) !!}">
                @lang('board.action.ban')
            </a>
        </div>
        @endcan

        @can('delete', $post)
        @set('postActions', true)
            <div class="action">
                <a class="action-link action-link-delete"
                    href="{!! $post->getModUrl('mod', [
                        'delete' => 1,
                    ]) !!}">
                    @lang('board.action.delete')
                </a>
            </div>

            @can('delete-history', $post)
            <div class="action">
                <a class="action-link action-link-delete-all"
                    href="{!! $post->getModUrl('mod', [
                        'delete' => 1,
                        'scope'  => 'all',
                    ]) !!}">
                    @lang('board.action.delete_board')
                </a>
            </div>
            @endcan

            @can('ban', $board)
            <div class="action">
                <a class="action-link action-link-ban-delete"
                    href="{!! $post->getModUrl('mod', [
                        'delete' => 1,
                        'ban'    => 1,
                    ]) !!}">
                    @lang('board.action.ban_delete')
                </a>
            </div>

            <div class="action">
                <a class="action-link action-link-ban-delete-all"
                    href="{!! $post->getModUrl('mod', [
                        'delete' => 1,
                        'ban'    => 1,
                        'scope'  => 'all',
                    ]) !!}">
                    @lang('board.action.ban_delete_board')
                </a>
            </div>
            @endcan
        @endcan

        {{-- Global Actions --}}
        @can('feature', $post)
        @set('postActions', true)
            <div class="action">
                <a class="action-link action-link-feature-global"
                    href="{!! $post->getModUrl('feature') !!}">
                    @lang(isset($details['featured_at']) && $details['featured_at']
                        ? 'board.action.refeature'
                        : 'board.action.feature'
                    )
                </a>
            </div>

            @if (isset($details['featured_at']) && $details['featured_at'])
            <div class="action">
                <a class="action-link action-link-feature-global"
                    href="{!! $post->getModUrl('feature') !!}">
                    @lang('board.action.feature')
                </a>
            </div>
            @endif
        @endcan

        @can('global-delete', $post)
            @set('postActions', true)
            <div class="action">
                <a class="action-link action-link-delete-global"
                    href="{!! $post->getModUrl('mod', [
                        'delete' => 1,
                        'scope'  => 'global',
                    ]) !!}">
                    @lang('board.action.delete_global')
                </a>
            </div>

            @can('global-ban', $post)
            <div class="action">
                <a class="action-link action-link-ban-delete-global"
                    href="{!! $post->getModUrl('mod', [
                        'delete' => 1,
                        'ban'    => 1,
                        'scope'  => 'global',
                    ]) !!}">
                    @lang('board.action.ban_delete_global')
                </a>
            </div>
            @endcan
        @endcan
        @endif
    </div>

    @if ($postActions)
        @overwrite
        @yield('actions')
    @else
        @stop
    @endif

</div>
