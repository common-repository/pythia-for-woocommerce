jQuery(function() {
  var pythia_google_auth = {};

  pythia_google_auth.maybe_authenticate = function() {
    pythiaLoader.show();
    // Assign handlers immediately after making the request,
    // and remember the jqxhr object for this request
    var dataArray = { action: 'pythia_maybe_authenticate', nonce: pythia_auth_settings.pythia_admin_nonce, wizard_step: pythia_auth_settings.wizard_step };

    var jqxhr = jQuery.post(
      {
        url: ajaxurl,
        data: dataArray,
        dataType: 'json',
        success: function( response ) {
          if ( response && typeof response.data != 'undefined' ) {
            pythia_google_auth.update_source_id(response.data);
          } else {
            pythiaNotices.errorResponse(pythia_auth_settings.source_id_updated_err_msg);
          }
        }
      })
      .fail(function(response) {
		pythiaLoader.hide();
        if (response.responseJSON) {
          pythiaNotices.errorResponse(response.responseJSON.data.join(' '));
        }
      })
      .always(function(response) {
      });
  };

  pythia_google_auth.update_source_id = function(data) {
    pythiaLoader.show();
    data['action'] = 'pythia_update_source_id';
    data['nonce'] = pythia_auth_settings.pythia_admin_nonce;
    data['wizard_step'] = pythia_auth_settings.wizard_step;
    // Assign handlers immediately after making the request,
    // and remember the jqxhr object for this request

    var jqxhr = jQuery.post(
      {
        url: ajaxurl,
        data: data,
        dataType: 'json',
        success: function( response ) {
		  pythiaNotices.successResponse(pythia_auth_settings.source_id_updated_msg);
          if (response.data && response.data['redirect_to']) {
            window.location.href = response.data['redirect_to'];
          }
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
  };

  pythia_google_auth.update_analytics_account = function() {
    pythiaLoader.show();
    data = {
      'action':     'pythia_update_analytics_ua_account',
      'nonce':      pythia_auth_settings.pythia_admin_nonce,
      'wizard_step': pythia_auth_settings.wizard_step,
      'ga_ua_id':   jQuery('select[name="ga_source_web_id"] option:selected').val(),
      'ga_ua_name': jQuery('select[name="ga_source_web_id"] option:selected').text()
    }
    // Assign handlers immediately after making the request,
    // and remember the jqxhr object for this request

    var jqxhr = jQuery.post(
      {
        url: ajaxurl,
        data: data,
        dataType: 'json',
        success: function( response ) {
          if (response.data && response.data['id']) {
            var success_message = pythia_auth_settings.ga_ua_id_updated_msg.replace('%s', data['ga_ua_id'])
			pythiaNotices.successResponse(success_message);
			if (response.data && response.data['redirect_to']) {
				window.location.href = response.data['redirect_to'];
			}
          } else {
            pythiaNotices.errorResponse(pythia_auth_settings.ga_ua_id_updated_err_msg);
          }
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
  };

  jQuery('#pythia_google_auth_submit').on('click', function(e){
    e.preventDefault();
    pythia_google_auth.maybe_authenticate();
  });

  jQuery('#pythia_submit_ga_id').on('click', function(e){
    e.preventDefault();
    pythia_google_auth.update_analytics_account();
  });

});
