<section class="contrib-donorlist">
	@foreach ($donors as $groupName => $group)
	@if (count($group) > 0)
	<ul class="donors donors-{!! $groupName !!} grid-container">
		@foreach ($group as $donor)
		<li class="donor grid-{!! $donorWeight[$groupName] !!}">
			<div class="donor-desc">
				@if ($donor->attribution)
					<span class="donor-name">{{{$donor->attribution}}}</span>
				@else
					<span class="donor-name donor-anon">Anonymous</span>
				@endif
				<span class="donor-amount">${{{ $donor->amount / 100 }}}</span>
			</div>
		</li>
		@endforeach
	</ul>
	@endif
	@endforeach
</section>