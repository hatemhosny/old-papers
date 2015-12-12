<!-- General Options -->
<table class="widefat option-table" cellspacing="0">
    <tbody>
    	
    	<!-- Playlist -->
        <tr valign="top" class="alternate">
          <th scope="row"><?php _e('Default Playlist', 'radykal'); ?></th>
          <td>
      		<select name="default_playlist">
      			<option value="-1">None</option>
      			<?php 
      			$playlists = get_terms('dt_playlist');
				if ( count($playlists) > 0 ){
					echo "<ul>";
				    foreach ( $playlists as $playlist ) {
				    	echo '<option value="'.$playlist->term_id.'" '.selected($audioplayer_options['default_playlist'], $playlist->term_id, false).'>' . $playlist->name . '</option>';
				    }
				    echo "</ul>";
				}
      			?>
      		</select>
      		<span><?php _e('Select a default playlist that should be loaded into the player from beginning.', 'radykal'); ?></span>
          </td>
        </tr>
        
        <!-- Positions -->
        <tr valign="top" class="no-border">
          <th><?php _e('Positions', 'radykal'); ?></th>
        </tr>
        <tr valign="top" class="sub-options no-border">
          <th><?php _e('Wrapper', 'radykal'); ?></th>
          <td>
          	<input type="radio" name="wrapper_position" value="top" <?php checked($audioplayer_options['wrapper_position'], 'top'); ?> /> <label class="radio-label"><?php _e('Top', 'radykal'); ?></label>
          	<input type="radio" name="wrapper_position" value="bottom" <?php checked($audioplayer_options['wrapper_position'], 'bottom'); ?> /> <label class="radio-label"><?php _e('Bottom', 'radykal'); ?></label>
          	<input type="radio" name="wrapper_position" value="popup" <?php checked($audioplayer_options['wrapper_position'], 'popup'); ?> /> <label class="radio-label"><?php _e('Pop-Up', 'radykal'); ?></label>
          </td>
        </tr>
        <tr valign="top" class="sub-options last-sub-option">
          <th><?php _e('Player', 'radykal'); ?></th>
          <td>
          	<input type="radio" name="main_position" value="left" <?php checked($audioplayer_options['main_position'], 'left'); ?> />
          	<label class="radio-label"><?php _e('Left', 'radykal'); ?></label>
          	<input type="radio" name="main_position" value="center" <?php checked($audioplayer_options['main_position'], 'center'); ?> />
          	<label class="radio-label"><?php _e('Center', 'radykal'); ?></label>
          	<input type="radio" name="main_position" value="right" <?php checked($audioplayer_options['main_position'], 'right'); ?> /> 
          	<label class="radio-label"><?php _e('Right', 'radykal'); ?></label>
          </td>
        </tr>
        
        <!-- Colors -->
        <tr valign="top" class="no-border alternate">
          <th><?php _e('Colors', 'radykal'); ?></th>
        </tr>
        <tr valign="top" class="sub-options no-border alternate">
        	<th scope="row"><?php _e('Wrapper', 'radykal'); ?></th>
          	<td>
          		<input type="text" class="colorpicker" name="wrapper_color" value="<?php echo $audioplayer_options['wrapper_color']; ?>" />
	          	<span class="colorpicker-label"><?php _e('The background color of the wrapper', 'radykal'); ?></span>
          	</td>
        </tr>
        <tr valign="top" class="sub-options no-border alternate">
        	<th scope="row"><?php _e('Main', 'radykal'); ?></th>
          	<td>
	          	<input type="text" class="colorpicker" name="main_color" value="<?php echo $audioplayer_options['main_color']; ?>" />
	          	<span class="colorpicker-label"><?php _e('The main color for signs and title', 'radykal'); ?></span>
          	</td>
        </tr>
        <tr valign="top" class="sub-options no-border alternate">
        	<th scope="row"><?php _e('Graphics Fill', 'radykal'); ?></th>
          	<td>
	          	<input type="text" class="colorpicker" name="fill_color" value="<?php echo $audioplayer_options['fill_color']; ?>" />
	          	<span class="colorpicker-label"><?php _e('The background fill color of the graphics', 'radykal'); ?></span>
          	</td>
        </tr>
        <tr valign="top" class="sub-options no-border alternate">
        	<th scope="row"><?php _e('Graphics Fill Hover', 'radykal'); ?></th>
          	<td>
	          	<input type="text" class="colorpicker" name="fill_hover_color" value="<?php echo $audioplayer_options['fill_hover_color']; ?>" />
	          	<span class="colorpicker-label"><?php _e('The background fill color when you move your mouse over it.', 'radykal'); ?></span>
          	</td>
        </tr>
        <tr valign="top" class="sub-options no-border alternate">
        	<th scope="row"><?php _e('Meta', 'radykal'); ?></th>
          	<td>
	          	<input type="text" class="colorpicker" name="meta_color" value="<?php echo $audioplayer_options['meta_color']; ?>" />
	          	<span class="colorpicker-label"><?php _e('The meta color (Text under title)', 'radykal'); ?></span>
          	</td>
        </tr>
        <tr valign="top" class="sub-options no-border alternate">
        	<th scope="row"><?php _e('Stroke', 'radykal'); ?></th>
          	<td>
	          	<input type="text" class="colorpicker" name="stroke_color" value="<?php echo $audioplayer_options['stroke_color']; ?>" />
	          	<span class="colorpicker-label"><?php _e('The stroke color', 'radykal'); ?></span>
          	</td>
        </tr>
        <tr valign="top" class="sub-options alternate last-sub-option">
        	<th scope="row"><?php _e('Active Track in Playlist', 'radykal'); ?></th>
          	<td>
	          	<input type="text" class="colorpicker" name="active_track_color" value="<?php echo $audioplayer_options['active_track_color']; ?>" />
	          	<span class="colorpicker-label"><?php _e('The background color for the current selected track in the playlist', 'radykal'); ?></span>
          	</td>
        </tr>
        
        <!-- Dimensions -->
        <tr valign="top" class="no-border">
          <th scope="row"><?php _e('Dimensions', 'radykal'); ?></th>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Height', 'radykal'); ?></th>
          	<td><input type="text" size="3" name="wrapper_height" value="<?php echo $audioplayer_options['wrapper_height']; ?>" /> pixels</td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Playlist Height', 'radykal'); ?></th>
          	<td><input type="text" size="3" name="playlist_height" value="<?php echo $audioplayer_options['playlist_height']; ?>" /> pixels</td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Cover Width', 'radykal'); ?></th>
          	<td><input type="text" size="3" name="cover_width" value="<?php echo $audioplayer_options['cover_width']; ?>" /> pixels</td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Cover Height', 'radykal'); ?></th>
          	<td><input type="text" size="3" name="cover_height" value="<?php echo $audioplayer_options['cover_height']; ?>" /> pixels</td>
        </tr>
        <tr valign="top" class="sub-options last-sub-option">
        	<th scope="row"><?php _e('Offset between playlist and player', 'radykal'); ?></th>
          	<td><input type="text" size="3" name="offset" value="<?php echo $audioplayer_options['offset']; ?>" /> pixels</td>
        </tr>
        
        <!-- Labels -->
        <tr valign="top" class="no-border alternate">
          <th scope="row"><?php _e('Labels', 'radykal'); ?></th>
        </tr>
        <tr valign="top" class="sub-options no-border alternate">
        	<th scope="row"><?php _e('Facebook Link', 'radykal'); ?></th>
          	<td><input type="text" name="facebook_text" class="widefat" value="<?php echo $audioplayer_options['facebook_text']; ?>" /></td>
        </tr>
        <tr valign="top" class="sub-options no-border alternate">
        	<th scope="row"><?php _e('Twitter Link', 'radykal'); ?></th>
          	<td><input type="text" name="twitter_text" class="widefat" value="<?php echo $audioplayer_options['twitter_text']; ?>" /></td>
        </tr>
        <tr valign="top" class="sub-options alternate last-sub-option">
        	<th scope="row"><?php _e('Download Link (only for soundcloud tracks)', 'radykal'); ?></th>
          	<td><input type="text" name="download_text" class="widefat" value="<?php echo $audioplayer_options['download_text']; ?>" /></td>
        </tr>
        
        <!-- Checkboxes -->
        <tr valign="top" class="no-border">
          <th scope="row"><?php _e('Activations', 'radykal'); ?></th>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Opened', 'radykal'); ?></th>
          	<td><input type="checkbox" name="opened" value="1" <?php checked($audioplayer_options['opened'], 1); ?> /> <span class="checkbox-label"><?php _e('Open player by default', 'radykal'); ?></span></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Volume Button', 'radykal'); ?></th>
          	<td><input type="checkbox" name="volume" value="1" <?php checked($audioplayer_options['volume'], 1); ?> /> <span class="checkbox-label"><?php _e('Show volume button', 'radykal'); ?></span></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Visual playlist', 'radykal'); ?></th>
          	<td><input type="checkbox" name="playlist" value="1" <?php checked($audioplayer_options['playlist'], 1); ?> /> <span class="checkbox-label"><?php _e('Enable visual playlist', 'radykal'); ?></span></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Autoplay', 'radykal'); ?></th>
          	<td><input type="checkbox" name="autoPlay" value="1" <?php checked($audioplayer_options['autoPlay'], 1); ?> /> <span class="checkbox-label"><?php _e('Enable autoplay', 'radykal'); ?></span></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Autoload', 'radykal'); ?></th>
          	<td><input type="checkbox" name="autoLoad" value="1" <?php checked($audioplayer_options['autoLoad'], 1); ?> /> <span class="checkbox-label"><?php _e('Load MP3 track when autoplay is disabled', 'radykal'); ?></span></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Autonext', 'radykal'); ?></th>
          	<td><input type="checkbox" name="playNextWhenFinished" value="1" <?php checked($audioplayer_options['playNextWhenFinished'], 1); ?> /> <span class="checkbox-label"><?php _e('Play next track when current track ends', 'radykal'); ?></span></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Keyboard Shortcuts', 'radykal'); ?></th>
          	<td><input type="checkbox" name="keyboard" value="1" <?php checked($audioplayer_options['keyboard'], 1); ?> /> <span class="checkbox-label"><?php _e('Enable keyboard shortcuts', 'radykal'); ?></span></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Social Links', 'radykal'); ?></th>
          	<td><input type="checkbox" name="socials" value="1" <?php checked($audioplayer_options['socials'], 1); ?> /> <span class="checkbox-label"><?php _e('Show social Links', 'radykal'); ?></span></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Shuffle Button', 'radykal'); ?></th>
          	<td><input type="checkbox" name="shuffle" value="1" <?php checked($audioplayer_options['shuffle'], 1); ?> /> <span class="checkbox-label"><?php _e('Show the shuffle button', 'radykal'); ?></span></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Randomize', 'radykal'); ?></th>
          	<td><input type="checkbox" name="randomize" value="1" <?php checked($audioplayer_options['randomize'], 1); ?> /> <span class="checkbox-label"><?php _e('Randomize default playlist', 'radykal'); ?></span></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Auto Pop-Up', 'radykal'); ?></th>
          	<td><input type="checkbox" name="auto_popup" value="1" <?php checked($audioplayer_options['auto_popup'], 1); ?> /> <span class="checkbox-label"><?php _e('Pop out player in a Pop-Up window, when site is loaded', 'radykal'); ?></span></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Sortable Playlist', 'radykal'); ?></th>
          	<td><input type="checkbox" name="sortable" value="1" <?php checked($audioplayer_options['sortable'], 1); ?> /> <span class="checkbox-label"><?php _e('Make playlist sortable via Drag & Drop', 'radykal'); ?></span></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Hide on mobile devices', 'radykal'); ?></th>
          	<td><input type="checkbox" name="hide_on_mobile" value="1" <?php checked($audioplayer_options['hide_on_mobile'], 1); ?> /> <span class="checkbox-label"><?php _e('Hide the audio player on all mobile devices', 'radykal'); ?></span></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Loop Playlist', 'radykal'); ?></th>
          	<td><input type="checkbox" name="loop_playlist" value="1" <?php checked($audioplayer_options['loop_playlist'], 1); ?> /> <span class="checkbox-label"><?php _e('When end of playlist has been reached, start from beginning?', 'radykal'); ?></span></td>
        </tr>
        <tr valign="top" class="sub-options last-sub-option">
        	<th scope="row"><?php _e('Store playlist in browser', 'radykal'); ?></th>
          	<td><input type="checkbox" name="store_playlist" value="1" <?php checked($audioplayer_options['store_playlist'], 1); ?> /> <span class="checkbox-label"><?php _e('Store the playlist in the browser, so when the user comes back to the website, he will see the same playlist since the last visit', 'radykal'); ?></span></td>
        </tr>
        
        <!-- Window onload, onready -->
        <tr valign="top" class="alternate">
          <th scope="row">
          	<?php _e('Initiate first when window is loaded', 'radykal'); ?>
          <td><input type="checkbox" name="init_on_window" value="1" <?php checked($audioplayer_options['init_on_window'], 1); ?> /> <span class="checkbox-label"><span><?php _e('When you get this alert message:<br /> <i style="color: #000;">SM2 failed to start. Flash missing, blocked or security error? Status: INIT_TIMEOUT</i>.<br /> Then check this checkbox, this could help to fix the issue, because the player will be initiated first, when the window is completely loaded.', 'radykal'); ?></span></td>
        </tr>
        
        <!-- Responsive -->
        <tr valign="top" class="">
          <th scope="row">
          	<?php _e('Responsive Layout', 'radykal'); ?>
          <td>
          	<input type="checkbox" name="responsive_layout" value="1" <?php checked($audioplayer_options['responsive_layout'], 1); ?> />
          	<span class="checkbox-label"><?php _e('Include additional CSS to make the player responsive.', 'radykal'); ?></span>
          </td>
        </tr>
        
        <!-- Layout -->
        <tr valign="top" class="alternate">
          <th scope="row">
          	<?php _e('Layout', 'radykal'); ?>
          <td>
	          <select name="layout">
      			<option value="fullwidth" <?php selected($audioplayer_options['layout'], 'fullwidth'); ?>><?php _e('Fullwidth', 'radykal'); ?></option>
      			<option value="boxed" <?php selected($audioplayer_options['layout'], 'boxed'); ?>><?php _e('Boxed', 'radykal'); ?></option>
      		</select>
      		<span><?php _e('Make the player fullwidth or boxed.', 'radykal'); ?></span>
          </td>
        </tr>
        
    </tbody>
</table>