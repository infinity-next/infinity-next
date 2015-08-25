@extends('layouts.main.panel')

@section('title', "Reports")

@section('body')
	<section class="reports">
		<ul class="reported-posts">
			@foreach($reportedPosts as $reportedPost)
			<li class="reported-post">
				<article class="reported-content">
					<h3 class="report-board">/{{$reportedPost->board_uri}}/ - {{ $reportedPost->board->title}}</h3>
					<p class="report-board-desc">{{ $reportedPost->board->is_worksafe ? trans('board.sfw') : trans('board.nsfw') }}</p>
					
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
	</section>
@stop