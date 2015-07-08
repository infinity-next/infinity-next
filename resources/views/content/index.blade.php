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
				<div class="infobox" id="site-description">
					<div class="infobox-title">Infinity</div>
					<div class="infobox-info">
						<p>Welcome to Infinity, the live development environment for the Infinity Imageboard Software.</p>
						<p>Feel free to <a href="{!! url("test") !!}">try it</a> out and consider <a href="{!! url("contribute") !!}">contributing</a>.</p>
					</div>
				</div>
			</div>
			
			<div class="grid-40" id="site-statistics">
				<div class="infobox">
					<div class="infobox-title">Global Statistics</div>
					<div class="infobox-info">
						<p>There is currently
							<strong>{{{ $stats['boardCount'] }}}</strong> public board{{{ $stats['boardCount'] != 1 ? "s" : "" }}},
							<strong>{{{ $stats['boardIndexedCount'] }}}</strong> total.
							Side-wide, <strong>{{{ $stats['postRecentCount'] }}}</strong> post{{{ $stats['postRecentCount'] != 1 ? "s" : "" }}} have been made in the last hour,
							with <strong>{{{ $stats['postCount'] }}}</strong> being made on all active boards since October 23, 2013.</p>
					</div>
				</div>
			</div>
		</section>
	</div>
	
	@include( $c->template('index.project_introduction') )
</main>
@endsection
