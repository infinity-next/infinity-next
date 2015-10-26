@extends('layouts.main')

@section('header-inner')
	{{-- No header --}}
@endsection

@section('content')
<main id="boardlist" data-no-instant>
	<div class="grid-container">
		<section id="site-info">
			<div class="grid-20">
				@include('content.index.modules.logo')
			</div>
			
			<div class="grid-80">
				@include('content.index.modules.statistics')
			</div>
		</section>
		
		<div class="grid-100 grid-parent">
			<div class="board-list" data-widget="boardlist">
				<aside class="search-container grid-20">
					<form id="search-form" class="smooth-box" method="get" action="{{ url('boards.html') }}">
						<div class="box-title">@lang('boardlist.search.title')</div>
						
						<div class="board-search box-content">
							<label class="search-item search-sfw">
								<input type="checkbox" id="search-sfw-input" name="sfw" value="1" @if (Request::get('sfw', false))checked="checked"@endif />&nbsp;@lang('boardlist.search.sfw_only')
							</label>
							
							<div class="search-item search-title">
								<input type="text" id="search-title-input" name="title" name="title" value="{{ Request::get('titles',"") }}" placeholder="@lang('boardlist.search.titles')" />
							</div>
							
							<div class="search-item search-lang">
								<select id="search-lang-input" name="lang">
									<optgroup label="@lang('boardlist.search.lang.popular')">
										<option value="">@lang('boardlist.search.lang.any')</option>
										<option value="eng">@lang('lang.eng')</option>
										<option value="spa">@lang('lang.spa')</option>
									</optgroup>
									<optgroup label="@lang('boardlist.search.lang.all')">
										@foreach (trans('lang') as $langIso => $langName)
										<option value="{{ $langIso }}">{{ $langName }}</option>
										@endforeach
									</optgroup>
								</select>
							</div>
							
							<div class="search-item search-tag">
								<input type="text" id="search-tag-input" name="tags" value="{{ Request::get('tags',"") }}" placeholder="@lang('boardlist.search.tags')" />
							</div>
							
							<div class="search-item search-submit">
								<button id="search-submit">@lang('boardlist.search.find')</button>
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
							@foreach ($tags as $tag => $weight)
							<li class="tag-item">
								<a class="tag-link" href="{{ "?tags={$tag}" }}" style="font-size: 100%;">{{ $tag }}</a> 
							</li>
							@endforeach
						</ul>
					</form>
				</aside>
				
				<section class="board-list col grid-80">
					<div class="smooth-box">
						<table class="board-list-table">
							<!--
								If you are adding or removing columns to this file, there's a few steps.
								1. Make sure the data is being supplied by the boards.php/board-search.php file.
								2. If ADDING, please-please-please add a unique class to your cells and specify information in style.css! Don't duplicate class names.
								3. If ADDING, open js/board-directory.js and 'board-datum-xxx' definition that matches your data-column <th> attribute.
								4. Change the colspan="" attributes to be the new total of cells.
							-->
							<thead class="board-list-head">
								<tr>
									<!-- <th class="board-meta" data-column="meta"></th> -->
									<th class="board-uri" data-column="uri"></th>
									<th class="board-title" data-column="title">@lang('boardlist.table.title')</th>
									<th class="board-ppd" data-column="stats_ppd" title="@lang('boardlist.table.ppd_title')">@lang('boardlist.table.ppd')</th>
									<th class="board-plh" data-column="stats_plh" title="@lang('boardlist.table.plh_title')">@lang('boardlist.table.plh')</th>
									<th class="board-unique" data-column="stats_active_users" title="@lang('boardlist.table.active_title')">@lang('boardlist.table.active')</th>
									<th class="board-tags" data-column="tags">@lang('boardlist.table.tags')</th>
									<th class="board-max" data-column="posts_total">@lang('boardlist.table.total_posts')</th>
								</tr>
							</thead>
							
							<tbody class="board-list-tbody">
								@foreach ($boards as $board)
									<tr>
										<!-- <td class="board-meta"> board.locale </td> -->
										<td class="board-uri"><p class="board-cell">
											@include('widgets.boardfav', [ 'board' => $board ])
											<a href="{{ $board->getUrl() }}">/{{ $board->board_uri }}/</a>
											@if ($board->is_worksafe)<i class="fa fa-briefcase board-sfw" title="SFW"></i>@endif
										</p></td>
										<td class="board-title"><p class="board-cell" title="Created board['time']">{{ $board->title }}</p></td>
										<td class="board-ppd"><p class="board-cell board-ppd-desc">{{ $board->stats_ppd }}</p></td>
										<td class="board-plh"><p class="board-cell board-plh-desc">{{ $board->stats_plh }}</p></td>
										<td class="board-unique"><p class="board-cell">{{ $board->stats_active_users }}</p></td>
										<td class="board-tags"><p class="board-cell">@foreach ($board->tags as $tag)<a class="tag-link" href="{{ "?tags={$tag->tag}" }}">{{ $tag->tag }}</a>@endforeach</p></td>
										<td class="board-max"><p class="board-cell">{{ $board->posts_total }}</p></td>
									</tr>
								@endforeach
							</tbody>
							
							<tfoot>
								<tr class="board-list-loading">
									<td colspan="7" class="loading"></td>
								</tr>
								<tr class="board-list-omitted" data-omitted="{{ $boards->total() - $boards->perPage() }}" data-page="{{ $boards->currentPage() }}">
									<td colspan="7">
										<a id="board-list-more" class="{{ $boards->hasPages() ? "board-list-hasmore" : "" }}" href="{{ $boards->currentPage() == $boards->lastPage() ? $boards->url(1) : $boards->nextPageUrl() }}">
											{!! trans('boardlist.footer.displaying', [
												'board_current' => "<span class=\"board-page-num\">{$boards->firstItem()}</span>",
												'board_count'   => "<span class=\"board-page-count\">{$boards->lastItem()}</span>",
												'board_total'   => "<span class=\"board-page-total\">{$boards->total()}</span>",
											]) !!}
											<span class="board-page-loadmore">@lang('boardlist.footer.load_more')</span>
										</a>
									</td>
									
									<!-- if boards_omitted > 0 -->
									<script type="text/javascript">
										/* Cheeky hack redux.
										   We want to show the loadmore for JS users when we have omitted boards.
										   However, the board-directory.js isn't designed to manipulate the page index on immediate load. */
										document.getElementById("board-list-more").className = "board-list-hasmore";
									</script>
									<!-- endif -->
								</tr>
							</tfoot>
						</table>
					</div>
				</section>
			</div>
		</div>
	</div>
</main>
@endsection
