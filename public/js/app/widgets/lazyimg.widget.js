/**
 * Lazy Loading Image Widget
 */
ib.widget("lazyimg", function(window, $, undefined) {
	var widget = {
		
		// The default values that are set behind init values.
		defaults : {
			// Selectors for finding and binding elements.
			selector : {
				'img' : "img",
			}
		},
		
		
		events   : {
			imageLoad : function() {
				$(widget.options.selector.img, widget.$widget).each(function() {
					this.src = $(this).attr('data-src');
				});
			},
			
			windowScroll : function(event) {
				var $elem         = widget.$widget;
				var $window       = $(window);
				
				var docViewTop    = $window.scrollTop();
				var docViewBottom = docViewTop + $window.height();
				
				var elemTop       = $elem.offset().top;
				var elemBottom    = elemTop + $elem.height();
				
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
				$(widget.options.selector.img, widget.$widget).each(function() {
					$(this).attr('data-src', this.src);
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
