// ============================================================
// Purpose                      : Board Favorites
// Contributors                 : jaw-sh
// ============================================================

ib.widget("board-favorite", function(window, $, undefined) {
	var widget = {
		
		defaults : {
			selector : {
				
			}
		},
		
		// Events
		events   : {
			
		},
		
		// Event bindings
		bind     : {
			widget : function() {
				var widget.board = widget.$widget.attr('data-board');
				
				if (widget.board != "")
				{
					
				}
			}
		}
		
	};
	
	return widget;
});

