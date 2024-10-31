var pythia_projects = {};

pythia_projects.save_project_settings = function (form) {
	var pythia_selected_project = jQuery('#pythia_project').children('option:selected');
	var pythia_selected_source = jQuery('#pythia_source').children('option:selected');
	var projectData = {
		action: 'pythia_save_project_settings',
		_wpnonce: pythia_project_settings.save_project_nonce,
		project_id: pythia_selected_project.val(),
		project_name: pythia_selected_project.text(),
		redirect_to: null,
		source_id: null,
		source_name: null
	};

	if ( pythia_selected_source.val() ) {
		projectData.source_id   = pythia_selected_source.val();
		projectData.source_name = pythia_selected_source.text();
	}

	if ( pythia_project_settings.redirect_to ) {
		projectData.redirect_to = encodeURI( pythia_project_settings.redirect_to );
	}

	pythiaLoader.show();
	jQuery.post({
		url: ajaxurl,
		dataType: 'json',
		data: projectData,
		success: function (response) {
			if (response.data.redirect_to) {
				window.location.href = response.data.redirect_to;
			} else {
				pythiaNotices.successResponse(response.data.message);
			}
		}
	})
	.fail(function (response) {
		if (response.responseJSON && 'undefined' !== typeof response.responseJSON.data) {
			pythiaNotices.errorResponse(response.responseJSON.data.join(' '));
		}
	})
	.always(function () {
		pythiaLoader.hide();
	});
};

jQuery(function () {
	var settings_form = jQuery("#wc_pythia_project_settings");
	var project_combo = jQuery('select#pythia_project');

	settings_form.validate({
		submitHandler: function (form) {
			pythia_projects.save_project_settings(form);
		}
	});

	project_combo.change(function () {
		var pythia_selected_project_id = jQuery(this).children('option:selected').val();
		var project = {};
		if (pythia_selected_project_id) {
			project = pythia_project_settings.projects.find( proj => proj.id === pythia_selected_project_id );
			if (project && project.hasOwnProperty('sources')) {
				var project_sources_combo = jQuery('select#pythia_source');
				var validSource = false;
				project_sources_combo.html('');
				Object.keys(project['sources']).forEach(key => {
					if ( true === project['sources'][key]['enabled'] && 'woocommerce' === project['sources'][key]['source_type']['name'].toLowerCase() && pythia_project_settings.is_wocommerce_active ) {
						validSource = true;
						project_sources_combo.append('<option value="' + project['sources'][key]['id'] + '">' + project['sources'][key]['source_type']['name'] + '</option>');
					}
				});

				if ( ! validSource ) {
					project_sources_combo.append('<option value="wordpress">' + pythia_project_settings.source_default + '</option>');
				}

				// If we have only one source, select it by default
				// if (1 === project['sources'].length) {
				// 	project_sources_combo.val(project['sources'][0]['id']).trigger( "change" );
				// }
			}
		} else {
			jQuery('select#pythia_source option').not(':first').remove();
		}
	});
	// fire change action if we have only one project in the list.
	if ( project_combo.children('option').length === 1 ) {
		project_combo.trigger( "change" );
	}
});
