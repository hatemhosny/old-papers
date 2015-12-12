jQuery(document).ready(function() {
	
	//set a custom MP3 URL
	window.send_to_editor = function(html) {
		var url = jQuery(html).attr('href');
		tb_remove();
		jQuery('input[name=fap_track_url]').val(url);			
	};

});