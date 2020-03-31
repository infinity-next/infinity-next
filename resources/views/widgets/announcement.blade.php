@if (strlen(site_setting('siteAnnouncement')))
<!-- Don't even think about styling this out -->
<aside class="announcement">{!! site_setting('siteAnnouncement') !!}</aside>
@endif
