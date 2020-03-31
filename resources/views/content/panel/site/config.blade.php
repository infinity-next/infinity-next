@extends('layouts.main.panel')

@section('body')
{!! Form::open([
    'url'    => Request::url(),
    'method' => "PATCH",
    'files'  => true,
    'id'     => "config-site",
    'class'  => "form-config",
    'data-widget' => "config",
]) !!}
    <h3 class="config-title">@lang("panel.title.site")</h3>

    @foreach ($groups as $group)
        @include('widgets.config.group',[
            'group' => $group,
        ])
    @endforeach

    <div class="field row-submit">
        {!! Form::button(
            trans("config.submit"),
            [
                'type'      => "submit",
                'class'     => "field-submit",
        ]) !!}
    </div>
{!! Form::close() !!}
@endsection
