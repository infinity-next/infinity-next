// ===========================================================================
// Purpose          : Custom Styling
// Contributors     : jaw-sh
// Widget Version   : 2
// ===========================================================================

(function(window, $, undefined) {
	// Widget blueprint
	var blueprint = ib.getBlueprint();
	
	// Configuration options
	var options = {
		theme : {
			default : "next.css",
			type    : "select",
			values  : [
				"next.css",
				"next-yotsuba.css",
				"day-after-tomorrow.css",
				"kappa.css",
				"tomorrow-kappa.css",
			]
		},
		
		css : {
			default : "",
			type    : "textarea"
		}
	};
	
	ib.widget("stylist", blueprint, options);
})(window, window.jQuery);
