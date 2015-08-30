@extends('layouts.main.panel')

@section('title', trans('panel.title.reports'))

@section('body')
	<section class="reports">
		@if (count($reportedPosts))
		<ul class="reported-posts">
			@foreach($reportedPosts as $reportedPost)
			<li class="reported-post">
				<article class="reported-content">
					<h3 class="report-board">/{{$reportedPost->board_uri}}/ - {{ $reportedPost->board->title}}</h3>
					<p class="report-board-desc">{{ $reportedPost->board->is_worksafe ? trans('board.sfw') : trans('board.nsfw') }}</p>
					
					<aside class="report-actions">
						<a class="report-action" {{ url("/cp/boards/reports/{$reportedPost->post_id}/dismiss-post") }}>@lang('panel.reports.dismiss_post')</a>
						<a class="report-action" {{ url("/cp/boards/reports/{$reportedPost->post_id}/promote-post") }}>@lang('panel.reports.promote_post')</a>
					</aside>
					
					<div class="post-container">
						@include( 'content.board.post.single', [
							'board'   => $reportedPost->board,
							'post'    => $reportedPost,
							'reports' => $reportedPost->reports,
							'catalog' => false,
						])
					</div>
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