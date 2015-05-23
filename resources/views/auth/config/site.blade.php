@extends('layouts.cp')

@section('nav-secondary')
	@include('nav.cp.site')
@endsection

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
	<fieldset class="form-fields group-{{{ $group->group_name }}}">
		<legend class="form-legend">{{ trans("config.legend.{$group->group_name}") }}</legend>
		
		@foreach ($group->options as $option)
			@include($option->getTemplate(), [ 'option' => $option, 'group' => $group ])
		@endforeach
	</fieldset>
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