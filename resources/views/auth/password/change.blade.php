@extends('layouts.main')

@section('title', "Change Password")

@section('content')
<main>
	<section class="auth-form grid-container">
		@if (count($errors) > 0)
			<ul class="alerts grid-100">
			@foreach ($errors->all() as $error)
				<li class="alert">{{ $error }}</li>
			@endforeach
			</ul>
		@endif
		
		<div class="grid-100">
			<form class="form-pw" role="form" method="POST" action="{{ url('/cp/password/') }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}" />
				
				<fieldset class="form-fields">
					<legend class="form-legend">Change Password</legend>
					
					<input id="username" name="username" type="hidden" value="{!! Auth::user()->username !!}" />
					
					<div class="field row-password">
						<label class="field-label" for="password">Current Password</label>
						<input class="field-control"  id="password" name="password" type="password" />
					</div>
					
					<div class="field row-password_new">
						<label class="field-label" for="password_new">New Password</label>
						<input class="field-control"  id="password_new" name="password_new" type="password" />
					</div>
					
					<div class="field row-password_new_copassword_new_confirmationnfirm">
						<label class="field-label" for="password_new_confirmation">Confirm New Password</label>
						<input class="field-control"  id="password_new_confirmation" name="password_new_confirmation" type="password" />
					</div>
					
					<div class="field row-submit">
						<button type="submit">Change Password</button>
					</div>
				</fieldset>
			</form>
		</div>
	</section>
</main>
@endsection