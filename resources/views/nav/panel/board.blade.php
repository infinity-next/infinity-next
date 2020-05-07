<nav class="cp-side">
    <section class="cp-linklists">
        <ul class="cp-linkgroups">
            @can('create', \App\Board::class)
            <li class="cp-linkgroup">
                <ul class="cp-linkitems">
                    <li class="cp-linkitem">
                        <a class="linkitem-name linkitem-name-createboard" href="{!! route('panel.boards.create') !!}">@lang('nav.panel.secondary.board.create')</a>
                    </li>
                </ul>
            </li>
            @endcan

            @can('configure', \App\Board::class)
            <li class="cp-linkgroup">
                <a class="linkgroup-name">@lang('nav.panel.secondary.board.boards')</a>

                <ul class="cp-linkitems">
                    <li class="cp-linkitem">
                        <a class="linkitem-name" href="{!! route('panel.boards.assets') !!}">@lang('nav.panel.secondary.board.assets')</a>
                    </li>

                    <li class="cp-linkitem">
                        <a class="linkitem-name" href="{!! route('panel.boards.config') !!}">@lang('nav.panel.secondary.board.config')</a>
                    </li>

                    <li class="cp-linkitem">
                        <a class="linkitem-name" href="{!! route('panel.boards.staff') !!}">@lang('nav.panel.secondary.board.staff')</a>
                    </li>
                </ul>
            </li>
            @endcan

            @can('view', \App\Report::class)
            <li class="cp-linkgroup">
                <a class="linkgroup-name">@lang('nav.panel.secondary.board.discipline')</a>

                <ul class="cp-linkitems">
                    <li class="cp-linkitem">
                        <a class="linkitem-name" href="{!! route('panel.appeals.index') !!}">@lang('nav.panel.secondary.board.appeals')</a>
                    </li>

                    <li class="cp-linkitem">
                        <a class="linkitem-name" href="{!! route('panel.reports.index') !!}">@lang('nav.panel.secondary.board.reports')</a>
                    </li>
                </ul>
            </li>
            @endcan
        </ul>
    </section>
</nav>
