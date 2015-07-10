@extends('layouts.main')

@section('header-inner')
	{{-- No header --}}
@endsection

@section('content')
<div class="grid-container">
	<section class="grid-80 push-20">
		@include($c->template('index.modules.warning'))
	</section>
</div>

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
	
	@include($c->template('index.project_introduction'))
</main>
@endsection
