/**
 * Single post widget
 */
ib.widget("post", function(window, $, undefined) {
	var widget = {
		
		// The default values that are set behind init values.
		defaults : {
			// Selectors for finding and binding elements.
			selector : {
				'widget'         : ".post-container",
				
				'mode-reply'     : "main.mode-reply",
				'mode-index'     : "main.mode-index",
				
				'post-reply'     : ".post-reply",
				
				'elementCode'    : "pre code",
				'elementQuote'   : "blockquote",
				
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
		},
		
		// Events
		events   : {
			attachmentCollapseClick : function(event) {
				if(event.altKey || event.shiftKey || event.ctrlKey)
				{
					return true;
				}
				
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
				if (event.ctrlKey || $img.is(widget.options.selector['attachment-image-download']))
				{
					return true;
				}
				
				// If the attachment type is not an image, we can't expand inline.
				if ($img.is(widget.options.selector['attachment-image-expandable']))
				{
					$item.addClass('attachment-expanded');
					$img.parent().css({
							'background-image'    : 'url(' + $link.attr('data-thumb-url') + ')',
							'background-size'     : '100%',
							'background-repeat'   : 'no-repeat',
							'background-position' : 'center center',
							'min-width'           : $img.width() + 'px',
							'min-height'          : $img.height() + 'px',
							'opacity'             : 0.5,
						});
					
					$img
						// Bind an event to handle the image loading.
						.one("load", function() {
							// Remove our opacity change.
							$(this).parent().css({
								'background-image' : "none",
								'min-width'        : '',
								'min-height'       : '',
								'opacity'          : ""
							});
						})
						// Finally change the source of our thumb to the full image.
						.attr('src', $link.attr('data-download-url'));
					
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
				
				widget.$widget
					.on('click.ib-post',       widget.options.selector['post-reply'],         widget.events.postClick)
					.on('media-check.ib-post', widget.options.selector['attachment-image'],   widget.events.attachmentMediaCheck)
					.on('click.ib-post',       widget.options.selector['attacment-expand'],   widget.events.attachmentExpandClick)
					.on('click.ib-post',       widget.options.selector['attacment-collapse'], widget.events.attachmentCollapseClick)
				;
			}
		},
		
	};
	
	return widget;
});
