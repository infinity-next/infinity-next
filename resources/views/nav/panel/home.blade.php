<nav class="cp-side">
    <section class="cp-linklists">
        <ul class="cp-linkgroups">
            @if (!$user->isAnonymous())
            <li class="cp-linkgroup">
                <a class="linkgroup-name">@lang('nav.panel.secondary.home.account')</a>

                <ul class="cp-linkitems">
                    <li class="cp-linkitem">
                        <a class="linkitem-name" href="{!! route('panel.password') !!}">@lang('nav.panel.secondary.home.password_change')</a>
                    </li>
                </ul>
            </li>
            @endif

            <li class="cp-linkgroup">
                <a class="linkgroup-name">@lang('nav.panel.secondary.home.status')</a>

                <ul class="cp-linkitems">
                    <li class="cp-linkitem">
                        <a class="linkitem-name" href="{!! route('panel.banned') !!}">@lang('nav.panel.secondary.home.banned')</a>
                    </li>

                    <li class="cp-linkitem">
                        <a class="linkitem-name" href="{!! route('panel.site.bans') !!}">@lang('nav.panel.secondary.home.bans')</a>
                    </li>
                </ul>
            </li>

            {{--
            @if (env('CONTRIB_ENABLED', false))
            <li class="cp-linkgroup">
                <a class="linkgroup-name">@lang('nav.panel.secondary.home.sponsorship')</a>

                <ul class="cp-linkitems">
                    <li class="cp-linkitem">
                        <a class="linkitem-name" href="{!! secure_route('panel.donate') !!}">@lang('nav.panel.secondary.home.donate')</a>
                    </li>
                </ul>
            </li>
            @endif
            --}}
        </ul>
    </section>
</nav>
