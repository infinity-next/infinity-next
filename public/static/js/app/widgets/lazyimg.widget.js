// ===========================================================================
// Purpose          : Lazy Image Loading
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
	// Widget blueprint
	var blueprint = function() {};
	
	// Configuration options
	var options = {
		enabled : {
			default : false,
			type    : "bool"
		}
	};
	
	// Main Widget Initialization Binding
	blueprint.prototype.bind = function() {
		var widget  = this;
		var $widget = this.$widget;
		var data    = {
			widget  : widget,
			$widget : $widget
		};
		
		$widget.on('click', data, this.events.navClick);
		
		// With this image remove src data and indicate it's lazy.
		$widget.addClass("lazy-load");
		$widget.attr('data-src', $widget[0].src);
		$widget[0].src = "";
		
		$widget.on('lazywake.ib-lazymg',  data, widget.events.imageLazyWake);
		$(window).on('scroll.ib-lazyimg', data, widget.events.windowScroll);
	};
	
	// The default values that are set behind init values.
	blueprint.prototype.defaults = {
		// Selectors for finding and binding elements.
		selector : {
			'img'      : "img",
			'img-lazy' : "img.lazy-load",
		}
	};
	
	blueprint.prototype.events = {
		imageLazyWake : function(event) {
			var widget  = event.data.widget;
			var $widget = event.data.$widget;
			
			$(widget.options.selector['img-lazy'], $widget).each(function() {
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
			var widget        = event.data.widget;
			var $widget       = event.data.$widget;
			var $window       = $(window);
			
			var docViewTop    = $window.scrollTop();
			var docViewBottom = docViewTop + $window.height();
			
			var viewPad = 200;
			
			var elemTop       = $widget.offset().top - viewPad;
			var elemBottom    = elemTop + $widget.height() + viewPad;
			
			// We don't need the entire image to be present, just either edge.
			
			// If the top boundary is present
			if ( ((elemTop <= docViewBottom) && (elemTop >= docViewTop)) ||
				((elemBottom <= docViewBottom) && (elemBottom >= docViewTop)) )
			{
				$widget.trigger('lazywake');
			}
		}
	};
	
	ib.widget("lazyimg", blueprint, options);
})(window, window.jQuery);
