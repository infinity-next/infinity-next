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
			default : "",
			type    : "select",
			values  : [
				"",
				"next-yotsuba.css",
				"next-dark.css",
				"next-tomorrow.css",
			],
			onChange : function(event) {
				var setting = event.data.setting.get();
				var domObj  = document.getElementById('theme-stylesheet');
				
				if (setting)
				{
					domObj.href = window.app.url + "/static/css/skins/" + setting;
				}
				else
				{
					domObj.href = "";
				}
			},
			onUpdate : function(event) {
				var setting = event.data.setting.get();
				var domObj  = document.getElementById('theme-stylesheet');
				
				if (setting)
				{
					domObj.href = window.app.url + "/static/css/skins/" + setting;
				}
				else
				{
					domObj.href = "";
				}
			}
		},
		
		css : {
			default : "",
			type    : "textarea",
			onChange : function(event) {
				var setting = event.data.setting.get();
				var domObj  = document.getElementById('user-css');
				domObj.innerHTML = setting;
			},
			onUpdate : function(event) {
				var setting = event.data.setting.get();
				var domObj  = document.getElementById('user-css');
				domObj.innerHTML = setting;
			}
		}
	};
	
	ib.widget("stylist", blueprint, options);
})(window, window.jQuery);
