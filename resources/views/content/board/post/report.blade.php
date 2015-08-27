<div class="report-content">
	@if ($report->reason)
	<blockquote class="report-reason">{{$report->reason}}</blockquote>
	@else
	<blockquote class="report-reason report-no-reason"></blockquote>
	@endif
	
	<ul class="report-details">
		<li class="report-detail detail-dismiss"><a href="{{ url("/cp/boards/report/{$report->report_id}/dismiss") }}" class="report-dismiss-ip">@lang('board.report.dismiss_single')</a></li>
		
		<li class="report-detail detail-ip">{{ $user->getTextForIP($report->ip) }} [<a href="{{ url("/cp/boards/report/{$report->report_id}/dismiss-all") }}" class="report-dismiss-ip">@lang('board.report.dismiss_ip')</a>]</li>
		
		<li class="report-detail detail-association">
		@if ($report->user_id)
			<li class="report-detail detail-association">@lang('board.report.is_associated')</li>
		@else
			<li class="report-detail detail-association-none">@lang('board.report.is_not_associated')</li>
		@endif
	</ul>
</div>