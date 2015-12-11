// ============================================================
// Purpose                      : InstantClick Integration
// Contributors                 : jaw-sh
// ============================================================

ib.widget("instantclick", function(window, $, undefined) {
	var widget = {
		
		storage : {
			jQuery       : null,
			ib           : null,
			InstantClick : null,
		},
		
		defaults : {
			'wait' : 50
		},
		
		// Events
		events   : {
			InstantClick : {
				change : function() {
					console.log("InstantClick change");
					
					// Restore our cached objects.
					window.jQuery       = widget.storage.jQuery;
					window.$            = window.jQuery;
					window.ib           = widget.storage.ib;
					window.InstantClick = widget.storage.InstantClick;
					
					// Insert our window.app data.
					jQuery.globalEval( $("#js-app-data").html() );
					
					// Bind all widgets.
					ib.bindAll();
					
					// Scroll to requested item.
					if (window.location.hash != "")
					{
						var elem = document.getElementById(window.location.hash);
						
						if (elem && typeof elem.scrollToElement === "function")
						{
							elem.scrollToElement();
						}
					}
				}
			},
		},
		
		// Event bindings
		bind     : {
			widget : function() {
				console.log("InstantClick init");
				
				InstantClick.init(widget.options.wait);
				
				if (typeof window.InstantClick === "object") {
					widget.storage.jQuery       = window.jQuery;
					widget.storage.ib           = window.ib;
					widget.storage.InstantClick = window.InstantClick;
					
					$.each(widget.events.InstantClick, function(eventName, eventClosure) {
						InstantClick.on(eventName, eventClosure);
					});
				}
			}
		}
	};
	
	return widget;
});
