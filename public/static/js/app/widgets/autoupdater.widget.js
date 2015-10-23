/**
 * Autoupdater widget
 */
ib.widget("autoupdater", function(window, $, undefined) {
	
	var widget = {
		
		// The default values that are set behind init values.
		defaults : {
			// Selectors for finding and binding elements.
			selector : {
				'widget'         : "#autoupdater",
				
				'enabled'        : "#autoupdater-enabled",
				'timer'          : "#autoupdater-timer",
				'force-update'   : "#autoupdater-update",
				'updating'       : "#autoupdater-updating",
			},
		},
		
		updating    : false,
		updateTimer : false,
		updateURL   : false,
		updateAsked : false,
		updateLast  : parseInt(parseInt(Date.now(), 10) / 1000, 10),
		
		// Events
		events   : {
			
			update : function() {
				if (!widget.updating)
				{
					$(widget.options.selector['force-update'])
						.hide();
					$(widget.options.selector['updating'])
						.show();
					
					clearInterval(widget.updateTimer);
					
					$.ajax(widget.updateURL, {
						data : {
							'updatesOnly'  : 1,
							'updateHtml'   : 1,
							'updatedSince' : widget.updateLast,
						}
					})
						.done(widget.events.updateSuccess)
						.always(widget.events.updateComplete);
					
					widget.updating    = true;
					widget.updateTimer = false;
					widget.updateAsked = parseInt(parseInt(Date.now(), 10) / 1000, 10);
				}
			},
			
			updateSuccess : function(data, textStatus, jqXHR, scrollIntoView) {
				var $newPost = $();
				
				if (data instanceof Array)
				{
					widget.updateLast = widget.updateAsked;
					
					$.each(data, function(index, reply)
					{
						var $existingPost = $(".post-" + reply.post_id);
						
						if ($existingPost.length > 0)
						{
							if (reply.html !== null)
							{
								$newPost      = $(reply.html);
								
								var existingUpdated = parseInt($existingPost.attr('data-updated-at'), 10),
									newUpdated      = parseInt($newPost.attr('data-updated-at'), 10);
								
								if (isNaN(existingUpdated) || isNaN(newUpdated) || (newUpdated > existingUpdated))
								{
									$existingPost.replaceWith($newPost);
									ib.bindElement($newPost[0]);
								}
							}
							else
							{
								$existingPost.addClass('post-deleted');
								return $existingPost;
							}
						}
						else if(reply.html !== null)
						{
							$newPost = $("<li class=\"thread-reply\"><article class=\"reply\">"+reply.html+"</article></li>");
							$newPost.insertBefore(widget.$widget);
							ib.bindAll($newPost);
							
							if (scrollIntoView === true)
							{
								if (typeof $newPost[0].scrollIntoView !== "undefined")
								{
									$newPost[0].scrollIntoView({
										behavior : "smooth",
										block    : "end"
									});
								}
								else if (typeof $newPost[0].scrollIntoViewIfNeeded !== "undefined")
								{
									$newPost[0].scrollIntoViewIfNeeded({
										behavior : "smooth",
										block    : "end"
									});
								}
							}
						}
					});
				}
				
				return $newPost;
			},
			
			updateComplete : function() {
				widget.updating = false;
				
				$(widget.options.selector['force-update'])
					.show();
				$(widget.options.selector['updating'])
					.hide();
				
				clearInterval(widget.updateTimer);
				widget.updateTimer = setInterval(widget.events.updateInterval, 1000);
			},
			
			updateInterval : function() {
				if ($(widget.options.selector['enabled']).is(":checked"))
				{
					var $timer = $(widget.options.selector['timer'], widget.$widget);
					var time   = parseInt($timer.attr('data-time'), 10);
					
					if (isNaN(time))
					{
						time = 10;
					}
					else
					{
						--time;
						
						if (time <= 0)
						{
							time = 10;
							
							widget.$widget.trigger('au-update');
						}
					}
					
					$timer
						.text(time+'s')
						.attr('data-time', time);
				}
				
				clearInterval(widget.updateTimer);
				widget.updateTimer = setInterval(widget.events.updateInterval, 1000);
			},
			
			updaterUpdateClick : function(event) {
				var $timer = $(widget.options.selector['timer'], widget.$widget);
				
				$timer.attr('data-time', 10);
				widget.events.update();
				
				event.preventDefault();
				return false;
			}
			
		},
		
		// Event bindings
		bind     : {
			timer  : function() {
				var url   = widget.$widget.data('url');
				
				if (url)
				{
					widget.updateURL = url;
					widget.$widget.show();
					
					clearInterval(widget.updateTimer);
					widget.updateTimer = setInterval(widget.events.updateInterval, 1000);
				}
			},
			
			widget : function() {
				
				$(widget.options.selector['force-update'])
					.show();
				$(widget.options.selector['updating'])
					.hide();
				
				widget.$widget
					.on('au-update',   widget.events.update)
					.on('click.ib-au', widget.options.selector['force-update'], widget.events.updaterUpdateClick)
				;
				
				widget.bind.timer();
			}
		}
	};
	
	return widget;
})
