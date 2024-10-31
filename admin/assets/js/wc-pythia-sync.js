jQuery(function () {
  var pythia_sync = {};

  // Init progressbar.
  var progressbar = jQuery('#progressbar');

  pythia_sync.show_pending_values = function (pending_orders) {
	var processedOrders = parseInt( pythia_sync_settings.pending_orders_count ) - parseInt( pending_orders );
	var progressValue   = 100;

	jQuery('.pending_orders_count').html(pending_orders);
	if ( parseInt( pythia_sync_settings.pending_orders_count ) > 0 && parseInt( pending_orders ) > 0 ) {
		progressValue = ( processedOrders * 100 ) / parseInt( pythia_sync_settings.pending_orders_count );
		progressbar.progressbar({
			value: progressValue || 0
		});
  	}
  };

  pythia_sync.schedule_actions = function () {
    pythiaLoader.show();
    // Assign handlers immediately after making the request,
    // and remember the jqxhr object for this request
    var dataArray = {
      action: 'pythia_schedule_action',
      _wpnonce: pythia_sync_settings.schedule_nonce
    };

    var jqxhr = jQuery.post(
      {
        url: ajaxurl,
        data: dataArray,
        dataType: 'json',
        success: function (response) {
          pythia_sync.show_pending_values(response.pending_orders_count);
          pythiaNotices.hide();
          pythia_sync.sync();
        }
      })
      .fail(function (response) {
        if (response.responseJSON) {
			pythiaNotices.errorResponse(response.responseJSON.data.join(' '));
        }
      })
      .always(function (response) {
        pythiaLoader.hide();
      });
  };

  pythia_sync.sync = function () {
    // Assign handlers immediately after making the request,
    // and remember the jqxhr object for this request
    var dataArray = {
      action: 'pythia_sync',
      _wpnonce: pythia_sync_settings.sync_nonce
	};

    pythiaNotices.hide();

    var jqxhr = jQuery.post(
      {
        url: ajaxurl,
        data: dataArray,
        dataType: 'json',
        success: function (response) {
          if (response.success) {
			  if ( response.data.pending_orders_count && parseInt( response.data.pending_orders_count ) > 0 ) {
				pythia_sync.show_pending_values(response.data.pending_orders_count);
				pythia_sync.sync();
			  } else {
				// Syncronization Finished!
				window.location.href = pythia_sync_settings.thank_you_page;
			  }
          } else {
            pythiaNotices.errorResponse(response.data.message);
          }
        }
      })
      .fail(function (response) {
        if (response.responseJSON && response.responseJSON.data) {
			pythiaNotices.errorResponse(response.responseJSON.data.join(' '));
			// Sync finished.
			progressbar.progressbar({
				disabled: true
			});
        }
      });
  };

  pythia_sync.resync = function () {
	pythiaLoader.show();
    // Assign handlers immediately after making the request,
    // and remember the jqxhr object for this request
    var dataArray = {
      action: 'pythia_resync',
      _wpnonce: pythia_sync_settings.resync_nonce
    };

    var jqxhr = jQuery.post(
      {
        url: ajaxurl,
        data: dataArray,
        dataType: 'json',
        success: function (response) {
          pythiaNotices.hide();
          pythia_sync.schedule_actions();
        }
      })
      .fail(function (response) {
        if (response.responseJSON) {
			pythiaNotices.errorResponse(response.responseJSON.data.join(' '));
        }
      })
      .always(function (response) {
        pythiaLoader.hide();
      });
  };

  jQuery('#pythia_manual_sync_submit').on('click', function (e) {
    e.preventDefault();
    pythia_sync.schedule_actions();
  });

  jQuery('#pythia_manual_resync').on('click', function (e) {
    e.preventDefault();
    pythia_sync.resync();
  });

  if ( pythia_sync_settings.sync_enabled && null !== pythia_sync_settings.schedule_next_run && 'undefined' !== typeof pythia_sync_settings.pending_orders_count && pythia_sync_settings.pending_orders_count > 0) {
    pythia_sync.show_pending_values(pythia_sync_settings.pending_orders_count);
    // Run Sync if sync is enabled and there are pending sync schedules.
    pythia_sync.sync();
  }
});
