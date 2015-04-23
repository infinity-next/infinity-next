@extends('layouts.main')

@section('content')
<header class="contrib-header">
	@include('widgets.boardlist')
	
	<h1 class="contrib-title">Help me develop Larachan.</h1>
	<h2 class="contrib-desc">Our communities deserve something better.</h2>
	
	<section class="contrib-howto">
		<h3 class="contrib-title">How to help</h3>
		
		<ul class="contrib-options">
			<li class="option-code">
				<a class="option-link" href="#">
					<span class="option-stack fa-stack">
						<i class="fa fa-square fa-stack-2x"></i>
						<i class="fa fa-github-alt fa-stack-1x fa-inverse"></i>
					</span>
					
					<blockquote class="option-desc">
						<h3>Code!</h3>
						<p>Larachan runs on the Laravel, the world's most popular PHP framework. If you have experience with either, check out our GitHub.</p>
					</blockquote>
				</a>
			</li>
			<li class="option-code">
				<a class="option-link" href="#">
					<span class="option-stack fa-stack">
						<i class="fa fa-square fa-stack-2x"></i>
						<i class="fa fa-ils fa-stack-1x fa-inverse"></i>
					</span>
					
					<blockquote class="option-desc">
						<h3>Cash!</h3>
						<p>I run on food and require electricity to power my electronics. If you want to support Larachan's full-time development, I can take accept your faith in Stripe or Bitcoins.</p>
					</blockquote>
				</a>
			</li>
			<li class="option-code">
				<a class="option-link" href="#">
					<span class="option-stack fa-stack">
						<i class="fa fa-square fa-stack-2x"></i>
						<i class="fa fa-lightbulb-o fa-stack-1x fa-inverse"></i>
					</span>
					
					<blockquote class="option-desc">
						<h3>Ideas!</h3>
						<p>The look and use of an imageboard is very important to its feel and culture. I'm always listening to feedback and will often be pitching ideas to see how people respond.</p>
					</blockquote>
				</a>
			</li>
			<li class="option-code">
				<a class="option-link" href="#">
					<span class="option-stack fa-stack">
						<i class="fa fa-square fa-stack-2x"></i>
						<i class="fa fa-heart fa-stack-1x fa-inverse"></i>
					</span>
					
					<blockquote class="option-desc">
						<h3>Hype!</h3>
						<p>Even if you have nothing else to give, you can still spread the word that this project exists. Helping shill increases the chance that someone with more to give may do so. Seeing excitement over the project is also a huge motivational factor that I sincerely appreciate.</p>
					</blockquote>
				</a>
			</li>
		</ul>
	</section>
</header>

<main class="contrib">
	<section class="contrib-section section-me">
		<h3 class="section-title">About Me</h3>
		<p>My name is Josh. I am not a profiteer, and I'm a shitty businessman.</p>
		<p>I made my first web page when I was nine. It was hosted on Geocities and was about my cat. A couple of years later I picked up a video game with pretty extensive modability and began writing my own stuff for it. People liked it, and development of this mod consumed my free time when I was in middleschool and highschool. I didn't care about worksheets, only my code. Every day I'd bring home binders with college rule paper filled with nothing but pseudocode and ideas.</p>
		<p>The gamemode became pretty popular. When I was about fifteen, the <em>cool kids</em> started talking about using MySQL as a storage engine for game data instead of plaintext files. I didn't know how to set up MySQL on my computer at that time, but I did know it was on my Dreamhost website. To emulate the older modders, I wrote a module that stored all this information online. Eventually I realized I could pull this data and make a high-score table, so I did. This was about all I got done before growing too old to stay interested in the game, however, and ended up transforming what I had made into an EVE Online fansite that read information from the EVE API system.</p>
		<p>By the time I was 18 I had dropped out of highschool and college. Neither could hold my attention so I didn't do well. I focused all my time into learning more about web development and put all my chips on the table. I knew if I was going to do anything in life, I'd be working online. When one of my corpmates saw my EVE Online website, they liked it, and offered me work. Later that year we were both employed full time by a start-up with 3 developers (inclusive) and 6 staff total.</p>
		<p>Two years later,the company handled over $4,000,000,000 in payroll that annuam. It was no longer a start-up and they could afford to pay me enough to move out of my home. Over the course of my employment with them, I had moved seven times in just under four years, traveling a combined 16,000 miles. But, as they continued to grow, the rigid structures closed in around me. I was no longer able to set my own times and was never allowed to participate in the creation of solutions. People hired into positions over me handed decisions from on high and that is what I had to code. The thrill of the job had evaporated.</p>
		<p>As a developer, I thrive off passion. Knowing what I build is used and enjoyed, knowing that people depend on me. When every client was "the most important" and big deals could break the entire company, sixty hour weeks and sixteen hour shifts were adventures, not labor. By the end of my run with the company, this was the furthest from true that I could imagine. Everything was dull, and once again I was bored.</p>
		<p>So, I left.</p>
		<p>I am poised to do anything I want. With four years of work experience, I meet the minimum requirements for most jobs in my industry without having a diploma. I could whip up a portfolio website and find a replacement for my work in no time. I've already gotten offers, but that's not what I want to do. My goals are now elsewhere: passion projects. Here is where Larachan fits in.</p>
	</section>
</main>

<footer class="contrib-footer">
	@include('widgets.boardlist')
	
	<section id="footnotes">
		<div>Larachan &copy; Larachan Development Group 2015</div>
	</section>
</footer>
@stop