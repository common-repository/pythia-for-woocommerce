jQuery(function() {

	jQuery.validator.addMethod('strength', function(value, element) {
	  // Reset the form & meter
		var strengthResult = jQuery('#password-strength');
		strengthResult.removeClass( 'short bad good strong' );

		// Extend our blacklist array with those from the inputs & site data
		var blacklistArray = wp.passwordStrength.userInputBlacklist();

		// Get the password strength
		var strength = wp.passwordStrength.meter( value, blacklistArray );

		// Add the strength meter results
		switch ( strength ) {

			case 1:
			case 2:
				strengthResult.addClass( 'bad' ).html( pwsL10n.bad );
				break;

			case 3:
				strengthResult.addClass( 'good' ).html( pwsL10n.good );
				break;

			case 4:
				strengthResult.addClass( 'strong' ).html( pwsL10n.strong );
				break;

			case 5:
				strengthResult.addClass( 'short' ).html( pwsL10n.mismatch );
				break;

			default:
				strengthResult.addClass( 'short' ).html( pwsL10n.short );
		}
		// enable this if we don't want to accept very weak passwords
		// if (strength == 0) {
		//   return false;
		// }
		return true;
	}, '');

	var sign_form = jQuery("#wc_pythia_sign_up").show();

	var v = sign_form
	.validate({
	  ignore: ".ignore",
	  rules: {
		  password: {
			strength: true,
			required: true
		  },
		},
	  submitHandler: function(form) {
		pythiaLoader.show();
		pythiaNotices.hide();

		// Assign handlers immediately after making the request,
		// and remember the jqxhr object for this request
		var dataArray = {
		  action: 'pythia_sign_up',
		  _wpnonce: pythia_setup_settings.sign_up_nonce
		};
		jQuery.each( jQuery(form).serializeArray(), function( i, field ) {
		  // TODO: check for allowed fields and only fill them
		  dataArray[field.name] = field.value;
		});

		var jqxhr = jQuery.post(
		  {
			url: ajaxurl,
			data: dataArray,
			dataType: 'json',
			success: function( response ) {
				pythiaNotices.hide();
			}
		  })
		  .fail(function(response) {
			if (response.responseJSON) {
				pythiaNotices.errorResponse(response.responseJSON.data.join(' '));
			}
		  })
		  .always(function(response) {
			pythiaLoader.hide();
		  });

		jqxhr.done(function(success_response) {
		  if ( ! success_response || 0 >= success_response.data.length ) {
			return;
		  }
		  pythiaLoader.show();
		  var jqxhrstore = jQuery.post(
			{
			  url: ajaxurl,
			  dataType: 'json',
			  data: {
				action:     'pythia_store_settings',
				_wpnonce:   pythia_setup_settings.store_settings_nonce,
				first_name: success_response.data.first_name,
				last_name:  success_response.data.last_name,
				project_id: success_response.data.project.id,
				project_name: success_response.data.project.name,
				email:      success_response.data.email,
				api_token:  success_response.data.api_token,
				source_id:  success_response.data.source_id
			  },
			  success: function( response ) {
				window.location.href = response.data.redirect_to;
			  }
			})
			.fail(function(response) {
			  if (response.responseJSON) {
				pythiaNotices.errorResponse(response.responseJSON.data.join(' '));
			  }
			})
			.always(function() {
			  pythiaLoader.hide();
			});
		});
	  }
	});

	jQuery('.pythia-generate-pw').on('click', function (e) {
	  e.preventDefault();
	  $password = jQuery('#password');
	  $pass1 = jQuery('#password1');

	  $password.val( $pass1.data( 'pw' ) );
	  $password.focusout();
	  // Generate a new password.
	  wp.ajax.post( 'generate-password' )
	  .done( function( data ) {
		$pass1.data( 'pw', data );
	  } );
	} );

	jQuery('#password').bind('keyup change', function(e) {
		jQuery(this).valid();
	});

  });
