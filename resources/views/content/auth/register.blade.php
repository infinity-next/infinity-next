@extends('layouts.main.simplebox')

@section('title', "Register")

@section('body')
{!! Form::open([
    'url'    => route('auth.register.attempt'),
    'method' => "POST",
    'id'     => "create-form",
    'class'  => "form-auth",
]) !!}
    @include('content.auth.register.form')

    <div class="field row-submit">
        <button type="submit" class="field-submit">@lang('panel.field.register')</button>
    </div>
{!! Form::close() !!}
@endsection
