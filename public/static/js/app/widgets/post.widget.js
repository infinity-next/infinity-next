/**
 * Single post widget
 */
ib.widget("post", function(window, $, undefined) {
	var widget = {
		
		// The temporary hover-over item created to show backlink posts.
		$cite    : null,
		citeLoad : null,
		
		// The default values that are set behind init values.
		defaults : {
			classname : {
				'post-hover'  : "post-hover"
			},
			
			// Selectors for finding and binding elements.
			selector : {
				'widget'         : ".post-container",
				
				'mode-reply'     : "main.mode-reply",
				'mode-index'     : "main.mode-index",
				
				'post-reply'     : ".post-reply",
				
				'elementCode'    : "pre code",
				'elementQuote'   : "blockquote",
				
				'cite-slot'      : "li.detail-cites",
				'cite'           : "a.cite-post",
				'backlinks'      : "a.cite-backlink",
				'forwardlink'    : "blockquote.post a.cite-post",
				
				'post-form'      : "#post-form",
				'post-form-body' : "#body",
				
				'attachment'         : "li.post-attachment",
				'attacment-expand'   : "li.post-attachment:not(.attachment-expanded) a.attachment-link",
				'attacment-collapse' : "li.post-attachment.attachment-expanded a.attachment-link",
				'attachment-media'   : "audio.attachment-inline, video.attachment-inline",
				'attachment-image'   : "img.attachment-img",
				'attachment-image-download'   : "img.attachment-type-file",
				'attachment-image-expandable' : "img.attachment-type-img",
				'attachment-image-audio'      : "img.attachment-type-audio",
				'attachment-image-video'      : "img.attachment-type-video",
				'attachment-inline'  : "audio.attachment-inline, video.attachment-inline",
				'attachment-link'    : "a.attachment-link"
			},
			
			template : {
				'backlink' : "<a class=\"cite cite-post cite-backlink\"></a>"
			}
		},
		
		// Helpers
		anchorBoxToLink : function($box, $link) {
			var bodyWidth = document.body.scrollWidth;
			
			var linkRect  = $link[0].getBoundingClientRect();
			
			$(widget.options.classname['post-hover']).remove();
			
			if (!$box.parents().length)
			{
				$box.appendTo("body")
					.addClass(widget.options.classname['post-hover'])
					.css('position', "absolute");
			}
			
			var boxHeight = $box.outerHeight();
			var boxWidth  = $box.outerWidth();
			
			var posTop  = linkRect.top + window.scrollY;
			
			if (posTop + boxHeight > window.scrollY + window.innerHeight)
			{
				// Selects the larger of two values:
				// A) Our position in the scroll, or
				// B) The hidden part of the post subtracted from the top.
				// This check will try to keep the entire post visible,
				// but will always keep the top of the post visible.
				var posTopDiff = (posTop + boxHeight) - (window.scrollY + window.innerHeight);
				posTop = Math.max( window.scrollY, posTop - posTopDiff );
			}
			
			
			// Float to the right.
			// Default for LTR
			var posLeft;
			var posLeftOnRight = linkRect.right + 5;
			var posLeftOnLeft  = linkRect.left  - 5;
			var maxWidth       = (document.body.scrollWidth * 0.7) - 15;
			var newWidth;
			
			// LTR display
			if (ib.ltr)
			{
				// Left side has more space than right side,
				// and box is wider than remaining space.
				if (linkRect.left > document.body.scrollWidth - posLeftOnRight && boxWidth > document.body.scrollWidth - posLeftOnRight)
				{
					posLeft  = posLeftOnLeft;
					newWidth = Math.min(maxWidth, boxWidth, linkRect.left - 15);
					posLeft -= newWidth;
				}
				// Right side has more adequate room,
				// Or box fits in.
				else
				{
					posLeft  = posLeftOnRight;
					newWidth = Math.min(maxWidth, boxWidth, document.body.scrollWidth - posLeftOnRight  - 15);
				}
			}
			else
			{
				// TODO
			}
			
			
			$box.css({
				'top'       : posTop,
				'left'      : posLeft,
				'width'     : newWidth,
			});
			
			widget.$cite = $box;
		},
		
		cachePost : function(jsonOrjQuery) {
			// This stores the post data into a session storage so backlink loading is zippy as heck.
			if (typeof sessionStorage === "object")
			{
				var $post;
				
				if (typeof jsonOrjQuery === "undefined")
				{
					$post = widget.$widget;
				}
				else if (jsonOrjQuery instanceof jQuery)
				{
					$post = jsonOrjQuery;
				}
				else if (jsonOrjQuery.html)
				{
					var $post = $(jsonOrjQuery.html);
				}
				
				// We have to do this even with an item we pulled from AJAX to remove the ID.
				// The HTML dom cannot have duplicate IDs, ever. It's important.
				var $post = $post.clone();
				var id    = $post[0].id;
				$post.removeAttr('id');
				var html = $post[0].outerHTML;
				
				// Attempt to set a new storage item.
				// Destroy older items if we are full.
				var setting = true;
				
				while (setting === true)
				{
					try
					{
						sessionStorage.setItem( id, html );
						break;
					}
					catch (e)
					{
						if (sessionStorage.length > 0)
						{
							sessionStorage.removeItem( sessionStorage.key(0) );
						}
						else
						{
							setting = false;
						}
					}
				}
				
				return $post;
			}
		},
		
		clearCites : function() {
			if (widget.$cite instanceof jQuery)
			{
				widget.$cite.remove();
			}
			
			$(widget.options.classname['post-hover']).remove();
			
			widget.$cite    = null;
			widget.citeLoad = null;
		},
		
		// Events
		events   : {
			attachmentCollapseClick : function(event) {
				var $link   = $(this);
				var $item   = $link.parents("li.post-attachment");
				var $img    = $(widget.options.selector['attachment-image'], $item);
				var $inline = $(widget.options.selector['attachment-inline'], $item);
				
				if ($inline.length > 0)
				{
					$("[src]", $item).attr('src', "");
					$inline[0].pause(0);
					$inline[0].src = "";
					$inline[0].load();
				}
				
				if ($img.is('[data-thumb-width]'))
				{
					$img.css('width', $img.attr('data-thumb-width') + "px");
				}
				
				if ($img.is('[data-thumb-height]'))
				{
					$img.css('height', $img.attr('data-thumb-height') + "px");
				}
				
				$item.removeClass('attachment-expanded');
				$img.attr('src', $link.attr('data-thumb-url'));
				$inline.remove();
				$img.toggle(true);
				$img.parent().css({
					'background-image' : 'none',
					'min-width'        : '',
					'min-height'       : '',
				});
				
				event.preventDefault();
				return false;
			},
			
			attachmentMediaCheck : function(event) {
				var $img  = $(this);
				var $link = $img.parents(widget.options.selector['attachment-link']).first();
				
				// Check for previous results.
				if ($link.is(".attachment-canplay"))
				{
					return true;
				}
				else if ($link.is(".attachment-cannotplay"))
				{
					return false;
				}
				
				// Test audio.
				if ($img.is(widget.options.selector['attachment-image-audio']))
				{
					var $audio  = $("<audio></audio>");
					var mimetype = $img.attr('data-mime');
					var fileext  = $link.attr('href').split('.').pop();
					
					if ($audio[0].canPlayType(mimetype) != "" || $audio[0].canPlayType("audio/"+fileext) != "")
					{
						$link.addClass("attachment-canplay");
						return true;
					}
				}
				// Test video.
				else if ($img.is(widget.options.selector['attachment-image-video']))
				{
					var $video   = $("<video></video>");
					var mimetype = $img.attr('data-mime');
					var fileext  = $link.attr('href').split('.').pop();
					
					if ($video[0].canPlayType(mimetype) || $video[0].canPlayType("video/"+fileext))
					{
						$link.addClass("attachment-canplay");
						return true;
					}
				}
				else
				{
					$link.addClass("attachment-canplay");
					return true;
				}
				
				// Add failure results.
				$link.addClass('attachment-cannotplay');
				
				$img
					.addClass('attachment-type-file')
					.removeClass('attachment-type-video attachment-type-audio');
				
				return false;
			},
			
			attachmentMediaEnded : function(event) {
				var $media  = $(this);
				var $item   = $media.parents("li.post-attachment");
				var $link   = $(widget.options.selector['attachment-link'], $item);
				var $img    = $(widget.options.selector['attachment-image'], $item);
				var $inline = $(widget.options.selector['attachment-inline'], $item);
				
				$item.removeClass('attachment-expanded');
				$img.attr('src', $link.attr('data-thumb-url'));
				$inline.remove();
				$img.toggle(true);
				$img.parent().addClass('attachment-grow');
			},
			
			attachmentExpandClick : function(event) {
				var $link = $(this);
				var $item = $link.parents("li.post-attachment");
				var $img  = $(widget.options.selector['attachment-image'], $link);
				
				// We don't do anything if the user is CTRL+Clicking,
				// or if the file is a download type.
				if (event.altKey || event.ctrlKey || $img.is(widget.options.selector['attachment-image-download']))
				{
					return true;
				}
				
				// If the attachment type is not an image, we can't expand inline.
				if ($img.is(widget.options.selector['attachment-image-expandable']))
				{
					$item.addClass('attachment-expanded');
					$img.parent().css({
							// Removed because the effect was gross.
							// 'background-image'    : 'url(' + $link.attr('data-thumb-url') + ')',
							// 'background-size'     : '100%',
							// 'background-repeat'   : 'no-repeat',
							// 'background-position' : 'center center',
							'min-width'           : $img.width() + 'px',
							'min-height'          : $img.height() + 'px',
							'opacity'             : 0.5,
						});
					
					// Clear source first so that lodaing works correctly.
					$img
						.attr('data-thumb-width', $img.width())
						.attr('data-thumb-height', $img.height())
						.attr('src', "")
						.css({
							'width'  : "auto",
							'height' : "auto"
						});
					
					$img
						// Change the source of our thumb to the full image.
						.attr('src', $link.attr('data-download-url'))
						// Bind an event to handle the image loading.
						.one("load", function() {
							// Remove our opacity change.
							$(this).parent().css({
								// 'background-image' : "none",
								// 'min-width'        : '',
								// 'min-height'       : '',
								'opacity'          : ""
							});
						});
					
					event.preventDefault();
					return false;
				}
				else if ($img.is(widget.options.selector['attachment-image-audio']))
				{
					var $audio  = $("<audio controls autoplay class=\"attachment-inline attachment-audio\"></audio>");
					var $source = $("<source />");
					var mimetype = $img.attr('data-mime');
					var fileext  = $link.attr('href').split('.').pop();
					
					if ($audio[0].canPlayType(mimetype) || $audio[0].canPlayType("audio/"+fileext))
					{
						$item.addClass('attachment-expanded');
						
						$source
							.attr('src',  $link.attr('href'))
							.attr('type', $img.attr('data-mime'))
							.one('error', function(event) {
								// Our source has failed to load!
								// Trigger a download.
								$img
									.trigger('click')
									.removeClass('attachment-type-audio')
									.addClass('attachment-type-file');
							})
							.appendTo($audio);
						
						$audio.insertBefore($link);
						widget.bind.mediaEvents($audio);
						
						$audio.parent().addClass('attachment-grow');
						
						event.preventDefault();
						return false;
					}
				}
				else if ($img.is(widget.options.selector['attachment-image-video']))
				{
					var $video   = $("<video controls autoplay class=\"attachment-inline attachment-video\"></video>");
					var $source  = $("<source />");
					var mimetype = $img.attr('data-mime');
					var fileext  = $link.attr('href').split('.').pop();
					
					if ($video[0].canPlayType(mimetype) || $video[0].canPlayType("video/"+fileext))
					{
						$item.addClass('attachment-expanded');
						
						$source
							.attr('src',  $link.attr('href'))
							.attr('type', $img.attr('data-mime'))
							.one('error', function(event) {
								// Our source has failed to load!
								// Trigger a download.
								$img
									.trigger('click')
									.removeClass('attachment-type-video')
									.addClass('attachment-type-download attachment-type-failed');
							})
							.appendTo($video);
						
						$img.toggle(false);
						
						widget.bind.mediaEvents($video);
						$video.insertBefore($link);
						
						event.preventDefault();
						return false;
					}
					else
					{
						$img
							.addClass('attachment-type-file')
							.removeClass('attachment-type-video');
						
						return true;
					}
				}
				else
				{
					return true;
				}
			},
			
			citeClick : function(event) {
				if (event.altKey || event.ctrlKey)
				{
					return true;
				}
				
				var $cite     = $(this);
				var board_uri = $cite.attr('data-board_uri');
				var board_id  = parseInt($cite.attr('data-board_id'), 10);
				var $target   = $("#post-"+board_uri+"-"+board_id);
				
				if ($target.length)
				{
					window.location.hash = board_id;
					$target[0].scrollIntoView();
					
					event.preventDefault();
					return false;
				}
			},
			
			citeMouseOver : function(event) {
				widget.clearCites();
				
				var $cite     = $(this);
				var board_uri = $cite.attr('data-board_uri');
				var board_id  = parseInt($cite.attr('data-board_id'), 10);
				var post_id   = "post-"+board_uri+"-"+board_id;
				var $post;
				
				if (widget.citeLoad == post_id)
				{
					return true;
				}
				
				// Loads session storage for our post if it exists.
				if (typeof sessionStorage === "object")
				{
					$post = $(sessionStorage.getItem( post_id ));
					
					if ($post instanceof jQuery && $post.length)
					{
						widget.anchorBoxToLink($post, $cite);
						return true;
					}
				}
				
				widget.citeLoad = post_id;
				
				jQuery.ajax({
					type:        "GET",
					url:         "/"+board_uri+"/post/"+board_id+".json",
					contentType: "application/json; charset=utf-8"
				}).done(function(response, textStatus, jqXHR) {
					$post = widget.cachePost(response);
					
					if (widget.citeLoad === post_id)
					{
						widget.anchorBoxToLink($post, $cite);
					}
				});
			},
			
			citeMouseOut : function(event) {
				widget.clearCites();
			},
			
			codeHighlight : function() {
				// Activate code highlighting if the JS module is enabled.
				if (typeof hljs === "object")
				{
					$(widget.defaults.selector.elementCode, widget.$widget).each(function(index, element) {
						hljs.highlightBlock(element);
					});
				}
			},
			
			postClick : function(event) {
				if ($(widget.options.selector['mode-reply']).length !== 0)
				{
					event.preventDefault();
					
					var $this = $(this);
					var $body = $(widget.options.selector['post-form-body']);
					
					$body
						.val($body.val() + ">>" + $this.data('board_id') + "\n")
						.focus();
					
					return false;
				}
				
				return true;
			},
			
			threadNewPosts : function(event, posts) {
				// Data of our widget, the item we are hoping to insert new citations into.
				var $detail          = $(widget.options.selector['cite-slot'], widget.$widget);
				var $backlinks       = $detail.children();
				var widget_board_uri = widget.$widget.attr('data-board_uri');
				var widget_board_id  = widget.$widget.attr('data-board_id');
				
				// All new updates show off their posts in three difeferent groups.
				jQuery.each(posts, function(index, group) {
					// Each group can have many jQuery dom elements.
					jQuery.each(group, function(index, $post) {
						// This information belongs to a new post.
						var $container     = $post.find("[data-board_uri][data-board_id]:first");
						var post_board_uri = $container.attr('data-board_uri');
						var post_board_id  = $container.attr('data-board_id');
						var $cites         = $(widget.options.selector['forwardlink'], $container);
						
						// Each post may have many citations.
						$cites.each(function(index) {
							// This information represents the post we are citing.
							var $cite          = $(this);
							var cite_board_uri = $cite.attr('data-board_uri');
							var cite_board_id  = $cite.attr('data-board_id');
							
							// If it doesn't belong to our widget, we don't want it.
							if (cite_board_uri == widget_board_uri && cite_board_id == widget_board_id)
							{
								var $target    = $("#post-" + cite_board_uri + "-" + post_board_id);
								
								if (!$backlinks.filter("[data-board_uri="+post_board_uri+"][data-board_id="+post_board_id+"]").length)
								{
									var $backlink = $(widget.options.template['backlink'])
										.attr('data-board_uri', post_board_uri)
										.data('board_uri', post_board_uri)
										.attr('data-board_id', post_board_id)
										.data('board_id', post_board_id)
										.attr('href', "/" + post_board_uri + "/post/" + post_board_id)
										.appendTo($detail);
									
									// Believe it or not this is actually important.
									// it adds a space after each item.
									$detail.append("\n");
									
									if (post_board_uri == window.app.board)
									{
										$backlink.addClass('cite-local').html("&gt;&gt;" + post_board_id);
									}
									else
									{
										$backlink.addClass('cite-remote').html("&gt;&gt;&gt;/" + post_board_uri + "/" + post_board_id);
									}
								}
							}
						});
					});
				});
			}
		},
		
		// Event bindings
		bind     : {
			mediaEvents : function($element) {
				$element
					.on('ended.ib-post', widget.events.attachmentMediaEnded)
				;
			},
			
			widget : function() {
				
				widget.events.codeHighlight();
				
				$(window)
					.on('au-updated.ib-post', widget.events.threadNewPosts)
				;
				
				widget.$widget
					.on('click.ib-post',       widget.options.selector['post-reply'],         widget.events.postClick)
					.on('media-check.ib-post', widget.options.selector['attachment-image'],   widget.events.attachmentMediaCheck)
					.on('click.ib-post',       widget.options.selector['attacment-expand'],   widget.events.attachmentExpandClick)
					.on('click.ib-post',       widget.options.selector['attacment-collapse'], widget.events.attachmentCollapseClick)
					
					// Citations
					.on('click.ib-post',       widget.options.selector['cite'], widget.events.citeClick)
					.on('mouseover.ib-post',   widget.options.selector['cite'], widget.events.citeMouseOver)
					.on('mouseout.ib-post',    widget.options.selector['cite'], widget.events.citeMouseOut)
				;
				
				widget.cachePost();
			}
		},
		
	};
	
	return widget;
});
