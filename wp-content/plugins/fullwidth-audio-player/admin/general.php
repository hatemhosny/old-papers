<!-- General Options -->
<table class="widefat option-table" cellspacing="0">
    <tbody>
    
    	 <!-- Visibility -->
    	<tr valign="top" class="alternate">
          <th scope="row"><?php _e('Add player to... ', 'radykal'); ?></th>
          <td>
          	<select name="player_visibility">
      			<option value="all" <?php selected($general_options['player_visibility'], 'all'); ?>><?php _e('every page', 'radykal'); ?></option>
      			<option value="frontpage" <?php selected($general_options['player_visibility'], 'frontpage'); ?>><?php _e('only frontpage', 'radykal'); ?></option>
      			<option value="shortcodes" <?php selected($general_options['player_visibility'], 'shortcodes'); ?>><?php _e('shortcodes found', 'radykal'); ?></option>
      		</select>
          </td>
        </tr>
        
        <!-- Buttons -->
        <tr valign="top" class="no-border">
          <th scope="row"><?php _e('External Buttons', 'radykal'); ?></th>
          <td><div class="description"><?php printf(__('The buttons of the tracks and playlists in your post or page.<br /><a href="%1$s">Click here to edit the style sheet of these buttons</a>. You find them at the bottom of the CSS document.', 'radykal'), admin_url().'plugin-editor.php?file=fullwidth-audio-player%2Fcss%2Fjquery.fullwidthAudioPlayer.css&plugin=fullwidth-audio-player%2Ffullwidth-audio-player.php'); ?></div></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Play CSS Class', 'radykal'); ?></th>
          	<td><input type="text" name="play_css_class" class="widefat" value="<?php echo $general_options['play_css_class']; ?>" /></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Referral CSS Class', 'radykal'); ?></th>
          	<td><input type="text" name="referral_css_class" class="widefat" value="<?php echo $general_options['referral_css_class']; ?>" /></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Play Text', 'radykal'); ?></th>
          	<td><input type="text" name="play_button_text" class="widefat" value="<?php echo $general_options['play_button_text']; ?>" /></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Enqueue Text', 'radykal'); ?></th>
          	<td><input type="text" name="enqueue_button_text" class="widefat" value="<?php echo $general_options['enqueue_button_text']; ?>" /></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Referral Text', 'radykal'); ?></th>
          	<td><input type="text" name="referral_button_text" class="widefat" value="<?php echo $general_options['referral_button_text']; ?>" /></td>
        </tr>
        <tr valign="top" class="sub-options no-border">
        	<th scope="row"><?php _e('Login Text', 'radykal'); ?></th>
          	<td><input type="text" name="login_text" class="widefat" value="<?php echo $general_options['login_text']; ?>" /></td>
        </tr>
        <tr valign="top" class="sub-options last-sub-option">
          <th scope="row"><?php _e('Log in to download', 'radykal'); ?></th>
          <td>
          	<input type="checkbox" name="login_to_download" value="1" <?php checked($general_options['login_to_download'], 1); ?> />
          	<span class="checkbox-label"><?php _e('Only logged in users can download the tracks.', 'radykal'); ?></span>
          </td>
        </tr>
        
        <!-- Dimensions -->
        <tr valign="top" class="no-border alternate">
          <th scope="row"><?php _e('Dimensions', 'radykal'); ?></th>
          <td><div class="description"><?php _e('The dimensions of the images in the list/grid items that you add to your page or post.', 'radykal'); ?></div></td>
        </tr>
        <tr valign="top" class="sub-options no-border alternate">
        	<th scope="row"><?php _e('List Item Image Width', 'radykal'); ?></th>
          	<td><input type="text" size="3" name="list_image_width" value="<?php echo $general_options['list_image_width']; ?>" /> pixels</td>
        </tr>
        <tr valign="top" class="sub-options no-border alternate">
        	<th scope="row"><?php _e('List Item Image Height', 'radykal'); ?></th>
          	<td><input type="text" size="3" name="list_image_height" value="<?php echo $general_options['list_image_height']; ?>" /> pixels</td>
        </tr>
        <tr valign="top" class="sub-options no-border alternate">
        	<th scope="row"><?php _e('Grid Item Image Width', 'radykal'); ?></th>
          	<td><input type="text" size="3" name="grid_image_width" value="<?php echo $general_options['grid_image_width']; ?>" /> pixels</td>
        </tr>
        <tr valign="top" class="sub-options alternate last-sub-option">
        	<th scope="row"><?php _e('Grid Item Image Height', 'radykal'); ?></th>
          	<td><input type="text" size="3" name="grid_image_height" value="<?php echo $general_options['grid_image_height']; ?>" /> pixels</td>
        </tr>
        
        <!-- Base64 -->
        <tr valign="top" class="">
          <th scope="row"><?php _e('Hide track URL with base64', 'radykal'); ?></th>
          <td>
          	<input type="checkbox" name="base64" value="1" <?php checked($general_options['base64'], 1); ?> />
          	<span class="checkbox-label"><?php _e('You can hide the track URL with base64, so users can not download the track by viewing the source code.', 'radykal'); ?></span>
          </td>
        </tr>
        
        <!-- Base64 -->
        <tr valign="top" class="alternate">
          <th scope="row"><?php _e('Public posts', 'radykal'); ?></th>
          <td>
          	<input type="checkbox" name="public_posts" value="1" <?php checked($general_options['public_posts'], 1); ?> />
          	<span class="checkbox-label"><?php _e('Every track will be published as a public post.', 'radykal'); ?></span>
          </td>
        </tr>
    </tbody>
</table>