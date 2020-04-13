<div class="actions-anchor actions-post" data-no-instant>
    <span class="actions-label"><i class="fa fa-angle-down"></i></span>

    {{-- Board specific content management actions --}}
    <div class="actions">
        <div class="action">
            <a class="action-link action-link-report " href="{!! $post->getModUrl('report') !!}">
                @lang('board.action.report')
            </a>
        </div>

        <div class="action">
            <a class="action-link action-link-report-global " href="{!! $post->getModUrl('report.global') !!}">
                @lang('board.action.report_global')
            </a>
        </div>

        <div class="action">
            <a class="action-link action-link-report " href="{!! $post->getUrl('history') !!}">
                @lang('board.action.history', [
                    'board_uri' => $details['board_uri'],
                ])
            </a>
        </div>

        <div class="action">
            <a class="action-link action-link-report " href="{!! route('panel.history.global', [ 'ip' => $details['author_ip'], ]) !!}">
                @lang('board.action.history_global')
            </a>
        </div>

        <div class="action">
            <a class="action-link action-link-edit " href="{!! $post->getModUrl('edit') !!}">
                @lang('board.action.edit')
            </a>
        </div>

        <div class="action">
            <a class="action-link action-link-sticky" href="{!! $post->getModUrl('sticky') !!}">
                @lang('board.action.sticky')
            </a>
        </div>

        <div class="action">
            <a class="action-link action-link-unsticky " href="{!! $post->getModUrl('unsticky') !!}">
                @lang('board.action.unsticky')
            </a>
        </div>

        <div class="action">
            <a class="action-link action-link-lock " href="{!! $post->getModUrl('lock') !!}">
                @lang('board.action.lock')
            </a>
        </div>

        <div class="action">
            <a class="action-link action-link-unlock " href="{!! $post->getModUrl('unlock') !!}">
                @lang('board.action.unlock')
            </a>
        </div>

        <div class="action">
            <a class="action-link action-link-bumplock " href="{!! $post->getModUrl('bumplock') !!}">
                @lang('board.action.bumplock')
            </a>
        </div>

        <div class="action">
            <a class="action-link action-link-unbumplock " href="{!! $post->getModUrl('unbumplock') !!}">
                @lang('board.action.unbumplock')
            </a>
        </div>

        {{-- Broad sweeping user & board actions --}}
        <div class="action">
            <a class="action-link action-link-ban " href="{!! $post->getModUrl('mod', [
                    'ban'    => 1,
                ]) !!}">
                @lang('board.action.ban')
            </a>
        </div>

        <div class="action">
            <a class="action-link action-link-delete " href="{!! $post->getModUrl('mod', [
                    'delete' => 1,
                ]) !!}">
                @lang('board.action.delete')
            </a>
        </div>

        <div class="action">
            <a class="action-link action-link-delete-all " href="{!! $post->getModUrl('mod', [
                    'delete' => 1,
                    'scope'  => 'all',
                ]) !!}">
                @lang('board.action.delete_board')
            </a>
        </div>

        <div class="action">
            <a class="action-link action-link-ban-delete " href="{!! $post->getModUrl('mod', [
                    'delete' => 1,
                    'ban'    => 1,
                ]) !!}">
                @lang('board.action.ban_delete')
            </a>
        </div>

        <div class="action">
            <a class="action-link action-link-ban-delete-all " href="{!! $post->getModUrl('mod', [
                    'delete' => 1,
                    'ban'    => 1,
                    'scope'  => 'all',
                ]) !!}">
                @lang('board.action.ban_delete_board')
            </a>
        </div>

        {{-- Global Actions --}}
        <div class="action">
            <a class="action-link action-link-feature-global " href="{!! $post->getModUrl('feature') !!}">
                @lang(isset($details['featured_at']) && $details['featured_at']
                    ? 'board.action.refeature'
                    : 'board.action.feature'
                )
            </a>
        </div>

        <div class="action">
            <a class="action-link action-link-delete-global " href="{!! $post->getModUrl('mod', [
                    'delete' => 1,
                    'scope'  => 'global',
                ]) !!}">
                @lang('board.action.delete_global')
            </a>
        </div>

        <div class="action">
            <a class="action-link action-link-ban-delete-global " href="{!! $post->getModUrl('mod', [
                    'delete' => 1,
                    'ban'    => 1,
                    'scope'  => 'global',
                ]) !!}">
                @lang('board.action.ban_delete_global')
            </a>
            </div>
    </div>
</div>
