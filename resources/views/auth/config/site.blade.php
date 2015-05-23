@extends('layouts.cp')

@section('body')
{!! Form::open([
	'url'    => Request::url(),
	'method' => "PUT",
	'files'  => true,
	'id'     => "config-site",
	'class'  => "form-config",
]) !!}
	@include('widgets.messages')
	
	<h3 class="config-title">{{ trans("config.title.site") }}</h3>
	
	@foreach ($groups as $group)
	<fieldset class="form-fields group-{{{ $group->group_name }}}">
		<legend class="form-legend">{{ trans("config.legend.{$group->group_name}") }}</legend>
		
		@foreach ($group->options as $option)
			@include($option->getTemplate(), [ 'option' => $option, 'group' => $group ])
		@endforeach
	</fieldset>
	@endforeach
{!! Form::close() !!}
@endsection