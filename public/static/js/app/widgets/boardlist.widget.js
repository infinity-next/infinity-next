// ============================================================
// Purpose                      : Board directory handling
// Contributors                 : jaw-sh
// ============================================================

ib.widget("boardlist", function(window, $, undefined) {
	var widget = {
		
		// The default values that are set behind init values.
		defaults : {
			
			// Selectors for finding and binding elements.
			selector   : {
				'board-head'    : ".board-list-head",
				'board-body'    : ".board-list-tbody",
				'board-loading' : ".board-list-loading",
				'board-omitted' : ".board-list-omitted",
				
				'search'        : "#search-form",
				'search-lang'   : "#search-lang-input",
				'search-sfw'    : "#search-sfw-input",
				'search-tag'    : "#search-tag-input",
				'search-title'  : "#search-title-input",
				'search-submit' : "#search-submit",
				
				'tag-list'      : ".tag-list",
				'tag-link'      : ".tag-link",
				
				'footer-page'   : ".board-page-num",
				'footer-count'  : ".board-page-count",
				'footer-total'  : ".board-page-total",
				'footer-more'   : "#board-list-more"
			},
			
			// HTML Templates for dynamic construction
			templates   : {
				// Board row item
				'board-row'          : "<tr></tr>",
				
				// Individual cell definitions
				'board-cell-meta'    : "<td class=\"board-meta\"></td>",
				'board-cell-uri'     : "<td class=\"board-uri\"></td>",
				'board-cell-title'   : "<td class=\"board-title\"></td>",
				'board-cell-pph'     : "<td class=\"board-pph\"></td>",
				'board-cell-posts_total' : "<td class=\"board-max\"></td>",
				'board-cell-active'  : "<td class=\"board-unique\"></td>",
				'board-cell-tags'    : "<td class=\"board-tags\"></td>",
				
				// Content wrapper
				// Used to help constrain contents to their <td>.
				'board-content-wrap' : "<p class=\"board-cell\"></p>",
				
				// Individual items or parts of a single table cell.
				'board-datum-lang'   : "<span class=\"board-lang\"></span>",
				'board-datum-uri'    : "<a class=\"board-link\"></a>",
				'board-datum-sfw'    : "<i class=\"fa fa-briefcase board-sfw\" title=\"SFW\"></i>",
				'board-datum-nsfw'   : "<i class=\"fa fa-briefcase board-nsfw\" title=\"NSFW\"></i>",
				'board-datum-tags'   : "<a class=\"tag-link\" href=\"#\"></a>",
				'board-datum-pph'    : "<p class=\"board-cell board-pph-desc\" title=\"%1 made in the last hour, %2 on average\"></p>",
				
				
				// Tag list.
				'tag-list'           : "<ul class=\"tag-list\"></ul>",
				'tag-item'           : "<li class=\"tag-item\"></li>",
				'tag-link'           : "<a class=\"tag-link\" href=\"#\"></a>"
			}
		},
		
		lastSearch : {},
		
		bind : {
			form : function() {
				var selectors = widget.options.selector;
				
				var $search       = $( selectors['search'] );
				var $searchLang   = $( selectors['search-lang'] );
				var $searchSfw    = $( selectors['search-sfw'] );
				var $searchTag    = $( selectors['search-tag'] );
				var $searchTitle  = $( selectors['search-title'] );
				var $searchSubmit = $( selectors['search-submit'] );
				
				var searchForms   = {
						'boardlist'    : widget.$widget,
						'search'       : $search,
						'searchLang'   : $searchLang,
						'searchSfw'    : $searchSfw,
						'searchTag'    : $searchTag,
						'searchTitle'  : $searchTitle,
						'searchSubmit' : $searchSubmit
					};
				
				if ($search.length > 0)
				{
					// Bind form events.
					widget.$widget
						// Load more
						.on( 'click', selectors['board-omitted'], searchForms, widget.events.loadMore )
						// Tag click
						.on( 'click', selectors['tag-link'], searchForms, widget.events.tagClick )
						// Form Submission
						.on( 'submit', selectors['search'], searchForms, widget.events.searchSubmit )
						// Submit click
						.on( 'click', selectors['search-submit'], searchForms, widget.events.searchSubmit );
					
					$(window)
						.on( 'hashchange', searchForms, widget.events.hashChange );
					
					$searchSubmit.prop( 'disabled', false );
				}
			}
		},
		
		build  : {
			boardlist : function(data) {
				widget.build.boards(data['boards'], data['order']);
				widget.build.lastSearch(data['search']);
				widget.build.footer(data);
				widget.build.tags(data['tagWeight']);
				
			},
			
			boards : function(boards, order) {
				// Find our head, columns, and body.
				var $head = $( widget.options.selector['board-head'], widget.$widget );
				var $cols = $("[data-column]", $head );
				var $body = $( widget.options.selector['board-body'], widget.$widget );
				
				$.each( order, function( index, uri ) {
					var row  = boards[uri];
					var $row = $( widget.options.template['board-row'] );
					
					$cols.each( function( index, col ) {
						widget.build.board( row, col ).appendTo( $row );
					} );
					
					$row.appendTo( $body );
				} );
				
			},
			board : function(row, col) {
				var $col   = $(col);
				var column = $col.attr('data-column');
				var value  = row[column];
				var $cell  = $( widget.options.template['board-cell-' + column] );
				var $wrap  = $( widget.options.template['board-content-wrap'] );
				
				if (typeof widget.build.boardcell[column] === "undefined")
				{
					if (value instanceof Array)
					{
						if (typeof widget.options.template['board-datum-' + column] !== "undefined")
						{
							$.each( value, function( index, singleValue )
							{
								$( widget.options.template['board-datum-' + column] )
									.text( singleValue )
									.appendTo( $wrap );
							} );
						}
						else
						{
							$wrap.text( value.join(" ") );
						}
					}
					else
					{
						$wrap.text( value );
					}
				}
				else
				{
					var $content = widget.build.boardcell[column]( row, value );
					
					if ($content instanceof jQuery)
					{
						if ($content.is("." + $wrap[0].class)) {
							// Our new content has the same classes as the wrapper.
							// Replace the old wrapper.
							$wrap = $content;
						}
						else
						{
							// We use .append() instead of .appendTo() as we do elsewhere
							// because $content can be multiple elements.
							$wrap.append( $content );
						}
					}
					else if (typeof $content === "string")
					{
						$wrap.html( $content );
					}
					else
					{
						console.log("Special cell constructor returned a " + (typeof $content) + " that board-directory.js cannot interpret.");
					}
				}
				
				$wrap.appendTo( $cell );
				return $cell;
			},
			boardcell : {
				'meta' : function(row, value) {
					return $( widget.options.template['board-datum-lang'] ).text( row['locale'] );
				},
				'uri'  : function(row, value) {
					var $link = $( widget.options.template['board-datum-uri'] );
					var $sfw  = $( widget.options.template['board-datum-' + (row['sfw'] == 1 ? "sfw" : "nsfw")] );
					
					$link
						.attr( 'href', "/"+row['uri']+"/" )
						.text( "/"+row['uri']+"/" );
					
					// I decided against NSFW icons because it clutters the index.
					// Blue briefcase = SFW. No briefcase = NSFW. Seems better.
					if (row['sfw'] == 1) {
						return $link[0].outerHTML + $sfw[0].outerHTML;
					}
					else {
						return $link[0].outerHTML;
					}
				},
				'pph' : function(row, value) {
					return $( widget.options.template['board-datum-pph'] )
						.attr( 'title', function(index, value) {
							return value.replace("%1", row['pph']).replace("%2", row['pph_average']);
						} )
						.text( row['pph'] );
				},
			},
			
			lastSearch : function(search) {
				return widget.lastSearch =  { 
					'lang'  : search.lang === false ? "" : search.lang,
					'page'  : search.page,
					'tags'  : search.tags === false ? "" : search.tags.join(" "),
					'time'  : search.time,
					'title' : search.title === false ? "" : search.title,
					'sfw'   : search.nsfw ? 0 : 1
				};
			},
			
			footer : function(data) {
				var selector = widget.options.selector;
				var $page    = $( selector['footer-page'], widget.$widget );
				var $count   = $( selector['footer-count'], widget.$widget );
				var $total   = $( selector['footer-total'], widget.$widget );
				var $more    = $( selector['footer-more'], widget.$widget );
				var $omitted = $( selector['board-omitted'], widget.$widget );
				
				var boards   = Object.keys(data['boards']).length;
				var omitted  = data['omitted'] - data['search']['page'];
				
				if (omitted < 0)
				{
					omitted = 0;
				}
				
				var total    = boards + omitted + data['search']['page'];
				
				//$page.text( data['search']['page'] );
				$count.text( data['search']['page'] + boards );
				$total.text( total );
				$more.toggleClass( "board-list-hasmore", omitted != 0 );
				$omitted.toggle( boards + omitted > 0 );
			},
			
			tags : function(tags) {
				var selector = widget.options.selector;
				var template = widget.options.template;
				var $list    = $( selector['tag-list'], widget.$widget );
				
				if ($list.length)
				{
					$.each( tags, function(tag, weight) {
						var $item = $( template['tag-item'] );
						var $link = $( template['tag-link'] );
						
						$link
							.css( 'font-size', weight+"%" )
							.text( tag )
							.appendTo( $item );
						
						$item.appendTo( $list );
					} );
				}
			}
		},
		
		events : {
			loadMore : function(event) {
				var parameters = $.extend( {}, widget.lastSearch );
				
				parameters.page = $( widget.options.selector['board-body'], widget.$widget ).children().length;
				
				widget.submit( parameters );
			},
			
			hashChange : function(event) {
				if (window.location.hash != "")
				{
					// Turns "#porn,tits" into "porn tits" for easier search results.
					var tags = window.location.hash.substr(1, window.location.hash.length).split(",");
					var hash = tags.join(" ");
				}
				else
				{
					var tags = [];
					var hash = "";
				}
				
				$( widget.options.selector['search-tag'], widget.$widget ).val( hash );
				$( widget.options.selector['tag-list'], widget.$widget ).html("");
				$( widget.options.selector['board-body'], widget.$widget ).html("");
				
				widget.submit( { 'tags' : tags } );
				
				return true;
			},
			
			searchSubmit : function(event) {
				event.preventDefault();
				
				$( widget.options.selector['tag-list'], widget.$widget ).html("");
				$( widget.options.selector['board-body'], widget.$widget ).html("");
				
				widget.submit( { 
					'lang'  : event.data.searchLang.val(),
					'tags'  : event.data.searchTag.val(),
					'title' : event.data.searchTitle.val(),
					'sfw'   : event.data.searchSfw.prop('checked') ? 1 : 0
				} );
				
				return false;
			},
			
			tagClick : function(event) {
				event.preventDefault();
				
				var $this  = $(this),
					$input = $( widget.options.selector['search-tag'] );
				
				$input
					.val( ( $input.val() + " " + $this.text() ).replace(/\s+/g, " ").trim() )
					.trigger( 'change' )
					.focus();
				
				return false;
			}
		},
		
		submit : function( parameters ) {
			var $boardlist    = widget.$widget;
			var $boardload    = $( widget.options.selector['board-loading'], $boardlist );
			var $searchSubmit = $( widget.options.selector['search-submit'], $boardlist );
			var $footerMore   = $( widget.options.selector['board-omitted'], $boardlist );
			
			$searchSubmit.prop( 'disabled', true );
			$boardload.show();
			$footerMore.hide();
			
			return $.get(
				"/board-search.php",
				parameters,
				function(data) {
					$searchSubmit.prop( 'disabled', false );
					$boardload.hide();
					
					widget.build.boardlist( $.parseJSON(data) );
				}
			);
		},
		
		init : function( target ) {
			if (typeof target !== "string")
			{
				target = widget.options.selector.boardlist;
			}
			
			// Parse ?GET parameters into lastSearch object.
			if (window.location.search != "" && window.location.search.length > 0)
			{
				// ?a=1&b=2 -> a=1&b=2 -> { a : 1, b : 2 }
				window.location.search.substr(1).split("&").forEach( function(item) {
					widget.lastSearch[item.split("=")[0]] = item.split("=")[1];
				} );
			}
			
			var $boardlist = $(target);
			
			if ($widget.length > 0 )
			{
				$( widget.options.selector['board-loading'], $boardlist ).hide();
				
				widget.$widget = $boardlist;
				widget.bind.form();
				
				if (window.location.hash != "")
				{
					$(window).trigger( 'hashchange' );
				}
			}
		}
	};
	
	return widget;
} );