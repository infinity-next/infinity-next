@extends('layouts.main')

@section('title', trans('board.config.title', [ 'board_uri' => $board->board_uri ]))

@section('content')
<main class="board-config">
    <section class="board-config-table grid-container">
        <div class="smooth-box">
            <table class="config-table">
                <colgroup>
                    <col style="width: 25em;" />
                    <col style="width: auto;" />
                </colgrop>
                <thead>
                    <tr>
                        <th>@lang('board.config.table.option')</th>
                        <th>@lang('board.config.table.value')</th>
                    </tr>
                </thead>
                @foreach ($groups as $group)
                @if ($group->options->count())
                <tbody>
                    <tr>
                        <td colspan="2"><strong>{{ $group->getDisplayName() }}</strong></td>
                    </tr>
                    @foreach ($group->options as $option)
                    <tr>
                        <td>{{ $option->getDisplayName() }}</td>
                        <td>{{ $option->getDisplayValue() }}</td>
                    </tr>
                    @endforeach
                </tbody>
                @endif
                @endforeach
            </table>
        </div>
    </section>
</main>
@stop
