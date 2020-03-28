<div class="report-content" data-no-instant>
    <ul class="report-actions actions-report">
        <li class="report-action action-dismiss">
            [<a href="{{ $report->getUrl('dismiss') }}" class="report-dismiss">@lang('panel.reports.dismiss_single')</a>]
        </li>

        @can('promote', $report)
        <li class="report-action action-promote">
            [<a href="{{ $report->getUrl('promote') }}" class="report-promote">@lang('panel.reports.promote_single')</a>]
        </li>
        @endcan

        @can('demote', $report)
        <li class="report-action action-demote">
            [<a href="{{ $report->getUrl('demote') }}" class="report-demote">@lang('panel.reports.demote_single')</a>]
        </li>
        @endcan
    </ul>

    @if($report->reason)
    <blockquote class="report-reason">{{ $report->reason }}</blockquote>
    @else
    <blockquote class="report-reason report-no-reason"></blockquote>
    @endif

    <ul class="report-details">
        @if($report->global)
        <li class="report-detail detail-global">@lang('panel.reports.global_single')</li>
        @else
        <li class="report-detail detail-local">@lang('panel.reports.local_single')</li>
        @endif

        <li class="report-detail detail-ip">{{ user()->getTextForIP($report->getReporterIpAsString()) }}
            [<a href="{{ $report->getUrl('dismiss.ip') }}" class="report-dismiss-ip">@lang('panel.reports.dismiss_ip')</a>]
        </li>

        <li class="report-detail detail-association">
        @if($report->user_id)
            <li class="report-detail detail-association">@lang('panel.reports.is_associated')</li>
        @else
            <li class="report-detail detail-association-none">@lang('panel.reports.is_not_associated')</li>
        @endif
    </ul>
</div>
