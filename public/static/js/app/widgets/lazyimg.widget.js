/**
 * Lazy Loading Image Widget
 */
ib.widget("lazyimg", function(window, $, undefined) {
	var widget = {
		
		// The default values that are set behind init values.
		defaults : {
			// Selectors for finding and binding elements.
			selector : {
				'img'      : "img",
				'img-lazy' : "img.lazy-load",
			}
		},
		
		
		events   : {
			imageLoad : function() {
				$(widget.options.selector['img-lazy'], widget.$widget).each(function() {
					var $this = $(this);
					
					$this.removeClass("lazy-load");
					
					// Correct the source.
					this.src = $this.attr('data-src');
					
					// Call the postbox widget to check if the image can be played
					// before the user tries to click it.
					$this.trigger('media-check');
				});
			},
			
			windowScroll : function(event) {
				// Determine if the boundaries of this image are in the viewport.
				
				var $elem         = widget.$widget;
				var $window       = $(window);
				
				var docViewTop    = $window.scrollTop();
				var docViewBottom = docViewTop + $window.height();
				
				var viewPad = 200;
				
				var elemTop       = $elem.offset().top - viewPad;
				var elemBottom    = elemTop + $elem.height() + viewPad;
				
				// We don't need the entire image to be present, just either edge.
				
				// If the top boundary is present
				if ( ((elemTop <= docViewBottom) && (elemTop >= docViewTop)) ||
					((elemBottom <= docViewBottom) && (elemBottom >= docViewTop)) )
				{
					widget.events.imageLoad();
				}
			}
		},
		
		bind     : {
			widget : function() {
				$(widget.options.selector['img'], widget.$widget).each(function() {
					var $this = $(this);
					
					$this.addClass("lazy-load");
					$this.attr('data-src', this.src);
					this.src = "";
				});
				
				$(window)
					.on('scroll.ib-lazyimg', widget.events.windowScroll)
					.on('ready.ib-lazyimg', widget.events.windowScroll);
				
				widget.events.windowScroll();
			}
		}
	};
	
	return widget;
});
