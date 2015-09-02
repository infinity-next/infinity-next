<div class="report-content">
	@if ($report->reason)
	<blockquote class="report-reason">{{$report->reason}}</blockquote>
	@else
	<blockquote class="report-reason report-no-reason"></blockquote>
	@endif
	
	<ul class="report-details">
		<li class="report-detail detail-actions">
			<a href="{{ url("/cp/boards/report/{$report->report_id}/dismiss") }}" class="report-dismiss-ip">@lang('panel.reports.dismiss_single')</a>
		</li>
		@if ($report->canPromote($user))
		<li class="report-detail detail-actions">
			<a href="{{ url("/cp/boards/report/{$report->report_id}/dismiss") }}" class="report-dismiss-ip">@lang('panel.reports.promote_single')</a>
		</li>
		@endif
		@if ($report->canDemote($user))
		<li class="report-detail detail-actions">
			<a href="{{ url("/cp/boards/report/{$report->report_id}/dismiss") }}" class="report-dismiss-ip">@lang('panel.reports.demote_single')</a>
		</li>
		@endif
		
		@if ($report->global)
		<li class="report-detail detail-global">@lang('panel.reports.global_single')</li>
		@else
		<li class="report-detail detail-local">@lang('panel.reports.local_single')</li>
		@endif
		
		<li class="report-detail detail-ip">{{ $user->getTextForIP($report->getReporterIpAsString()) }} [<a href="{{ url("/cp/boards/report/{$report->report_id}/dismiss-ip") }}" class="report-dismiss-ip">@lang('panel.reports.dismiss_ip')</a>]</li>
		
		<li class="report-detail detail-association">
		@if ($report->user_id)
			<li class="report-detail detail-association">@lang('panel.reports.is_associated')</li>
		@else
			<li class="report-detail detail-association-none">@lang('panel.reports.is_not_associated')</li>
		@endif
	</ul>
</div>