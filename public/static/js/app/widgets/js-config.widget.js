// ===========================================================================
// Purpose          : JavaScript Configuration
// Contributors     : jaw-sh
// Notes            : This is a attempt at a new Widget format. It makes ample
//                    use of JS's prototyping, which may offer a
//                    serious speed advantage.
// ===========================================================================

(function(window, $, undefined) {
	var blueprint = function() {};
	
	// Default configurable valuess.
	blueprint.prototype.defaults = {
		
		// HTML Templates
		template : {
			
			// The outer panel.
			panel : "<form id=\"js-config\"></form>"
			
		}
		
	};
	
	// Event definitions
	blueprint.prototype.events = {
		
		navClick : function(event) {
			
			event.data.widget.presentDialog();
			
		}
		
	};
	
	// Main Widget Initialization Binding
	blueprint.prototype.bind = function() {
		var data = {
			widget  : this,
			$widget : this.$widget
		};
		
		this.$widget.on('click', data, this.events.navClick);
	};
	
	// Dialog (Option Menu) Presentation
	blueprint.prototype.presentDialog = function() {
		var $dialog = $(this.options.template.panel);
		
		// Blocks out the screen with an interruptable dialog.
		$.blockUI({
			message : $dialog
		});
		
		$('.blockOverlay').one('click', $.unblockUI);
	};
	
	ib.widget("js-config", blueprint);
})(window, window.jQuery);
