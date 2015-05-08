@extends('layouts.main')

@section('header-inner')
	<!-- No header. -->
@endsection

@section('content')
<div class="grid-container">
	<section class="grid-80 push-20" id="site-warning">
		Warning: Some boards on this site might contain content of an adult or offensive nature.<wbr />
		Please cease use of this site if it is illegal for you to view such content.<wbr />
		The boards on this site are made entirely by the users and do not represent the opinions of the administration of Larachan.<wbr />
		In the interest of free speech, only content that directly violates the DMCA or other US laws is deleted.<wbr />
	</section>
</div>

<main id="frontpage">
	<div class="grid-container">
		<section id="site-info">
			<figure class="grid-20" id="site-logo">
				<img src="/img/logo.png" alt="Site Logo" id="site-logo-img" />
			</figure>
			
			<div class="grid-40" id="site-description">
				<div class="infobox">
					<div class="infobox-title">Larachan</div>
					<div class="infobox-info">
						<p>Welcome to Larachan, the live development envrionment for the Larachan Imageboard Software.</p>
						<p>Feel free to <a href="{!! url("a") !!}">try it</a> out and consider <a href="{!! url("contribute") !!}">contributing</a>.</p>
					</div>
				</div>
			</div>
			
			<div class="grid-40" id="site-statistics">
				<div class="infobox">
					<div class="infobox-title">Global Statistics</div>
					<div class="infobox-info">
						<p>There is currently
							<strong>{{{$stats['boardCount']}}}</strong> public board{{{$stats['boardCount'] != 1 ? "s" : ""}}},
							<strong>{{{$stats['boardIndexedCount']}}}</strong> total.
							Side-wide, <strong>{{{$stats['postRecentCount']}}}</strong> post{{{$stats['postRecentCount'] != 1 ? "s" : ""}}} have been made in the last hour,
							with <strong>{{{$stats['postCount']}}}</strong> being made on all active boards since October 23, 2013.</p>
					</div>
				</div>
			</div>
		</section>
	</div>
	
	<div class="grid-container">
		<section class="grid-100" id="site-boards">
			@include('widgets.boardsearch')
		</section>
	</div>
</main>
@endsection
