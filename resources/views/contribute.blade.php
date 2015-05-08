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
				<a class="option-link" href="{!! url('cp/donate') !!}">
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
				<a class="option-link" href="{!! url('/larachan/') !!}">
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
				<a class="option-link">
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
	
	@include('widgets.devtimer')
	
	<section class="contrib-goals grid-container">
		<ul class="goals">
			
			<li class="goal smooth-box">
				<h4 class="goal-title">$2,000 Mark</h4>
				<blockquote class="goal-desc">
					With my first month's worth of work, I will focus my efforts primarily on the core functionality of an imageboard.
				</blockquote>
				
				<ul class="goal-items">
					<li class="goal-item grid-25">
						<span class="goal-part">Image attachments</span>
						<blockquote class="goal-helper">
							Attachments will be stored with a hashed name and recorded in the database with detailed information.
							This helps streamline future feature additions, assist with DMCA takedowns, and blacklist child pornography before it ever hits the servers.
							Currently, existing imageboards offer no such thing and similar features are hacked in.
						</blockquote>
					</li>
					<li class="goal-item grid-25">
						<span class="goal-part">Moderator tools</span>
						<blockquote class="goal-helper">
							Commonly, moderation tools are usually just links to command pages and are awkward to use.
							Spammers and raiders have an upper hand because posting systems are very streamlined while moderation tools are not.
							Having a lot of experience in JS+AJAX, I can build moderator tools that are many times more effecient.
						</blockquote>
					</li>
					<li class="goal-item grid-25">
						<span class="goal-part">Ban management</span>
						<blockquote class="goal-helper">
							Managing users and stopping spam is an eternal problem.
							Imageboards are intentionally crude in this regard, but rudamentary ban placement, ban management, and appeal systems are extremely important.
							Proper constrution of the ban system poises it for feature-richness in the later, long-term goals.
						</blockquote>
					</li>
					<li class="goal-item grid-25">
						<span class="goal-part">Site management &amp; config</span>
						<blockquote class="goal-helper">
							Configuration is usually ignored by developers because it is presumed that the website admin will be technically competent.
							However, modern imageboards have expanded to allow users to create their own boards.
							Exposing them to an unpolished, glitchy, complicated, and featureless backend is frustrating for everyone.
							As a final ambition for the $1,600 stretch goal, the framework for a proper board configuration will be laid.
						</blockquote>
					</li>
				</ul>
			</li>
			
			<li class="goal smooth-box">
				<h4 class="goal-title">$4,000 Mark</h4>
				<blockquote class="goal-desc">
					With room to breathe, we can top off the core feature set by adding something relatively novel, then revisit existing features and upgrade them.
				</blockquote>
				
				<ul class="goal-items">
					<li class="goal-item grid-25">
						<span class="goal-part">User board creation</span>
						<blockquote class="goal-helper">
							A wildly successful feature on the Infinity Branch of Vichan, implementing an optional board creation system is a must.
							User created accounts will be able to build their own and manage them neatly from their user control panel.
						</blockquote>
					</li>
					<li class="goal-item grid-25">
						<span class="goal-part">Moderator permissions</span>
						<blockquote class="goal-helper">
							A large issue with the extant imageboards is a lack of proper permission heirarchy, meaning that (from a developer standpoint) determining who can do what is awkward.
							This problems compounds with the issue of single-board owners and their local volunteers.
							Larachan account permissions will be based on group masks common in large software suites to allow for granular control of user permissions.
						</blockquote>
					</li>
					<li class="goal-item grid-25">
						<span class="goal-part">Board management &amp; config</span>
						<blockquote class="goal-helper">
							Mixing together the fine tuned moderator permissions and site management panel, we can begin to build config specific to board owners.
							This would include local volunteers, board-specific assets, and other settings to personalize each board.
						</blockquote>
					</li>
					<li class="goal-item grid-25">
						<span class="goal-part">Local bans &amp; public lists</span>
						<blockquote class="goal-helper">
							Taking the existing ban system, we can improve site transparency and publish bans for each board.
							This will include details for who placed them, how long they last, why it was placed, and if an appeal was denied.
							Board owners will be responsible for their own user management and handling their bans.
						</blockquote>
					</li>
				</ul>
			</li>
			
			<li class="goal smooth-box">
				<h4 class="goal-title">$6,000 Mark</h4>
				<blockquote class="goal-desc">
					With some big ticket items done and the suite coming together, we can start looking at quality of life improvements.
				</blockquote>
				
				<ul class="goal-items">
					<li class="goal-item grid-25">
						<span class="goal-part">Better posting tools</span>
						<blockquote class="goal-helper">
							Imageboards have used the same textarea for the last 15 years.
							There exists a variety of parsers we can look at and decide upon.
							Considering the importance of a post in the imageboard, there's no reason not to make the posting tool nice.
						</blockquote>
					</li>
					<li class="goal-item grid-25">
						<span class="goal-part">Multiple attachments</span>
						<blockquote class="goal-helper">
							Infinity Branch currently has multiple image attachments, but the way that attachments are inlined is clunky.
							Aside from just getting the feature working (which is the easy part), having a proper, unobstructive display is crucial.
						</blockquote>
					</li>
					<li class="goal-item grid-25">
						<span class="goal-part">Embeds and custom embeds</span>
						<blockquote class="goal-helper">
							There are dozens of popular websites that have APIs for embedding content.
							Everything you can imagine, from YouTube and Vimeo to Strawpoll and Imgur.
							While utilizing a single embed is easy, building out a system for site administrators to customize their embeds is time consuming but a very strong feature to have.
						</blockquote>
					</li>
					<li class="goal-item grid-25">
						<span class="goal-part">Multimedia attachments</span>
						<blockquote class="goal-helper">
							A longstanding feature request on imageboard suites is the ability to attach MP3s and other multimedia formats to their posts.
							Music, video, flash, and many other topical boards stand to gain a lot from this.
							Just lumping an attachment onto the system is easy, but I'd like to take the time to add user interfaces for these.
						</blockquote>
					</li>
				</ul>
			</li>
			
			<li class="goal smooth-box">
				<h4 class="goal-title">$8,000 Mark</h4>
				<blockquote class="goal-desc">
					By now, we'd be looking good. Lets try to fix something that's hard to talk about.
				</blockquote>
				
				<ul class="goal-items">
					<li class="goal-item grid-50">
						<span class="goal-part">Attachment bans</span>
						<blockquote class="goal-helper">
							Staff running a large imageboard often have to deal with illegal or copyrighted content.
							The systems in place to automatically root out this stuff on YouTube or other large systems is widely unavailable to smaller websites.
							At this mark, in preparation of moving large imageboards onto Larachan, I can begin to deal with <em>attachment bans</em>.
							<br /><br />
							This handles two tasks.
							First, allowing media of any format to be banned based on its hash.
							The second more complicated task is "fuzzy hashing", or banning images based on what they look like.
							A small, single-pixel change to a banned image (like child pornography) will completely change its hash.
							However, by utilizing special algorithms, it's possible to accurately ban this content based on what it looks like without having to store any illegal content on the server.
						</blockquote>
					</li>
					<li class="goal-item grid-50">
						<span class="goal-part">Hash &amp; spam databases</span>
						<blockquote class="goal-helper">
							<em>AdBlock</em> is an extremel popular advertisement blocking software that utilizes a shared database to propagate bans to evreyone using the plugin.
							Similarly, I can take time to set up Larachan to have databases of illegal hashes and known spam methods.
							Any instance of Larachan that <strong>opts in</strong> to use this database would learn bad hashes before ever seeing it itself.
							We can apply this to spam as well, and strengthen the security of all websites using the technology.
						</blockquote>
					</li>
				</ul>
			</li>
			
			<li class="goal smooth-box">
				<h4 class="goal-title">$10,000 Mark</h4>
				<blockquote class="goal-desc">
					At this level of funding, we can look to match the many smaller features of popular imageboard suites.
				</blockquote>
				
				<ul class="goal-items">
					<li class="goal-item grid-33">
						<span class="goal-part">Quality of life</span>
						<blockquote class="goal-helper">
							The Infinity branch of Vichan has a lot of simple JavaScript tools that make its site more usable.
							Infinite scrolling, inline post tools, thread counters, auto-updaters, etc.
							These are not complicated systems and are worth reproducing into Larachan.
							<br /><br />
							This would also be the time to listen to less critical feedback and implement "It would be nice if..." suggestions, and to go back and deal with anything we wish we had time for.
							In general, just improve the software as a whole.
						</blockquote>
					</li>
					<li class="goal-item grid-33">
						<span class="goal-part">Site-specific donor systems</span>
						<blockquote class="goal-helper">
							Specific imageboard owners have been getting shafted by fundraising systems like Patreon and Grattipay.
							The financial system is a bit of a monopoly, and this unfairness was what led me to build my own fundraising tools.
							I can re-implement this contribution system as a stand alone donation form.
							With that, imageboard owners will be able to raise funds without shelling out to and relying on the social justice bullies.
						</blockquote>
					</li>
					<li class="goal-item grid-33">
						<span class="goal-part">API</span>
						<blockquote class="goal-helper">
							An <em>API</em> is a way for other 3rd party applications to read or interact with your website.
							Twitter, 4chan, and pretty much every major web application will have an API.
							While this is a very nice feature to have, it doesn't directly affect the usability of the site, which is why its funding depth is so high.
						</blockquote>
					</li>
				</ul>
			</li>
			
			<li class="goal smooth-box">
				<h4 class="goal-title">$12,000 Mark</h4>
				<div clas="grid-100">
					<figure class="goal-figure">
						<img id="goal-newchan" src="/img/assets/newchan.png" alt="Larachan + Other Imageboards Combining" />
						<figcaption class="goal-helper">
							Having achieved a feature richness that meets and exceeds large, existing websites,
							it is likely that they will want to adopt the technology.<br />
							<br />
							I can now build migration scripts for popular imageboard software.
						</figucaption>
					</figure>
				</div>
			</li>
			
			<li class="goal smooth-box">
				<h4 class="goal-title">Beyond</h4>
				<blockquote class="goal-desc">
					Once all expectations are met, Larachan can continue forward with new features, and constantly fine-tuning tools and user interfaces.
					In truth, development never ends, and I try not to abandon my work.
					<br /><br />
					However, I have a pipe dream.
					<br /><br />
					With enough time and money, I would build what I've taken to calling a <em>confederation</em>.
					Rather than a single large website with many boards, a confederaton could run with many nodes, each with only a few boards.
					Other nodes could propagate the content of that board to their own database, and push posts made to the source (if allowed).
					Confederate nodes could centralize these independantly operated boards and act as an access point, while not revealing the true source of each node.
					<br /><br />
					Using this setup, information pushed into the confederation could not die.
					Malicious entities or governments who attempted to destroy one node would not cost the network any information.
					It would continue to propagate forward to as many individual nodes as possible.
					<br /><br />
					Just a pipe dream.
				</blockquote>
			</li>
			
		</ul>
	</section>
	
	@include('widgets.donorlist')
	
	<section class="contrib-section section-me">
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
					<p class="skill-desc">I have strong understanding of what CSS can do (and an encyclopedic understanding of IE6 caveats). Since so much of my job revolved around making live webpages look like static designs, a lot of the last four years have been dedicated to CSS selectors. I was also the guy telling people that their HTML was "unsemantical".</p>
				</li>
				<li class="me-skill skill-mysql grid-50">
					<i class="fa fa-database"></i>
					<strong class="skill-name">MySQL</strong>
					<p class="skill-desc">While not my greatest strength, I'm very familiar with databases. Everything I've ever done has used MySQL as its storage engine and I know enough about it to get by on any project. Perhaps just as important, I know when <em>not</em> to use the database, something I wish others had appreciated before building entire features out of a complicated set of triggers, views, and stored procedures.</p>
				</li>
			</ul>
		</div>
	</section>
</main>
@stop