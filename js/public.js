var faveIt = faveIt || {};

(function ($) {
	$.widget("ced.faveit", {
		options: { 
			faveClass : 'faved',
			user : faveItData.userId,
			nonce : faveItData.faveNonce
		},
		
		_init : function() {
			var el = this.element,
				options = this.options,
				post = el.data( 'post-id' );
				
			if( post )
				options.post = post;
			else {
				this.destroy();
			}
			
			//Events
			el.on( 'click', $.proxy( this.toggle, this ) );
		},
		
		toggle : function( e ) {
			e.preventDefault();
			var target = $( this.element );
			if( target.hasClass( this.options.faveClass ) )
				this.unfave();
			else
				this.fave();
		},

		fave : function() {
			var target = $( this.element ),
				plugin = this,
				options = this.options,
				data = {
					post_id : options.post,
					user_id :options.user,
					_ajax_nonce : options.nonce,
					action : 'fave'
			};
			$.post( ajaxurl, data, function( response ) {		
				if( response.success ) {
					target.addClass( options.faveClass );
					plugin._trigger( 'change', 0, 'fave' );
				}
			}, 'json' );
			
		},
		
		unfave : function() {
			var target = $( this.element ),
				plugin = this,
				options = this.options,
				data = {
					post_id : options.post,
					user_id : options.user,
					_ajax_nonce : options.nonce,
					action : 'unfave'
			};
			$.post( ajaxurl, data, function( response ) {		
				if( response.success ) {
					target.removeClass( options.faveClass );
					plugin._trigger( 'change', 0, 'unfave' );
				}
			}, 'json' );
		}	
	});
}(jQuery));	