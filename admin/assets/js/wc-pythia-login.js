jQuery(function() {
  var pythia_login      = {};

  pythia_login.login = function ( form ) {
    pythiaLoader.show();
    // Assign handlers immediately after making the request,
    // and remember the jqxhr object for this request.
    var dataArray = { action: 'pythia_login' };
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
			if ( response.data && 'object' === typeof response.data['projects'] ) {
				pythiaNotices.successResponse(pythia_auth_settings.login_successful_msg);
				window.location.href = pythia_auth_settings.projects_page;
			} else {
				pythiaNotices.errorResponse(pythia_auth_settings.no_projects_err_msg);
			}
        }
      })
      .fail(function(response) {
        if (response.responseJSON && 'undefined' !== typeof response.responseJSON.data) {
          pythiaNotices.errorResponse(response.responseJSON.data.join(' '));
        }
      })
      .always(function(response) {
        pythiaLoader.hide();
      });
  };

  var login_form = jQuery("#wc_pythia_sign_up");
  login_form.validate({
    submitHandler: function(form) {
      pythia_login.login(form);
    }
  });
});
