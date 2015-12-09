// ===========================================================================
// Purpose          : JavaScript Configuration
// Contributors     : jaw-sh
// Notes            : This is a attempt at a new Widget format. It makes ample
//                    use of JS's prototyping, which may offer a
//                    serious speed advantage.
// ===========================================================================

(function(window, $, undefined) {
	var blueprint = function() {};
	
	// Main Widget Initialization Binding
	blueprint.prototype.bind = function() {
		var data = {
			widget  : this,
			$widget : this.$widget
		};
		
		this.$widget.on('click', data, this.events.navClick);
	};
	
	// Default configurable valuess.
	blueprint.prototype.defaults = {
		
		// HTML Templates
		template : {
			
			// The outer panel.
			panel     : "<form id=\"js-config\"></form>",
			
			// Inner container.
			container : "<table class=\"config-table\"></table>",
			
			// Sub-container for both nav and fieldsets.
			interior  : "<tr class=\"config-interior\"></tr>",
			
			// Outer container for the navigation list.
			navcell   : "<td class=\"config-cell cell-nav\"></td>",
			
			// Navigation list.
			navlist   : "<ul class=\"config-nav-list\"></ul>",
			
			// Navigation item.
			navitem   : "<li class=\"config-nav-item\"><i class=\"fa\"></i></li>",
			
			// Outer container for the fieldsets
			fieldcell : "<td class=\"config-cell cell-fields\"></td>",
			
			// Widget fieldset
			fieldset  : "<fieldset class=\"config-group\"></fieldset>",
			
			// Widget fieldset legend
			legend    : "<legend class=\"config-legend\"></legend>",
			
			// Row for the fields.
			row       : "<label class=\"confg-row\"></label>"
		}
		
	};
	
	// Event definitions
	blueprint.prototype.events = {
		
		navClick : function(event) {
			
			event.data.widget.presentDialog();
			
		}
		
	};
	
	// Dialog (Option Menu) Presentation
	blueprint.prototype.presentDialog = function() {
		var widget     = this;
		var $dialog    = $(widget.options.template.panel);
		
		var $container = $(widget.options.template.legend);
		$container.appendTo($dialog);
		
		var $interior  = $(widget.options.template.interior);
		$interior.appendTo($container);
		
		var $navcell   = $(widget.options.template.navcell);
		$navcell.appendTo($interior);
		
		var $fieldcell = $(widget.options.template.fieldcell);
		$fieldcell.appendTo($interior);
		
		var $navlist   = $(widget.options.template.navlist);
		$navlist.appendTo($navcell);
		
		jQuery.each(ib.settings, function(widgetName, settings) {
			var $fieldset = $(widget.options.template.fieldset);
			$fieldset.appendTo($fieldcell);
			
			$(widget.options.template.legend)
				.append(widgetName)
				.appendTo($fieldset);
			
			$(widget.options.template.navitem)
				.append(widgetName)
				.appendTo($navlist);
			
			jQuery.each(settings, function(settingName, setting) {
				setting.toHTML().appendTo($fieldset);
			});
		});
		
		// Blocks out the screen with an interruptable dialog.
		$.blockUI({
			message : $dialog
		});
		
		$('.blockOverlay').one('click', $.unblockUI);
	};
	
	// Configuration options
	var config = {
		
	};
	
	ib.widget("js-config", blueprint, config);
})(window, window.jQuery);
