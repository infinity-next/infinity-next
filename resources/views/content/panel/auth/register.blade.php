@extends('layouts.simplebox')

@section('title', "Register")

@section('body')
<form class="form-auth" role="form" method="POST" action="{{ url('/cp/auth/register') }}">
	<input type="hidden" name="_token" value="{{ csrf_token() }}" />
	
	<fieldset class="form-fields">
		<legend class="form-legend">Register</legend>
		
		<div class="field row-username">
			<label class="field-label" for="username">Username</label>
			<input class="field-control" id="username" name="username" value="{{ old('username') }}" type="text" maxlength="64" />
		</div>
		
		<div class="field row-email">
			<label class="field-label" for="email">
				Email
				<span class="field-description">This field is optional, but is required to reset your password.</span>
			</label>
			<input class="field-control" id="email" name="email" value="{{ old('email') }}" type="email" maxlength="254" />
		</div>
		
		<div class="field row-password">
			<label class="field-label" for="password">Password</label>
			<input class="field-control" id="password" name="password" type="password" />
		</div>
		
		<div class="field row-password_confirmation">
			<label class="field-label" for="password_confirmation">Confirm Password</label>
			<input class="field-control" id="password_confirmation" name="password_confirmation" type="password" />
		</div>
		
		<div class="field row-captcha">
			<label class="field-label" for="captcha">
				{!! Captcha::img() !!}
				<span class="field-validation"></span>
			</label>
			<input class="field-control" id="captcha" name="captcha" type="text" />
		</div>
		
		<div class="field row-submit">
			<button type="submit" class="field-submit">Register</button>
		</div>
	</fieldset>
</form>
@endsection
