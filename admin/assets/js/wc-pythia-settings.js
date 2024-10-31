jQuery(function($) {
	function confirm(e) {
		var url = $(this).data('url');
		$( "#dialog-confirm" ).dialog({
			resizable: false,
			autoOpen: true,
			height: "auto",
			width: 400,
			modal: true,
			buttons: {
			  Yes: function() {
				window.location.href = url;
			  },
			  No: function() {
				$( this ).dialog( "close" );
			  }
			}
		});
		return false;
	}

  $('#wc-pythia-reset').click(confirm);
  $('#wc-pythia-disconnect').click(confirm);
});
