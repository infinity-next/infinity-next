<form action="{!! url('/cp/donate/') !!}" method="POST" id="payment-form" class="form-donate" data-widget="donate">
	<input type="hidden" name="_token" value="{{ csrf_token() }}" />
	
	@if (env('APP_DEBUG'))
		@include('widgets.messages', [ 'messages' => [ "<tt>APP_DEBUG</tt> is set to <tt>TRUE</tt>. This form is only a test and will not charge." ] ])
	@endif
	
	<fieldset id="card-details" class="form-fields require-js">
		<legend class="form-legend">Contribute Funds to Infinity Next Development Group</legend>
		
			@include( $c->template('panel.donate.form') )
	</fieldset>
	
	<div id="payment-security" class="grid-50 require-js">
		<span class="security-footer"><strong>At no point</strong> is your personal information stored on this webserver, even temporarily. We interact strictly through the merchant service.</span>
		
		<ul class="security-steps">
			<li class="security-step">
				<div class="security-icons"><i class="fa fa-user"></i><i class="fa fa-angle-right"></i><i class="fa fa-institution"></i></div>
				<span class="security-item">When submitted, your details are encrypted and sent directly to our merchant from your browser.</span>
			</li>
			<li class="security-step">
				<div class="security-icons"><i class="fa fa-institution"></i><i class="fa fa-angle-right"></i><i class="fa fa-user"></i></div>
				<span class="security-item">If valid, our merchant responds with an encrypted, non-identifying token.</span>
			</li>
			<li class="security-step">
				<div class="security-icons"><i class="fa fa-user"></i><i class="fa fa-angle-right"></i><i class="fa fa-server"></i></div>
				<span class="security-item">Your client sends the token to us instead of credit card details.</span>
			</li>
			<li class="security-step">
				<div class="security-icons"><i class="fa fa-server"></i><i class="fa fa-angle-right"></i><i class="fa fa-institution"></i></div>
				<span class="security-item">We check this token against our merchant's records. If it is valid, we charge it and store only a receipt.</span>
			</li>
		</ul>
	</div>
	
	@include( $c->template('panel.donate.bitcoin') )
	
</form>