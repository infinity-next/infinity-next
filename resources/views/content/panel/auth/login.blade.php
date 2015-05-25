@extends('layouts.simplebox')

@section('title', "Login")

@section('body')
<form class="form-auth" role="form" method="POST" action="{{ url('/cp/auth/login') }}">
	<input type="hidden" name="_token" value="{{ csrf_token() }}" />
	
	<fieldset class="form-fields">
		<legend class="form-legend">Login</legend>
		
		<div class="field row-username">
			<label class="field-label" for="username">Username or Email</label>
			<input class="field-control" id="username" name="username" type="text" maxlength="64" />
		</div>
		
		<div class="field row-password">
			<label class="field-label" for="password">Password</label>
			<input class="field-control" id="password" name="password" type="password" maxlength="255" />
		</div>
		
		<div class="field row-forgot">
			<a href="{{ url('/cp/password/email') }}">Forgot password</a>
		</div>
		
		<div class="field row-register">
			<a href="{{ url('/cp/auth/register') }}">Register an account</a>
		</div>
		
		<div class="field row-submit">
			<button type="submit" class="field-submit">Login</button>
			<label class="field-label-inline"><input type="checkbox" name="remember"/> Remember Me</label>
		</div>
	</fieldset>
</form>
@endsection
