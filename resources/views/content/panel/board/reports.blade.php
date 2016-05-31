@extends('layouts.main.panel')

@section('title', trans('panel.title.reports'))

@section('body')
<section class="reports">
    @if (count($reportedPosts))
    <ul class="reported-posts">
        @foreach($reportedPosts as $reportedPost)
        <li class="reported-post">
            <article class="reported-content">
                <h3 class="report-board" dir="ltr">/{{ $reportedPost->board_uri }}/ - {{ $reportedPost->board->title}}</h3>
                <p class="report-board-desc">{{ $reportedPost->board->is_worksafe ? trans('board.sfw') : trans('board.nsfw') }}</p>

                <ul class="report-actions actions-post" data-no-instant>
                    <li class="report-action">
                        <a class="report-action"
                            href="{{ route('panel.reports.dismiss.post', ['post' => $reportedPost]) }}">
                            @lang('panel.reports.dismiss_post')
                        </a>
                    </li>
                    @if ($reportedPost->countReportsCanPromote($user) > 0)
                    <li class="report-action">
                        <a class="report-action"
                            href="{{ route('panel.reports.promote.post', ['post' => $reportedPost]) }}">
                            @lang('panel.reports.promote_post')
                        </a>
                    </li>
                    @endif
                    @if ($reportedPost->countReportsCanDemote($user) > 0)
                    <li class="report-action">
                        <a class="report-action"
                            href="{{ route('panel.reports.demote.post', ['post' => $reportedPost]) }}">
                            @lang('panel.reports.demote_post')
                        </a>
                    </li>
                    @endif
                </ul>

                @include( 'content.board.post', [
                    'board'   => $reportedPost->board,
                    'post'    => $reportedPost,
                    'reports' => $reportedPost->reports,
                    'catalog' => false,
                    'preview' => false,
                ])
            </article>

            <ul class="post-reports">
                @foreach ($reportedPost->reports as $report)
                <li class="post-report">
                    <div class="report-container">
                        @include( 'content.board.post.report', [
                            'board'  => $reportedPost->board,
                            'post'   => $reportedPost,
                            'report' => $report,
                        ])
                    </div>
                </li>
                @endforeach
            </ul>
        </li>
        @endforeach
    </ul>
    @else
        <p>@lang('panel.reports.empty')
    @endif
</section>
@stop
