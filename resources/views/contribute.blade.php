@extends('layouts.main')

@section('title', "Contribute to Larachan")
@section('description', "Pushing imageboard communities beyond")

@section('content')
<main class="contrib">
	<section class="contrib-howto grid-container">
		<ul class="contrib-options smooth-box">
			<li class="contrib-option option-code grid-25">
				<a class="option-link" href="https://github.com/8n-tech/larachan/">
					<span class="option-stack fa-stack">
						<i class="fa fa-square fa-stack-2x"></i>
						<i class="fa fa-github-alt fa-stack-1x fa-inverse"></i>
					</span>
					
					<h3 class="option-name">Code</h3>
				</a>
				
				<blockquote class="option-desc">
					Larachan runs on the Laravel, the world's most popular PHP framework.<br />
					If you have experience with either, check out our GitHub.
				</blockquote>
			</li>
			<li class="contrib-option option-code grid-25">
				<a class="option-link" href="#contribute-shekel">
					<span class="option-stack fa-stack">
						<i class="fa fa-square fa-stack-2x"></i>
						<i class="fa fa-ils fa-stack-1x fa-inverse"></i>
					</span>
					
					<h3 class="option-name">Cash</h3>
				</a>
				
				<blockquote class="option-desc">
					I run on food and require electricity to power my laptop.<br />
					If you would like to support Larachan's full-time development, I can accept your faith with Stripe or in Bitcoins.
				</blockquote>
			</li>
			<li class="contrib-option option-code grid-25">
				<a class="option-link" href="#">
					<span class="option-stack fa-stack">
						<i class="fa fa-square fa-stack-2x"></i>
						<i class="fa fa-lightbulb-o fa-stack-1x fa-inverse"></i>
					</span>
					
					<h3 class="option-name">Ideas</h3>
				</a>
				
				<blockquote class="option-desc">
					The look and use of an imageboard is very important to its feel and culture.<br />
					I'm always listening to feedback and will often be pitching ideas to see how people respond.
				</blockquote>
			</li>
			<li class="option-code grid-25">
				<a class="option-link" href="#">
					<span class="option-stack fa-stack">
						<i class="fa fa-square fa-stack-2x"></i>
						<i class="fa fa-heart fa-stack-1x fa-inverse"></i>
					</span>
					
					<h3 class="option-name">Hype</h3>
				</a>
				
				<blockquote class="option-desc">
					Even if you have nothing else to give, you can still spread the word that this project exists. Helping shill increases our exposure to new donors.<br />
					Seeing excitement over the project is also a huge motivational factor that I sincerely appreciate.
				</blockquote>
			</li>
		</ul>
	</section>

	<section class="contrib-section section-me">
		<blockquote id="back-to-pol">“The question isn't who is going to let me;<br />it's who is going to stop me.”</blockquote>
		
		<blockquote class="me-autobio grid-container">
			<h3 class="me-title">My life in a few sentences</h3>
			<p>I'm Josh. I am neither a profiteer, nor a businessman.</p>
			<p>I made my first website at nine, hosted it on Geocities and wrote about my cat. Only a few years later, I picked up a video game with pretty extensive modability and began writing my own stuff for it. People liked what I made, and development of my mods consumed my free time when I was in middle school and high school. I didn't care about worksheets, only my code. Every day I'd bring home binders of college-ruled paper filled with nothing but pseudocode and ideas.</p>
			<p>The gamemode became pretty popular. When I was about fifteen, the older developers started talking about using MySQL as a storage engine for game data instead of plaintext files. I didn't know how to set up MySQL on my computer at that time, but I did know it was installed on my Dreamhost website, so I wrote a module that stored all this information online. Eventually I realized I could pull this data and make a high-score table, and I did. This was about all I got done before growing too old to stay interested in the game, however, and ended up transforming what I had made into an EVE Online fansite that read information from the EVE API system.</p>
			<p>By the time I was 17 I had dropped out of highschool and college. Neither could hold my attention, so I didn't do well. I focused all my time into learning more about web development and put all my chips on the table. I knew if I was going to do anything in life, I'd be working online. When one of my corpmates saw my EVE Online website, they liked it, and offered me work. Later that year we were both employed full time by a start-up with 3 developers (inclusive) and 6 staff total.</p>
			<p>Two years later, the company handled over $4,000,000,000 in payroll that annuam. It was no longer a start-up and they could afford to pay me enough to move out of my friend's place. Over the course of my employment with them, I had moved seven times in just under four years, traveling well over 16,000 miles combined. But, as they continued to grow, the rigid structures closed in around me. I was no longer able to set my own times and was never allowed to participate in the creation of solutions. People hired into positions over me handed decisions from on high and that is what I had to code. The thrill of the job had evaporated.</p>
			<p>As a developer, I thrive off passion. Knowing what I build is used and enjoyed, knowing that people depend on me. When every client was "the most important" and big deals could make or break the entire company, sixty hour weeks and sixteen hour shifts were adventures, not labor. By the end of my run with the company, it had gone as far from that as possible. Everything was dull, and again I was bored.</p>
			<p>So, I left.</p>
			<p>I am poised to do anything I want. With four years of work experience, I meet the minimum requirements for most jobs in my industry without having a diploma. I could whip up a portfolio website and find a replacement job in no time. I've already gotten offers, but that's not what I want to do.<br />My goals are now elsewhere: passion projects. This is where Larachan (and you) fit in.</p>
		</blockquote>
		
		<div class="me-skillset grid-container">
			<h4 class="me-title">Skills</h4>
			<ul class="me-skills">
				<li class="me-skill skill-js grid-50">
					<i class="fa fa-cogs"></i>
					<strong class="skill-name">JavaScript &amp; jQuery</strong>
					<p class="skill-desc">My primary responsibility with the company was maintaining every view on the system. It eventually became my strongest skillset, despite initial resistance to the idea of working with front-end technologies. My crowning achievement was a 15,000 line jQuery-based timesheet front-end that was the core feature of our system and my sole responsibility.</p>
				</li>
				<li class="me-skill skill-php grid-50">
					<i class="fa fa-code"></i>
					<strong class="skill-name">PHP</strong>
					<p class="skill-desc">The programming language I am most comfortable with is PHP. With an unapologetically lax and easy to learn syntax, powering enough core functionality to get just about anything done, it's unsurprising to me that it's the most common web language.<br /><em>(Please don't throw bricks through my windows, /tech/.)</em></p>
				</li>
			</ul>
			<ul class="me-skills">
				<li class="me-skill skill-css grid-50">
					<i class="fa fa-css3"></i>
					<strong class="skill-name">CSS &amp; HTML</strong>
					<p class="skill-desc">I have strong understanding of what CSS can do (and an encyclopedic understanding of IE6 caveats). Since so much of my job revolved around making live webpages look like static designs, a lot of the last four years have been dedicated to CSS selectors. I was also the guy telling people that their HTML was "unsemantical".<br />
					No, really, <tt>&lt;a href=&quot;javascript:doSomething()&quot;&gt;</tt> isn't how we do shit in 2015.</p>
				</li>
				<li class="me-skill skill-mysql grid-50">
					<i class="fa fa-database"></i>
					<strong class="skill-name">MySQL</strong>
					<p class="skill-desc">While not my greatest strength, I'm very familiar with databases. Everything I've ever done has used MySQL as its storage engine and I know enough about it to get by on any project. Perhaps even more importantly, I know when <em>not</em> to use the database, something I wish my coworkers had known before building entire features out of a complicated set of triggers, views, and stored procedures.</p>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="contrib-shekel grid-container">
		<a id="contribute-shekel"></a>
		<h3 class="shekel-title">I can afford to work ...</h3>
		<blockquote class="shekel-timer">17 days<wbr/> and<wbr/> 12 hours<wbr/></blockquote>
		<p class="shekel-oyvey">... thanks to the generous contributors.</p>
		
		<blockquote class="shekel-explainer">
			<p>This is based on the assumption that my quality of life will cost $400 AUD a week to sustain.</p>
			<p>My expenses are not that grand. Here is what factors into my estimate.</p>
			<ul>
				<li><strong>$200 for rent and utilities.</strong> My roommate and landlord is my friend so I'm not at risk of becoming homeless if things go sour, but it would strain our relationship not to meet my financial obligations to him.</li>
				<li><strong>$20 a day for consumables.</strong> Food, drink, etc. I'm a big guy. <img src="//i.imgur.com/AT2M04g.png" /></li>
				<li><strong>$60 a week for bills and "in case shit".</strong> This amount is saved to roll over <em>in case shit</em>. It also acts as a buffer in case I need to pay bills while looking for new work.</li>
			</ul>
		</blockquote>
	</section>
	
	<section class="contrib-donate">
		<div class="grid-container">
			@include('content.forms.donate')
		</div>
	</section>
	
	<section class="contrib-section section-code">
		<blockquote>
		</blockquote>
	</section>
</main>
@stop