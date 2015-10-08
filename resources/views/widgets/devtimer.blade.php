<section class="contrib-shekel">
	<div class="grid-container">
		<a id="contribute-shekel"></a>
		
		@if ($devCarbon->isFuture())
			<h3 class="shekel-title">I can afford to work ...</h3>
			<blockquote class="shekel-timer" title="This is on the assumption of a 40 hour work week.">{{{ $devTimer }}} </blockquote>
			<p class="shekel-oyvey">... thanks to ${{ number_format($donations / 100) }} donated by generous contributors,<br />
				who have supported development for {{{ $devStart->diffInDays() }}} days so far.
			</p>
		@else
			<h3 class="shekel-title">I'm underfunded by ...</h3>
			<blockquote class="shekel-timer timer-underfunded" title="This is on the assumption of a 40 hour work week.">{{{ $devTimer }}} </blockquote>
			<p class="shekel-oyvey"> ... and need <a href="{{ secure_url("contribute") }}">more contributions</a> to keep the project on time.</p>
		@endif
	</div>
</section>