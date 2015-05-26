@extends('layouts.main.simplebox')

@section('title', "Reset Password")

@section('body')
<form class="form-pw" role="form" method="POST" action="{{ url('/password/email') }}">
	<input type="hidden" name="_token" value="{{ csrf_token() }}" />
	
	<fieldset class="form-fields">
		<legend class="form-legend">Reset Password</legend>
		
		<div class="field row-email">
			<label class="field-label" for="email">E-Mail Address</label>
			<input class="field-control"  id="email" name="email" type="email" value="{{ old('email') }}" />
		</div>
		
		<div class="field row-captcha">
			<label class="field-label" for="captcha">{!! Captcha::img() !!}</label>
			<input class="field-control" id="captcha" name="captcha" type="text" />
		</div>
		
		<div class="field row-submit">
			<button type="submit">Send Password Reset Link</button>
		</div>
	</fieldset>
</form>
@endsection