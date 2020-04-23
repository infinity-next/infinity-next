<nav class="cp-side">
    <section class="cp-linklists">
        <ul class="cp-linkgroups">
            <li class="cp-linkgroup">
                <a class="linkgroup-name">@lang('nav.panel.secondary.site.setup')</a>

                <ul class="cp-linkitems">
                    <li class="cp-linkitem">
                        @can('admin-config')<a class="linkitem-name" href="{!! route('panel.site.config') !!}">@lang('nav.panel.secondary.site.config')</a>@endcan
                        @can('admin-config')<a class="linkitem-name" href="{!! route('panel.site.pages') !!}">@lang('nav.panel.secondary.site.pages')</a>@endcan
                        @can('ban-file')<a class="linkitem-name" href="{!! route('panel.site.files.index') !!}">@lang('nav.panel.secondary.site.files')</a>@endcan
                        @can('admin-config')<a class="linkitem-name" href="{!! route('panel.site.phpinfo') !!}">phpinfo</a>@endcan
                    </li>
                </ul>
            </li>
        </ul>
    </section>
</nav>
