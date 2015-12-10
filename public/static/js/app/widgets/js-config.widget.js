// ===========================================================================
// Purpose          : JavaScript Configuration UI Dialog
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
	// Widget blueprint
	var blueprint = function() {};
	
	// Configuration options
	var options = {
		
	};
	
	// Main Widget Initialization Binding
	blueprint.prototype.bind = function() {
		var data = {
			widget  : this,
			$widget : this.$widget
		};
		
		this.$widget.on('click.ib-js-config', data, this.events.navClick);
	};
	
	// Default configurable valuess.
	blueprint.prototype.defaults = {
		
		// Significant Class Names
		classname : {
			menuactive : "config-nav-active"
		},
		
		// jQuery Selectors
		selector : {
			menuitems : ".config-nav-item",
			fieldsets : ".config-group"
		},
		
		// HTML Templates
		template : {
			// The outer panel.
			panel     : "<form id=\"js-config\"></form>",
			
			// Config title
			title     : "<h1 class=\"config-title\">Infinity Next User Options</h1>",
			
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
		
		menuClick : function(event) {
			// Change the active fieldset.
			var widget = event.data.widget;
			var target = event.delegateTarget;
			var menuWidgetName = event.target.dataset.fieldset;
			
			var $menuitems = $(widget.options.selector.menuitems, target);
			var $fieldsets = $(widget.options.selector.fieldsets, target);
			
			// Change visible fieldsets.
			$fieldsets.each(function() {
				$(this).toggle(this.dataset.fieldset == menuWidgetName);
			});
			
			// Change active menu item.
			$menuitems.each(function() {
				$(this).toggleClass(
					widget.options.classname.menuactive,
					this.dataset.fieldset == menuWidgetName
				);
			});
		},
		
		navClick : function(event) {
			// Open the interface for changing settings.
			event.data.widget.presentDialog();
		}
		
	};
	
	// Dialog (Option Menu) Presentation
	blueprint.prototype.presentDialog = function() {
		var widget     = this;
		var $dialog    = $(widget.options.template.panel);
		
		// Begin putting together the dialog.
		var $title = $(widget.options.template.title);
		$title.appendTo($dialog);
		
		var $container = $(widget.options.template.container);
		$container.appendTo($dialog);
		
		var $interior  = $(widget.options.template.interior);
		$interior.appendTo($container);
		
		var $navcell   = $(widget.options.template.navcell);
		$navcell.appendTo($interior);
		
		var $fieldcell = $(widget.options.template.fieldcell);
		$fieldcell.appendTo($interior);
		
		var $navlist   = $(widget.options.template.navlist);
		$navlist.appendTo($navcell);
		
		// This indicates if we've unhidden our first fieldset pane.
		var firstFieldset = true;
		
		// Loop through each widget and pluck their settings.
		jQuery.each(ib.settings, function(widgetName, settings) {
			// Ensure there are options for this group.
			if (Object.keys(settings).length)
			{
				// Add a fieldset.
				var $fieldset = $(widget.options.template.fieldset)
					.data('fieldset', widgetName)
					.attr('data-fieldset', widgetName)
					.appendTo($fieldcell);
				
				// Append its legend.
				$(widget.options.template.legend)
					.append(widgetName)
					.appendTo($fieldset);
				
				// Add a navigation item.
				$(widget.options.template.navitem)
					.data('fieldset', widgetName)
					.attr('data-fieldset', widgetName)
					.append(widgetName)
					.appendTo($navlist);
				
				jQuery.each(settings, function(settingName, setting) {
					setting.toHTML().appendTo($fieldset);
				});
				
				if (firstFieldset)
				{
					firstFieldset = false;
					$fieldset.show();
				}
			}
		});
		
		// Blocks out the screen with an interruptable dialog.
		$.blockUI({
			message    : $dialog,
			css        : {
				background : "none",
				border     : "none",
				padding    : 0,
				margin     : 0,
				textAlign  : "left",
				cursor     : "normal"
			},
			overlayCSS : {
				border     : "none",
				padding    : 0,
				margin     : 0,
				textAlign  : "left",
				cursor     : "normal"
			}
		});
		
		// Bind fade event.
		$(".blockOverlay").one('click', $.unblockUI);
		
		// Bind menu toggle items
		$dialog.on(
			'click.ib-js-config',
			widget.options.selector.menuitems,
			{
				widget  : widget,
				$widget : widget.$widget
			},
			this.events.menuClick
		);
	};
	
	ib.widget("js-config", blueprint, options);
})(window, window.jQuery);
