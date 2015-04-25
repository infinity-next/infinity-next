@extends('layouts.main')

@section('title', "Reset Password")

@section('content')
<main>
	<section class="auth-form grid-container smooth-box">
		@if (count($errors) > 0)
			<ul class="alerts grid-100">
			@foreach ($errors->all() as $error)
				<li class="alert">{{ $error }}</li>
			@endforeach
			</ul>
		@endif
		
		<div class="grid-100">
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
		</div>
	</section>
</main>
@endsection