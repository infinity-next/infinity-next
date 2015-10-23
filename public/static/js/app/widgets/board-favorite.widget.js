// ============================================================
// Purpose                      : Board Favorites
// Contributors                 : jaw-sh
// ============================================================

ib.widget("board-favorite", function(window, $, undefined) {
	var widget = {
		
		can :  function() {
			var ls    = typeof localStorage === "object";
			var board = widget.$widget.attr('data-board');
			
			return ls && board;
		},
		
		favorites : function() {
			var storageItem = localStorage.getItem(widget.options.storage.favorites);
			
			if (typeof storageItem === "string")
			{
				return storageItem.split(",");
			}
			
			return [];
		},
		
		favoriteThis : function( addFavorite ) {
			var boards = widget.favorites();
			
			if (addFavorite)
			{
				boards.push(widget.board);
				boards = jQuery.unique(boards);
			}
			else
			{
				boards = jQuery.grep(boards, function(value) {
					return value != widget.board;
				});
			}
			
			localStorage.setItem(widget.options.storage.favorites, boards);
		},
		
		defaults : {
			classname : {
				'favorited' : "board-favorited"
			},
			
			storage   : {
				'favorites'      : "ib.favorites",
				'favorites-data' : "ib.favoritedata"
			},
			
			selector  : {
				'favorites-menu' : "#favorite-boards"
			}
		},
		
		// Events
		events   : {
			
			favoriteChange : function(event) {
				var favorites = widget.favorites();
				
				for (var i = 0; i < favorites.length; ++i)
				{
					if (widget.board == favorites[i])
					{
						widget.$widget.addClass(widget.options.classname.favorited);
						return true;
					}
				}
				
				widget.$widget.removeClass(widget.options.classname.favorited);
				return false;
			},
			
			favoriteClick : function(event) {
				widget.favoriteThis( !widget.$widget.hasClass(widget.options.classname.favorited) );
				widget.events.favoriteChange.call(this, event);
				widget.events.favoriteUpdate.call(this, event);
			},
			
			favoriteUpdate : function(event) {
				$.get(window.app.url+"/board-details.json", {
					'boards' : widget.favorites()
				}).done(function(response) {
					
					localStorage.setItem(
						widget.options.storage['favorites-data'],
						JSON.stringify(response)
					);
					
					$(widget.options.selector['favorites-menu'])
						.trigger('build');
					
				});
			},
			
			// This is an HTML localStorage event.
			// it only fires if ANOTHER WINDOW trips the change.
			storage : function(event) {
				if (event.originalEvent.key == widget.options.storage.favorites)
				{
					widget.events.favoriteChange();
				}
			}
			
		},
		
		// Event bindings
		bind     : {
			failure : function() {
				widget.$widget.hide();
			},
			
			widget  : function() {
				widget.board = widget.$widget.attr('data-board');
				
				widget.$widget
					.show()
					.on( 'click.ib-board-favorite', widget.events.favoriteClick )
				;
				
				$(window)
					.on( 'storage.ib-board-favorite', widget.events.storage )
				;
				
				widget.events.favoriteChange();
			}
		}
		
	};
	
	return widget;
});

