jQuery(document).ready(function($) {

	var $formSubmitBtn = $('#fap-form-submit');

	$('#fap-type-selector').change(function() {
		var $this = $(this);
		
		//playlist is selected
		if($this.children(':selected').val() == 'playlist') {
			$('.fap-setting').show();
			$('#fap-single-track-form').hide();
			$('#fap-playlist-form, #fap-playlist-options').show();
			$formSubmitBtn.text('Add Playlist');
		}
		//single track
		else if($this.children(':selected').val() == 'track-url') {
			$('.fap-setting').show();
			$('#fap-single-track-form').show();
			$('#fap-playlist-form, #fap-playlist-options').hide();
			$formSubmitBtn.text('Add Track');
		}
		else {
			$('.fap-setting').hide();
			$('#fap-playlist-form').show();
			$formSubmitBtn.text('Change Default Playlist');
		}
		
	});
	
	$('input[name=fap_layout]').change(function() {
		if(this.value == 'grid' || this.value == 'list' || this.value == 'simple') {
			$('#fap-enqueue-click-option').show();
		}
		else {
			$('#fap-enqueue-click-option').hide();
		}
			
	});
	
	$formSubmitBtn.click(function() {
		
		if(tinymce.EditorManager.activeEditor == null) { alert('Please switch to the Visual mode in the editor to add the shortcode!'); return false; }
		
		if($formSubmitBtn.text() == 'Add playlist') {
			
			var id = $('#fap-playlists').val(),
				layout = $('input[name=fap_layout]:checked').val(),
				enqueue = $('fap_enqueue').is(':checked') ? 'yes' : 'no',
				autoenqueue = $('fap_auto_enqueue').is(':checked') ? 'yes' : 'no',
				playlistButton = $('#fap_playlist_button').val();
			
			tinymce.EditorManager.activeEditor.selection.setContent('[fap_playlist id="'+id+'" layout="'+layout+'" enqueue="'+enqueue+'" playlist_button="'+playlistButton+'" auto_enqueue="'+autoenqueue+'"]');
			
		}
		else if($formSubmitBtn.text() == 'Add Track'){
			
			var url = $('#fap-single-url').val(),
				title = $('#fap-single-title').val(),
				share = $('#fap-single-share').val(),
				cover = $('#fap-single-cover').val(),
				meta = $('#fap-single-meta').val(),
				layout = $('input[name=fap_layout]:checked').val(),
				enqueue = $('fap_enqueue').is(':checked') ? 'yes' : 'no',
				autoenqueue = $('fap_auto_enqueue').is(':checked') ? 'yes' : 'no';
				
				tinymce.EditorManager.activeEditor.selection.setContent('[fap_track url="'+url+'" title="'+title+'" title="'+title+'" share_link="'+share+'" cover="'+cover+'" meta="'+meta+'" layout="'+layout+'" enqueue="'+enqueue+'" auto_enqueue="'+autoenqueue+'"]');
			
			
		}
		else {
			var id = $('#fap-playlists').val();
			tinymce.EditorManager.activeEditor.selection.setContent('[fap_default_playlist id="'+id+'"]');
		}
		
		return false;
	});

});