<div class="infobox" id="site-statistics">
	<div class="infobox-title">@lang('index.title.statistics')</div>
	<div class="infobox-info">
		<p>@choice('index.info.statistic.boards', $stats['boardIndexedCount'], [
			'boards_total' => \Lang::choice('index.info.statistic.board_count', $stats['boardIndexedCount'], [
				'boards' => $stats['boardIndexedCount'],
			]),
			'boards_public' => \Lang::choice('index.info.statistic.board_count', $stats['boardTotalCount'], [
				'boards' => $stats['boardTotalCount'],
			]),
		])
		@lang('index.info.statistic.posts', [
			'recent_posts' => \Lang::choice('index.info.statistic.post_count', $stats['postRecentCount'], [
				'posts' => $stats['postRecentCount'],
			]),
		])
		@lang('index.info.statistic.posts_all', [
			'posts_total' => \Lang::choice('index.info.statistic.post_count', $stats['postCount'], [
				'posts' => $stats['postCount'],
			]),
			'start_date' => $stats['startDate']->format("F jS, Y"),
		])</p>
	</div>
</div>
