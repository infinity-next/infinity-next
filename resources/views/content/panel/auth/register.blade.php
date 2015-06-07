@extends('layouts.main.simplebox')

@section('title', "Register")

@section('body')
<form class="form-auth" role="form" method="POST" action="{{ url('/cp/auth/register') }}">
	<input type="hidden" name="_token" value="{{ csrf_token() }}" />
	
	@include($c->template('panel.auth.register.form'))
	
	<div class="field row-submit">
		<button type="submit" class="field-submit">@lang('panel.field.register')</button>
	</div>
</form>
@endsection
