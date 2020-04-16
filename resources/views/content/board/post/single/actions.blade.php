<div class="actions-anchor actions-post" data-no-instant>
    <span class="actions-label"><i class="fa fa-angle-down"></i></span>

    {{-- Board specific content management actions --}}
    <div class="actions">
        <a class="action action-report " href="{!! $post->getModUrl('report') !!}">
            @lang('board.action.report')
        </a>

        <a class="action action-report-global " href="{!! $post->getModUrl('report.global') !!}">
            @lang('board.action.report_global')
        </a>

        <a class="action action-history " href="{!! $post->getModUrl('history') !!}">
            @lang('board.action.history', [ 'board_uri' => $details['board_uri'], ])
        </a>

        <a class="action action-history-global " href="{!! $post->getModUrl('history.global') !!}">
            @lang('board.action.history_global')
        </a>

        <a class="action action-edit " href="{!! $post->getModUrl('edit') !!}">
            @lang('board.action.edit')
        </a>

        <a class="action action-sticky" href="{!! $post->getModUrl('sticky') !!}">
            @lang('board.action.sticky')
        </a>

        <a class="action action-unsticky " href="{!! $post->getModUrl('unsticky') !!}">
            @lang('board.action.unsticky')
        </a>

        <a class="action action-lock " href="{!! $post->getModUrl('lock') !!}">
            @lang('board.action.lock')
        </a>

        <a class="action action-unlock " href="{!! $post->getModUrl('unlock') !!}">
            @lang('board.action.unlock')
        </a>

        <a class="action action-bumplock " href="{!! $post->getModUrl('bumplock') !!}">
            @lang('board.action.bumplock')
        </a>

        <a class="action action-unbumplock " href="{!! $post->getModUrl('unbumplock') !!}">
            @lang('board.action.unbumplock')
        </a>

        <a class="action action-ban " href="{!! $post->getModUrl('mod') !!}">
            @lang('board.action.moderate')
        </a>
    </div>
</div>
