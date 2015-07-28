@extends('layouts.main')

@section('header-inner')
	{{-- No header --}}
@endsection

@section('content')
<main id="frontpage">
	<div class="grid-container">
		<section id="site-info">
			<div class="grid-20">
				@include($c->template('index.modules.logo'))
			</div>
			
			<div class="grid-40">
				@include($c->template('index.modules.description'))
			</div>
			
			<div class="grid-40">
				@include($c->template('index.modules.statistics'))
			</div>
		</section>
	</div>
	
	<div class="grid-container">
		<section id="site-highlights">
			<div class="grid-100">
				<div class="smooth-box">
					<h3>Check out these boards</h3>
					
					<ul class="index-highlights">
						<li class="index-highlight">
							<a href="/infinity/">/infinity/ - Infinity Next Development</a>
						</li>
						<li class="index-highlight">
							<a href="/update/">/update/ - Updates</a>
						</li>
						<li class="index-highlight">
							<a href="/space/">/space/ - Space</a>
						</li>
					</ul>
				</div>
			</div>
		</section>
	</section>
	
	@include($c->template('index.project_introduction'))
</main>
@endsection
