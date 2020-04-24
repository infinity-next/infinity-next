<nav class="cp-top">
    <section class="cp-linklists">
        @auth
        <ul class="cp-linkgroups">
            <li class="cp-linkgroup">
                <a class="linkgroup-name linkgroup-home" href="{!! route('panel.home') !!}">@lang('nav.panel.primary.home')</a>
            </li>

            @canany(['create', 'configure'], \App\Board::class)
            <li class="cp-linkgroup">
                <a class="linkgroup-name linkgroup-boards" href="{!! route('panel.boards.index') !!}">@lang('nav.panel.primary.board')</a>
            </li>
            @endcanany

            @can('admin-config')
            <li class="cp-linkgroup">
                <a class="linkgroup-name linkgroup-site" href="{!! route('panel.site.index') !!}">@lang('nav.panel.primary.site')</a>
            </li>
            @endcan

            @canany(['admin-users', 'admin-permissions', 'admin-config', 'ban-file'])
            <li class="cp-linkgroup">
                <a class="linkgroup-name linkgroup-users" href="{!! route('panel.user.index') !!}">@lang('nav.panel.primary.users')</a>
            </li>
            @endcanany
        </ul>
        @endauth

        <ul class="cp-linkgroups linkgroups-user" data-no-instant>
            @auth
            <li class="cp-linkgroup">
                @lang('panel.authed_as', [ 'name' => user()->getDisplayName() ])
            </li>
            <li class="cp-linkgroup">
                <a class="linkgroup-name" href="{!! route('logout') !!}">@lang('nav.panel.primary.logout')</a>
            </li>
            @else
            <li class="cp-linkgroup">
                <a class="linkgroup-name" href="{!! route('register') !!}">@lang('nav.panel.primary.register')</a>
            </li>
            <li class="cp-linkgroup">
                <a class="linkgroup-name" href="{!! route('login') !!}">@lang('nav.panel.primary.login')</a>
            </li>
            @endif
        </ul>
    </section>
</nav>
