/**
 * Autoupdater widget
 */
ib.widget("autoupdater", function(window, $, undefined) {
	
	var widget = {
		
		// The default values that are set behind init values.
		defaults : {
			
			classname : {
				'last-reply' : "thread-last-reply"
			},
			
			// Selectors for finding and binding elements.
			selector : {
				'widget'         : "#autoupdater",
				
				'enabled'        : "#autoupdater-enabled",
				'timer'          : "#autoupdater-timer",
				'force-update'   : "#autoupdater-update",
				'updating'       : "#autoupdater-updating",
				
				'cite'           : "a.cite-post",
				
				'thread-event-target' : ".thread:first",
				'thread-reply'        : ".thread-reply"
			},
		},
		
		// Update tracking
		updating     : false,
		updateTimer  : false,
		updateURL    : false,
		updateAsked  : false,
		updateLast   : false,
		updateMisses : 0,
		
		// Keeps track of what our last post was before we focused the window.
		$lastPost    : null,
		hasFocus     : false,
		newReplies   : 0,
		
		// Keeps track of if we're scrolled to the bottom.
		scrollLocked : false,
		
		// Helpers
		getTimeFromPost : function($post) {
			var $container = $post;
			
			if (!$container.is(".post-container"))
			{
				$container = $post.find(".post-container:first");
			}
			
			if (!$container.length)
			{
				return false;
			}
			
			
			var times = [0];
			
			if ($container.is("[data-created-at]"))
			{
				var createdAt = parseInt($container.attr('data-created-at'), 10);
				
				if (!isNaN(createdAt))
				{
					times.push(createdAt);
				}
			}
			
			if ($container.is("[data-deleted-at]"))
			{
				var deletedAt = parseInt($container.attr('data-deleted-at'), 10);
				
				if (!isNaN(deletedAt))
				{
					times.push(deletedAt);
				}
			}
			
			if ($container.is("[data-updated-at]"))
			{
				var updatedAt = parseInt($container.attr('data-updated-at'), 10);
				
				if (!isNaN(updatedAt))
				{
					times.push(updatedAt);
				}
			}
			
			return Math.max.apply(Math, times);
		},
		
		addYouPost : function(uri, id) {
			if (typeof window.localStorage !== "object")
			{
				return [];
			}
			
			try
			{
				var storage = localStorage.getItem("yourPosts."+uri).split(",");
			}
			catch (e)
			{
				var storage = [];
			}
			
			storage.push(id);
			storage = storage.filter(function(index, item, array) {
				return array.lastIndexOf(index) === item;
			});
			
			localStorage.setItem("yourPosts."+uri, storage.join(","));
		},
		
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
							'messenger'    : 1
						}
					})
						.done(widget.events.updateSuccess)
						.always(widget.events.updateComplete);
					
					widget.updating    = true;
					widget.updateTimer = false;
					widget.updateAsked = parseInt(parseInt(Date.now(), 10) / 1000, 10);
				}
			},
			
			updateLastReply : function() {
				// This corrects which post has the last reply class.
				if (widget.$lastPost !== null)
				{
					widget.$widget
						.siblings("." + widget.options.classname['last-reply'])
						.removeClass(widget.options.classname['last-reply']);
					
					// If we have replies after this, add the border.
					if (widget.$lastPost.next(widget.options.selector['thread-reply']).length)
					{
						widget.$lastPost
							.addClass(widget.options.classname['last-reply']);
					}
				}
				
				// This corrects our favicon.
				if (widget.newReplies > 0)
				{
					$("#favicon").attr('href', window.app.favicon.alert);
					document.title = "(" + widget.newReplies + ") " + window.app.title;
				}
				else
				{
					$("#favicon").attr('href', window.app.favicon.normal);
					document.title = window.app.title;
				}
			},
			
			updateSuccess : function(json, textStatus, jqXHR, scrollIntoView) {
				var newPosts     = $();
				var updatedPosts = $();
				var deletedPosts = $();
				var postData     = json;
				
				// This important event fire ensures that sibling data is intercepted.
				if (json.messenger)
				{
					postData = json.data;
					$(window).trigger('messenger', json);
				}
				
				if (postData instanceof Array)
				{
					$.each(postData, function(index, reply)
					{
						var $existingPost = $(".post-container[data-post_id=" + reply.post_id+"]");
						
						if ($existingPost.length > 0)
						{
							if (reply.html !== null)
							{
								$newPost      = $(reply.html);
								
								var existingUpdated = parseInt($existingPost.attr('data-updated-at'), 10),
									newUpdated      = parseInt($newPost.attr('data-updated-at'), 10);
								
								if (isNaN(existingUpdated) || isNaN(newUpdated) || (newUpdated > existingUpdated))
								{
									console.log("Autoupdater: Replacing " + reply.post_id);
									
									$existingPost.replaceWith($newPost);
									ib.bindElement($newPost[0]);
									
									updatedPosts.push($newPost);
									
									widget.updateLast = Math.max(widget.updateLast, widget.getTimeFromPost($newPost));
									
									return true;
								}
							}
							else
							{
								console.log("Autoupdater: Deleting " + reply.post_id);
								
								$existingPost.addClass('post-deleted');
								
								updatedPosts.push($existingPost);
								deletedPosts.push($existingPost);
								
								widget.updateLast = Math.max(widget.updateLast, widget.getTimeFromPost($existingPost));
								
								return true;
							}
						}
						else if(reply.html !== null)
						{
							console.log("Autoupdater: Inserting " + reply.post_id);
							
							$newPost = $("<li class=\"thread-reply\"><article class=\"reply\">"+reply.html+"</article></li>");
							$newPost.insertBefore(widget.$widget);
							ib.bindAll($newPost);
							
							newPosts.push($newPost);
							
							widget.updateLast = Math.max(widget.updateLast, widget.getTimeFromPost($newPost));
							
							// Push this ID into our You lists if we made it.
							if (reply.recently_created)
							{
								widget.addYouPost(reply.board_uri, reply.board_id);
							}
							
							// Used primarily by postbox updates to force the scroll to see our new post.
							if (scrollIntoView === true)
							{
								if (typeof $newPost[0].scrollIntoViewIfNeeded !== "undefined")
								{
									$newPost[0].scrollIntoViewIfNeeded({
										behavior : "smooth",
										block    : "end"
									});
								}
								else if (typeof $newPost[0].scrollIntoView !== "undefined")
								{
									$newPost[0].scrollIntoView({
										behavior : "smooth",
										block    : "end"
									});
								}
							}
							
							return true;
						}
					});
				}
				
				if (newPosts.length)
				{
					if (!widget.hasFocus)
					{
						widget.newReplies += newPosts.length;
					}
					else if (widget.scrollLocked)
					{
						window.scrollTo(0, document.body.scrollHeight);
					}
					
					widget.updateMisses = 0;
				}
				else
				{
					++widget.updateMisses;
				}
				
				$(window).trigger('au-updated', [{
					'newPosts'     : newPosts,
					'updatedPosts' : updatedPosts,
					'deletedPosts' : deletedPosts,
				}]);
				
				widget.events.updateLastReply();
				
				return false;
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
						time = 0;
					}
					
					--time;
					
					if (time <= 0)
					{
						time = (widget.hasFocus ? widget.updateMisses * 2 : Math.pow(widget.updateMisses, 1.5)) + 3;
						time = parseInt( Math.min(time, 30), 10);
						
						widget.$widget.trigger('au-update');
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
				
				widget.updateMisses = 0;
				$timer.attr('data-time', 5);
				widget.events.update();
				
				event.preventDefault();
				return false;
			},
			
			windowFocus    : function(event) {
				widget.hasFocus   = true;
				widget.$lastPost  = null;
				widget.newReplies = 0;
				
				document.title = window.app.title;
				$("#favicon").attr('href', window.app.favicon.normal);
			},
			
			windowUnfocus  : function(event) {
				// Sets our last seen post to the post immediately above the widget.
				widget.$lastPost    = widget.$widget.prev();
				widget.$lastPost    = widget.$lastPost.length ? widget.$lastPost : null;
				widget.hasFocus     = false;
				widget.scrollLocked = false;
			},
			
			windowScroll   : function(event) {
				// Determine if the boundaries of this image are in the viewport.
				
				var $elem         = widget.$widget;
				var $window       = $(window);
				
				var docViewTop    = $window.scrollTop();
				var docViewBottom = docViewTop + $window.height();
				
				var viewPad = 16;
				
				var elemTop       = $elem.offset().top - viewPad;
				var elemBottom    = elemTop + $elem.height() + viewPad;
				
				// We don't need the entire image to be present, just either edge.
				
				// If the top boundary is present
				widget.scrollLocked = (elemBottom <= docViewBottom) && (elemBottom >= docViewTop);
			}
		},
		
		// Event bindings
		bind     : {
			timer  : function() {
				var $lastReply = widget.$widget.prev();
				widget.$lastPost = $lastReply;
				
				if (!$lastReply.length)
				{
					// Select OP if we have no replies.
					$lastReply = widget.$widget.parents( widget.options.selector['thread-event-target'] );
					
					// We don't want OP to be our last post.
					widget.$lastPost = null;
				}
				
				widget.updateLast = widget.getTimeFromPost($lastReply);
				widget.hasFocus   = document.hasFocus();
				
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
				
				$(window)
					.on('scroll',      widget.events.windowScroll)
					.on('focus',       widget.events.windowFocus)
					.on('blur',        widget.events.windowUnfocus);
				
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
