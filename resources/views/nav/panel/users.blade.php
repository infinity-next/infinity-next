<nav class="cp-side">
    <section class="cp-linklists">
        <ul class="cp-linkgroups">
            @can('admin-users')
            <li class="cp-linkgroup">
                <a class="linkgroup-name">@lang('nav.panel.secondary.users.permissions')</a>

                <ul class="cp-linkitems">
                    <li class="cp-linkitem">
                        <a class="linkitem-name" href="{!! route('panel.user.index') !!}">@lang('nav.panel.secondary.users.user_index')</a>
                    </li>
                    <li class="cp-linkitem">
                        <a class="linkitem-name" href="{!! route('panel.role.index') !!}">@lang('nav.panel.secondary.users.role_permissions')</a>
                    </li>
                </ul>
            </li>
            @endcan
        </ul>
    </section>
</nav>
