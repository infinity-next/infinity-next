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
						<p>Feel free to <a href="{!! url("test") !!}">try it</a> out and consider <a href="{!! url("contribute") !!}">contributing</a>.</p>
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
		<!-- Intro -->
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
					Without a framework, the state of affairs will continue to deterroriate as more is done by more developers.
				</p>
				<p>
					With Larachan, we can aspire to add new features much quicker.
					Complex user permission masks, a wider variety of 3rd party embeds in posts, streamlined attachment management (such as hash banning).
					Even features that are currently impossible to do, like password protected boards, are a cinch when the current caching methods are discarded.
					These things are all very doable in a new architecture, and that is what I want to build.
				</p>
				<p>
					<strong><a href="{{{url('contribute')}}}">Help me build Larachan, and help the imageboard communities go beyond.</a></strong>
				</p>
			</section>
		</div>
		
		<!-- Blurb -->
		<div class="smooth-box">
			<section id="site-blurb">
				<div class="grid-50">
					<h3>To the Developers,</h3>
					<h4>Existing software caches in HTML</h4>
					<p>
						When you access <tt>/b/</tt>, there is a file called <tt>/b/index.html</tt> on the server.
						This HTML file is updated every time a post is made.
						Every page on a board has a subsequent <tt>2.html</tt> file.
						The reason an imageboard commonly has an arbitary 15 page limit is due to the time it takes to recache every page after a new thread is created.
						Some developers think they're clever by writing caches to the harddisk, but the bottleneck becomes the very expensive and hard limited disk I/O.
						With Larachan, posts never need to be deleted.
						Caching will work with existing technologies like Memcached.
					</p>
					<p>
						Aside from being ineffecient, it also causes <em>problems</em>.
						If you wanted to make a password-protected board, you'd not be able to, because, once unlocked, the page would be recached without a password form.
						Features (like the already extant delete-your-own-post button on Larachan) are impossible because the site cannot modify the view based on the request.
						Everything becomes more complicated with system built like this.
					</p>
					<p>
						There have been attempts at repairing this by implementing a front-end controller, but these patches caused problems on live servers.
						With enough time and effort, I'm sure it is possible to work around this system or rebuild it, but that's like rebuilding the engine block of a beat up Honda Civic. Why bother?
					</p>
					
					
					<h4>Existing software has a MySQL table for every board</h4>
					<p>
						<em>Every. Board.</em>
					</p>
					<p>
						<tt>posts_a</tt>, <tt>posts_b</tt>, and so on.
						MySQL queries on sites with a variable number of boards, such as in the Infinity branch, can take a full minute or more to join all 8000+ tables.
						Ineffecient queries take even longer, as they will have to reconnect and query each table individually.
						Since there is no model system on existing imageboards, SQL is written by hand, and less experienced programmers will make this mistake.
					</p>
					<p>
						There have been attempts at replacing this system with a global <tt>posts</tt> table, but that involves redoing <em>every single query on the system</em>.
						Since there aren't even any classes in most imageboard suites, this is a massive undertaking that would affect every file in the codebase.
						Again, with enough time and effort, anything is possible on, but why bother?
					</p>
					
					
					<h4>Existing software has no MVC</h4>
					<p>
						There is no front-end controller.<br />
						As previously mentioned, files are distributed from physical points on the harddrive after being generated by specific events.<br />
						Routing and dealing with requests is a cinch in Laravel. See how controllers work on <a href="https://github.com/8n-tech/larachan/blob/master/app/Http/routes.php">this very website</a>.
					</p>
					<p>
						There is no ODB, or even classes.<br />
						All SQL is done by hand, with varying results in effeciency based on who wrote it.
						This compounds with the general state of disrepair the database is in.<br />
						Laravel has one of the best framework models I've ever seen and I encourage everyone to <a href="http://laravel.com/docs/5.0/eloquent">check it out</a>.
					</p>
					<p>
						There is Twig.<br />
						<em>Twig</em> is a popular templating system, and while it is much more serviceable as a library than the codebase it resides in is, its biggest fault is translations.
						Localization on Infinity Branch is very clunky and <em>changing any letter in the master language will break all translations of it.</em>
						The system is literally <tt>[ "sp" => [ 'English translation' => 'Traducción al español' ] ]</tt>.<br />
						Laravel has a <a href="http://laravel.com/docs/5.0/localization">built-in translation system</a> that is much more robust and easier for translators to work with.
					</p>
					
					
					<h4>Existing software has poor HTML, CSS &amp; JS conventions</h4>
					<p>
						While working on Infinity, I decided I would try to resolve a few quality of life complaints users were having in /operate/.
						One of the &quot;easy&quot; requests I saw was a simply to add a &quot;Post a reply&quot; link at the bottom of the page to open the reply box.
					</p>
					<p>
						This simple fix turned into a two-day affair.
						I used JS to insert the link (as opening the reply box required JS).
						To do this, I needed an anchor element to insert the link after in the footer.
						I didn't find anything suitable, so did a little work in the footer to give new class names to elements.
						<em>Big mistake.</em>
					</p>
					<p>
						This simple change broke every script we had because they all relied on the arbitrary jQuery selector <tt>$('form[name="postcontrols"] > .delete:first')</tt>.
						This had been copy+pasted into every single utility dealing with the thread view.
					</p>
					<p>
						And the unfortunate reality is everything is like this, needlessly.
						It's so easy to write good HTML, CSS, and jQuery and for some reason it just was not done.
					</p>
					
					<p>
						If you agree that the imageboard communities deserve better software, check out the <a href="{{{url('contribute')}}}">contribution page</a> for ways to help out.
					</p>
				</div>
				
				<div class="grid-50">
					<h3>To the Imageboard User,</h3>
					<h4>"Why fix what isn't broken?"</h4>
					<p>
						If you are someone who is resistant to change, there's not much I can write here to convince you.
						Your mind is already made up. You like things as they are, no matter their shortcomings, and you don't want anything else.
						You are entitled to that opinion, and if this is successful and ends up on a website you visit, I hope you like it as much as you do what you currently use.
					</p>
					<p>
						<em>However</em>, for everyone else: it <em>is</em> broken.
						It's easy to overlook inconveniences if you're used to them.
						There are simple additions or changes that everyone using an imageboard could appreciate, but are difficult to add (or even outright impossible) due to the aging architecture they run on.
						Even if they are at all doable, simply because of how things are built, the time it takes to get <em>any</em> feature out is tremendously bloated.
					</p>
					<p>
						Go ahead and shitpost on <a href="{{{url('/test/')}}}">&gt;&gt;&gt;/test/</a> real quick.
						You'll notice that, on your post, there is a delete button.
						This is something that <em>already exists</em> on this software that is completely impossible on existing imageboards due to flaws in the technology.
						There is no way to render a page differently depending on who accesses it, but on Larachan, it is.
					</p>
					<p>
						Password protected boards, boards restricted to board owners / site staff, variable user permissions, a built-in thread archival, a robust API (for 3rd party tools), board settings for things like number of threads per page, and so on.
						The list of what existing imageboards <em>cannot do</em> that Larachan and Laravel are readily capable of is gigantic.
						Even if you don't like specifc ideas, the fact is that they are <em>possible</em>.
					</p>
					<p>
						If these things interest you, check out our <a href="{{{url('contribute')}}}">contribution page</a> for more details and ways to help out.
					</p>
				</div>
			</section>
		</div>
		
		<!-- FAQ -->
		<div class="smooth-box">
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
				
				<h3>Why PHP!?</h3>
				<p>
					<strong>PHP is the easiest language to get hosting for.</strong> No matter how junk your shared host is, they will have PHP.
					Since Wordpress is the most popular software suite on the Internet these days, it's impossible to find a host that doesn't accept PHP.
				</p>
				<p>
					<strong>PHP is a very forgiving syntax.</strong> It shouldn't be hard to get Larachan hosted and configured to your liking.
					PHP is a very easy, rookie-friendly language that even a novice host can make changes to.
				</p>
				<p>
					<strong>You know PHP.</strong> Every webmaster on the Internet has at least dabbled in PHP.
					With a gargantuan developer pool, finding help and contributions for Larachan will be easier on PHP.
				</p>
				<p>
					<strong>It's what I'm best at!</strong> Almost all of my experience as a developer comes from working with PHP.
					I understand its flaws (trust me, I do!), but it's what I know.
					For a project like this, where my objective is to have things done correctly and quickly, PHP is the best choice.<br />
					One day I'll learn Python, but for now, it's PHP or bust.
				</p>
			</section>
		</div>
	</div>
</main>
@endsection
