@extends('layouts.main')

@section('header-inner')
	{{-- No header --}}
@endsection

@section('content')
<main id="boardlist" data-widget="boardlist">
	<div class="grid-container">
		<section id="site-info">
			<div class="grid-20">
				@include('content.index.modules.logo')
			</div>
			
			<div class="grid-40">
				@include('content.index.modules.description')
			</div>
			
			<div class="grid-40">
				@include('content.index.modules.statistics')
			</div>
		</section>
		
		<div class="board-list grid-100 grid-parent">
			<aside class="search-container grid-20">
				<form id="search-form" class="smooth-box" method="get" action="/boards.php">
					<h2 class="box-title">Search</h2>
					
					<div class="board-search box-content">
						<label class="search-item search-sfw">
							<input type="checkbox" id="search-sfw-input" name="sfw" value="1" {{-- if not search.nsfw --}}checked="checked"{{-- endif --}} />&nbsp;Hide NSFW boards
						</label>
						
						<div class="search-item search-title">
							<input type="text" id="search-title-input" name="title" name="title" value="{{-- search.title --}}" placeholder="Search titles..." />
						</div>
						
						<div class="search-item search-lang">
							<select id="search-lang-input" name="lang">
								<optgroup label="Popular">
									<option value="">All languages</option>
									<option value="en">English</option>
									<option value="es">Spanish</option>
								</optgroup>
								<optgroup label="All">
									<!-- for lang_code, lang_name in languages -->
									<option value="lang_code"><!-- lang_name --></option>
									<!-- endfor -->
								</optgroup>
							</select>
						</div>
						
						<div class="search-item search-tag">
							<input type="text" id="search-tag-input" name="tags" value="{{-- search.tags|join(' ') --}}" placeholder="Search tags..." />
						</div>
						
						<div class="search-item search-submit">
							<button id="search-submit">Search</button>
							<span id="search-loading" class="loading-small board-list-loading" style="display: none;"></span>
							<script type="text/javascript">
								/*
									Cheeky hack.
									DOM Mutation is now depreceated, but board-directory.js fires before this button is added.
									Since .ready() only fires after the entire page loads, we have this here to disable it as soon
									as we pass it in the DOM structure.
									We don't just disable="disable" it because then it would be broken for all non-JS browsers.
								*/
								document.getElementById( 'search-submit' ).disabled = "disabled";
								document.getElementById( 'search-loading' ).style.display = "inline-block";
							</script>
						</div>
					</div>
					
					<ul class="tag-list box-content">
						<!-- TODO: html_tags -->
					</ul>
				</form>
			</aside>
			
			<section class="board-list col grid-80">
				<div class="smooth-box">
					<table class="board-list-table">
						<!--
							If you are adding or removing columns to this file, there's a few steps.
							1. Make sure the data is being supplied by the boards.php/board-search.php file.
							2. Add or remove the <col /> tag and <th /> tag.
							3. If ADDING, please-please-please add a unique class to your cells and specify information in style.css! Don't duplicate class names.
							4. If ADDING, open js/board-directory.js and 'board-datum-xxx' definition that matches your data-column <th> attribute.
							5. Change the colspan="" attributes to be the new total of cells.
						-->
						<colgroup>
							<!-- <col class="board-meta" /> -->
							<col class="board-uri" />
							<col class="board-title" />
							<col class="board-pph" />
							<col class="board-unique" />
							<col class="board-tags" />
							<col class="board-max" />
						</colgroup>
						<thead class="board-list-head">
							<tr>
								<!-- <th class="board-meta" data-column="meta"></th> -->
								<th class="board-uri" data-column="uri">@lang('boardlist.table.uri')</th>
								<th class="board-title" data-column="title">@lang('boardlist.table.title')</th>
								<th class="board-pph" data-column="pph" title="Posts per hour">@lang('boardlist.table.pph')</th>
								<th class="board-unique" data-column="active" title="Unique IPs to post in the last 72 hours">@lang('boardlist.table.active')</th>
								<th class="board-tags" data-column="tags">@lang('boardlist.table.tags')</th>
								<th class="board-max" data-column="posts_total">@lang('boardlist.table.total_posts')</th>
							</tr>
						</thead>
						
						<tbody class="board-list-tbody"><!-- TODO: INITIAL BOARD HTML HERE --></tbody>
						
						<tbody class="board-list-loading">
							<tr>
								<td colspan="6" class="loading"></td>
							</tr>
						</tbody>
						
						<tbody class="board-list-omitted" data-omitted="0"><!-- TODO:: BOARDS OMITTED COUNT HERE -->
							<tr>
								<td colspan="6" id="board-list-more">Displaying results <span class="board-page-num"><!-- search.page + 1 --></span> through <span class="board-page-count"><!-- boards|count + search.page --></span> out of <span class="board-page-total"><!-- boards|count + boards_omitted --></span>. <span class="board-page-loadmore">Click to load more.</span></td>
								
								<!-- if boards_omitted > 0 -->
								<script type="text/javascript">
									/* Cheeky hack redux.
									   We want to show the loadmore for JS users when we have omitted boards.
									   However, the board-directory.js isn't designed to manipulate the page index on immediate load. */
									document.getElementById("board-list-more").className = "board-list-hasmore";
								</script>
								<!-- endif -->
							</tr>
						</tbody>
					</table>
				</div>
			</section>
		</div>
	</div>
</main>
@endsection
