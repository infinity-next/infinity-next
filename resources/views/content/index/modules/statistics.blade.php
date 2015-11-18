<div class="infobox" id="site-statistics">
	<div class="infobox-title">Global Statistics</div>
	<div class="infobox-info">
		<p>There are currently
			<strong>{{{ number_format($stats['boardCount']) }}}</strong> public board{{{ $stats['boardCount'] != 1 ? "s" : "" }}},
			<strong>{{{ number_format($stats['boardIndexedCount']) }}}</strong> total.
			Site-wide, <strong>{{{ number_format($stats['postRecentCount']) }}}</strong> post{{{ $stats['postRecentCount'] != 1 ? "s" : "" }}} have been made in the last hour,
			with <strong>{{{ number_format($stats['postCount']) }}}</strong> being made on all active boards since March 1st, 2015.</p>
	</div>
</div>