@extends('layouts.main')

@section('header-inner')
	<!-- No header. -->
@endsection

@section('content')
<!--
<div class="grid-container">
	<section class="grid-80 push-20" id="site-warning">
		Warning: Some boards on this site might contain content of an adult or offensive nature.<wbr />
		Please cease use of this site if it is illegal for you to view such content.<wbr />
		The boards on this site are made entirely by the users and do not represent the opinions of the administration of Larachan.<wbr />
		In the interest of free speech, only content that directly violates the DMCA or other US laws is deleted.<wbr />
	</section>
</div>
-->

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
		<div class="smooth-box">
			@include('widgets.messages', [ 'messages' => [ "Larachan's fundraiser has not officially started. Please send feedback for these pages to josh@larachan.org"] ])
			
			<section id="site-intro">
				<h2>Introduction to Larachan</h2>
				<p>
					<em>Larachan</em> is an open-source imageboard software suite built on the <a href="//laravel.com">Laravel</a> framework in PHP.
					It is being built with the intention of superceding all currently available imageboard applications,
					inspired by my own long time use of imageboards and general low expectations for existing options.
					My two large contributions with the <a href="https://github.com/ctrlcctrlv/infinity">Infinity Development Group</a> gave rise to the concept of starting from scratch.
				</p>
				<p>
					The ultimate problem with these older codebases is their lack of direction.
					New, shiny features have to be built around existing flaws.
					When an inflatable springs a leak and you only apply a patch, the sides of the patch will also start to leak.
					Without adequaely addressing the problem, eventually end up with a large mass that doesn't actually improve the feature.
					This is where we are at now.
				</p>
				<p>
					I am someone with 4 years of professional experience in web development, and twice as many years in hobby work.
					Having been in teams and seen the long-term problems of code complications first hand,
					I know a complete rewrite with a proper foundation is the only real solution to the problem.
					The limited numbers of individuals working on separate code bases do not have the resources to slowly reform thousands of lines of code.
					Without a framework, the state of affairs will continue to deterroriate as more is done by more developers..
				</p>
				<p>
					<strong><a href="{{{url('contribute')}}}">Help me build Larachan, and help the imageboard communities go beyond.</a></strong>
				</p>
			</section>
			
			<section id="site-faq">
				<h2>Frequently Asked Questions</h2>
				
				<h3>Why should I donate?</h3>
				<p>
					If you are even a casual imageboard user, you've probably had a moment where you wanted something to work differently or better.
					A lot of "easy" additions, like password-protected boards, are not possible because of <em>massive, all-encompassing, fundamental flaws</em> in the software.
					I do not want to see imageboards go the way of BBS and Usenet, and neither should you.
					Without a reliable codebase and a developer who can afford to maintain and direct it, these problems will not go away and, at best, will stay the same.
				</p>
				
				<h3>Will this website compete with 4chan and 8chan?</h3>
				<p>
					<em>No.</em> I am building software, not a community.
					Content added to this site may vanish at any time, as it is for development and testing.
				</p>
				
				<h3>How can I contribute?</h3>
				<p>
					Larachan will be open-source once we enter version 0.1, which will happen immediately after I've written documentation for what I've already built.
					I cannot offer pay for work, as I'm already strapped for cash, but I will gladly serve as a reference to any serious contributors.
					If you are interested, I would seriously advise looking into the Laravel framework and familiarizing yourself with its architecture. There is a slight learning curve.
				</p>
				<p>
					If you want to be notified when the github repository is opened, please email me at <a href="mailt:josh@larachan.org">josh@larachan.org</a>.
				</p>
				
				<h3>Why did you build your own donation system? Why build it first thing?</h3>
				<p>
					Larachan's donation form was developed with Laravel's Stripe integration <em>specifically</em> to avoid Patreon, GoFundMe, Grattipay, and Kickstarter.
				</p>
				<p>
					Users of particular imageboards may be aware that those services are unfaithful to their patrons, despite taking a gratuitous 5 to 10% out of donations.
					They will kick anyone off their programs for any reason, or no reason at all.
					In the interest of webmasters, I've built a custom cashier system that can later be converted into a donation form for any website running Larachan.
					That way, only Stripe can cut funding to the website, and reliance on meddling, self-serving middleman can be avoided entirely.
				</p>
				
				<h3>I do not like this theme / it's too far from what I'm used to.</h3>
				<p>
					The website is being built with themeability in mind. The comforting blue gradient will return for those who want it.
				</p>
				
			</section>
		</div>
	</div>
</main>
@endsection
