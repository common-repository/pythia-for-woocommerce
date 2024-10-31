jQuery.fn.extend({
    animateCss: function(animationName) {
        const animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
        jQuery(this).addClass(`animated ${animationName} show`).one(animationEnd, function() {
            jQuery(this).removeClass(`animated ${animationName}`);
        });
    }
});

var pythiaLoader = {
	loader: null,
	container: null,
    init: function( className = 'main.pythia' ) {
		this.container = jQuery(className);
		if ( 'undefined' === typeof this.container ) {
			console.info( 'Pythia main HTML tag for notices does not exists.' );
			return;
		}

		this.parentContainer = this.container.parent();
		this.container = ( null !== this.parentContainer && this.parentContainer.length > 0 ) ? this.parentContainer : this.container;

		this.loader = jQuery('<div class="se-pre-con"></div>')
				.appendTo(this.container)
				.hide();
    },
    show: function() {
        if ( null !== this.loader ) {
            this.loader.show();
        } else {
            console.info( 'Loader not initialized.' );
        }
    },
    hide: function() {
        if ( null !== this.loader ) {
            this.loader.hide();
        } else {
            console.info( 'Loader not initialized.' );
        }
    }
};

var pythiaNotices = {
    hide: function( message = '' ) {
        jQuery('.py-error-message').html('').parent().hide();
        jQuery('.py-success-message').html('').parent().hide();
    },
    successResponse: function( message = '' ) {
        jQuery('.py-error-message').html('').parent().hide();
        jQuery('.py-success-message').html(message).parent().show();
    },
    errorResponse: function( message = '' ) {
        jQuery('.py-success-message').html('').parent().hide();
        jQuery('.py-error-message').html(message).parent().show();
	},
	close: function( e ) {
        jQuery('.py-success-message').html('').parent().hide();
        jQuery('.py-error-message').html('').parent().hide();
	}
};

jQuery(function($) {
	$(document).on('click', '.pythia-notice-close', function ( e ) {
		pythiaNotices.close( this );
	});
	// show a simple loading indicator
	pythiaLoader.init();
});
