@extends('layouts.main.panel')

@section('body')
{!! Form::open([
	'url'    => Request::url(),
	'method' => "PATCH",
	'files'  => true,
	'id'     => "config-site",
	'class'  => "form-config",
]) !!}
	<h3 class="config-title">{{ trans("config.title.site") }}</h3>
	
	@foreach ($groups as $group)
		@include( 'widgets.config.group' )
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