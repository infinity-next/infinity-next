// ===========================================================================
// Purpose          : Posts
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
	// Widget blueprint
	var blueprint = ib.getBlueprint();

	// Configuration options
	var options = {
		enable : {
			type : "bool",
			initial : false
		}
	};

	// Event bindings
	blueprint.prototype.bind = function() {
		if (!this.is('enable'))
		{
			console.log("InstantClick ignored");
			return false;
		}

		console.log("InstantClick init");

		InstantClick.init(this.options.wait);

		blueprint.prototype.storage.jQuery       = window.jQuery;
		blueprint.prototype.storage.ib           = window.ib;
		blueprint.prototype.storage.InstantClick = window.InstantClick;

		$.each(this.events.InstantClick, function(eventName, eventClosure) {
			InstantClick.on(eventName, eventClosure);
		});
	};

	blueprint.prototype.defaults = {
		'wait' : 50
	};

	blueprint.prototype.events = {
		InstantClick : {
			change : function() {
				console.log("InstantClick change");

				this.storage;
				// Restore our cached objects.
				window.jQuery       = blueprint.prototype.storage.jQuery;
				window.$            = blueprint.prototype.storage.jQuery;
				window.ib           = blueprint.prototype.storage.ib;
				window.InstantClick = blueprint.prototype.storage.InstantClick;

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
		}
	};

	// Long-term storage that the InstantCLick widget uses to preserve script
	// items between page sessions.
	blueprint.prototype.storage = {
		jQuery       : null,
		ib           : null,
		InstantClick : null,
	};

	ib.widget("instantclick", blueprint, options);

	$(document).one('ready', function(event) {
		ib.bindElement(document.documentElement);
	});
})(window, window.jQuery);
