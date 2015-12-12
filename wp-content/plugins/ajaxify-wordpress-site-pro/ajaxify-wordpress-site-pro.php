<?php
/**
  * Plugin Name: Ajaxify WordPress Site Pro
  * Description: This will ajaxify your website. All the front end links will turns to ajaxify.
  * Ajaxify WordPress Site will load posts, pages, search etc. without reloading entire page.
  * Author: Soumi Das
  * Author URI: http://www.youngtechleads.com
  * EmailId: soumi.das1990@gmail.com/skype:soumibgb
  * Version: 1.5
  * Plugin URI: http://www.youngtechleads.com/ajaxify-wordpress-site-pro
  */

// Global VAriables
global $plugin_dir_path;
$plugin_dir_path = plugin_dir_url( __FILE__ );

/*Call 'aws_install' function at the time of plug-in install.*/
register_activation_hook( __FILE__, 'aws_pro_install' );

/**
 * Function Name: aws_install
 * Description: Save data to database at the time of plugin install.
 *
 */
function aws_pro_install() {
	$container_id = ( get_option( 'container-id' ) != '' ) ? get_option( 'container-id' ) : '#main' ;
	$mcdc = ( get_option( 'mcdc' ) != '' ) ? get_option( 'mcdc' ) : '#menu' ;
	$search_form = ( get_option( 'search-form' ) != '' ) ? get_option( 'search-form' ) : '.search-form' ;
	$transitionupdown = ( get_option( 'transitionupdown' ) != '' ) ? get_option( 'transitionupdown' ) : '' ;
	$transitionfade = ( get_option( 'transitionfade' ) != '' ) ? get_option( 'transitionfade' ) : '' ;
	$scrollTop = ( get_option( 'scrollTop' ) != '' ) ? get_option( 'scrollTop' ) : '' ;
	$ml_slider_id = ( get_option( 'ml_slider_id' ) != '' ) ? get_option( 'ml_slider_id' ) : 0 ;
	$disqussite = ( get_option( 'disqussite' ) != '' ) ? get_option( 'disqussite' ) : '' ;
	$sub_ajax_container = ( get_option( 'sub_ajax_container' ) != '' ) ? get_option( 'sub_ajax_container' ) : '' ;
	$loader = (get_option('loader') != '') ? get_option('loader') : '' ;

	update_option( 'container-id', $container_id );
	update_option( 'mcdc', $mcdc );
	update_option( 'search-form', $search_form );
	update_option( 'transitionupdown', $transitionupdown );
	update_option( 'transitionfade', $transitionfade );
	update_option( 'scrollTop', $scrollTop );
	update_option( 'ml_slider_id', $ml_slider_id );
	update_option( 'disqussite', $disqussite );
	update_option( 'sub_ajax_container', $sub_ajax_container );
	update_option('loader', $loader);
}


/*Call 'aws_option_link' function to Add a submenu link under Profile tab.*/
add_action( 'admin_menu', 'aws_pro_option_link' );

/**
 * Function Name: aws_option_link
 * Description: Add a submenu link under Settings tab.
 *
 */
function aws_pro_option_link() {
	$aws_page_hook = add_options_page( 'AWS Pro Options', 'AWS Pro Options', 'manage_options', 'aws-pro-options', 'aws_pro_option_form' );

	add_action( "admin_print_scripts-$aws_page_hook", 'aws_pro_admin_css' );
}

function aws_pro_admin_css() {
	global $plugin_dir_path;
	wp_enqueue_style( 'aws-style-css', $plugin_dir_path . '/css/aws-style.css' );
}
/**
 * Function name: aws_pro_option_form
 * Description: Show aws option form to admin, save data to wp_option table.
 */
function aws_pro_option_form() {
	global $plugin_dir_path;
	echo '<div class="wrap"><h2>AWS Options</h2>';

	/**
	 * Check whether the form submitted or not.
	 */
	if ( isset( $_POST['option-save'] ) ) {
		//Get the form value
		$ids    = trim( $_POST['no-ajax-ids'] );
		$container_id  = trim( $_POST['container-id'] );
		$mcdc = trim( $_POST['mcdc'] );
		$search_form = trim( $_POST['search_form'] );
		$transitionupdown = isset( $_POST['transitionupdown'] ) ? $_POST['transitionupdown'] : '';
		$transitionfade = isset( $_POST['transitionfade'] ) ? $_POST['transitionfade'] : '';
		$scrollTop = isset( $_POST['scrollTop'] ) ? $_POST['scrollTop'] : '';
		$loader = $_POST['loader'];
		$ml_slider_id = isset( $_POST['ml_slider_id'] ) ? $_POST['ml_slider_id'] : '';
		$disqussite = isset( $_POST['disqussite'] ) ? $_POST['disqussite'] : '';
		$sub_ajax_container = $_POST['sub_ajax_container'];

		if ( $container_id == '' || $mcdc == '' )
			echo '<p style="color:red">Data for * marked fields are mendatory.</p>';
		else {
			//Explode the value by comma(,).
			$ids_arr = explode( ',', $ids );

			//Remove spaces if any.
			foreach ( $ids_arr as $key => $id ) {
				$ids_arr[$key] = trim( $id );
			}
			$ids = implode( ',', $ids_arr );

			////Update the database
			update_option( 'no-ajax-ids', $ids );
			update_option( 'container-id', $container_id );
			update_option( 'mcdc', $mcdc );
			update_option( 'search-form', $search_form );
			update_option( 'transitionupdown', $transitionupdown );
			update_option( 'transitionfade', $transitionfade );
			update_option( 'scrollTop', $scrollTop );
			update_option('loader', $loader);
			update_option( 'ml_slider_id', $ml_slider_id );
			update_option( 'disqussite', $disqussite );
			update_option( 'sub_ajax_container', $sub_ajax_container );

			do_action( 'save_more_fields' );

			echo '<div class="updated" id="message"><p>Settings updated.</p></div>';
		}
	}
?>
	<!-- AWS option table start here -->
	<form id="option-form" method="post" name="option-form">
		<table id="aws-option-table">
			<tr>
				<td><strong>No ajax container IDs/CLASSes:<strong></td>
				<td>
					<textarea id="no_ajax_ids" name="no-ajax-ids"><?php echo get_option( 'no-ajax-ids' ); ?></textarea>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					Provide the ids or the classes, along with hash(#) or dot(.) as prefix respectively, of the parent tag / element whose child anchor(a)
					tags you don't want to handled by this(AWS Pro) plugin.
					<br />
					<b>NOTE:</b> multiple values must be separated by comma(,) without any space(s). eg: #id1,.class1,.class2,#id2
				</td>
			</tr>
			<tr>
				<td><strong>Ajax container ID/CLASS:*</strong></td>
				<td><input type="text" name="container-id" value="<?php echo get_option( 'container-id' ); ?>" /></td>
			</tr>
			<tr>
				<td></td>
				<td>ID/CLASS of the main container div whose data needs to be ajaxify. eg: <strong><i>#main</strong></i> or <strong><i>.content</strong></i> or <strong><i>#content</strong></i> or <strong><i>#page</strong></i> any one.</td>
			</tr>
			<tr>
				<td><strong>Multiple sub ajax container IDs/CLASSes:<strong></td>
				<td>
					<textarea id="sub-ajax-container" name="sub_ajax_container"><?php echo get_option( 'sub_ajax_container' ); ?></textarea>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					Provide the ids or the classes, along with hash(#) or dot(.) as prefix respectively, of the div or any element you want to be ajaxify.
					<br />
					<b>NOTE:</b> multiple values must be separated by comma(,) without any space(s). eg: #id1,.class1,.class2,#id2
				</td>
			</tr>
			<tr>
				<td><strong>Menu container ID/CLASS:*</strong></td>
				<td>
					<input type="text" name="mcdc" value="<?php echo get_option( 'mcdc' ); ?>" />
				</td>
			</tr>
			<tr>
				<td></td>
				<td>ID/CLASS of the div in which menu's ul, li present. eg: menu<br><strong>Example:</strong> if form tag class is menu then provide <strong><i>.menu-main</i></strong> if ID is menu the provide <strong><i>#menu</i></strong></td>
			</tr>
			<tr>
				<td><strong>Search form TAG ID/CLASS:*</strong></td>
				<td><input type="text" name="search_form" value="<?php echo get_option( 'search-form' ); ?>" /></td>
			</tr>
			<tr>
				<td></td>
				<td>To make your search ajaxify provide the search form ID/CLASS.<br><strong>Example:</strong> if form tag class is search-form then provide <strong><i>.search-form</i></strong> if ID is search-form the provide <strong><i>#search-form</i></strong></td>
			</tr>
			<tr>
				<td><strong>Container Transition Effect:</strong></td>
				<td>
					<input type="checkbox" value="1" name="transitionupdown" <?php checked( get_option('transitionupdown'), 1, true ); ?> />
					Toggle Width:
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input type="checkbox" value="1" name="transitionfade" <?php checked( get_option('transitionfade'), 1, true ); ?> />
					  Fade in out effect.
				</td>
			<tr>
			<tr>
				<td><strong>Loader Image:</strong></td>
				<td>
					<?php
						$loader = get_option('loader');
						$loaders = array(
									'ball-pulse',
									'ball-grid-pulse',
									'ball-clip-rotate',
									'ball-clip-rotate-pulse',
									'square-spin',
									'ball-clip-rotate-multiple',
									'ball-rotate',
									'cube-transition',
									'ball-zig-zag',
									'ball-zig-zag-deflect',
									'ball-triangle-path',
									'ball-scale',
									'line-scale',
									'line-scale-party',
									'ball-scale-multiple',
									'ball-pulse-sync',
									'ball-beat',
									'line-scale-pulse-out',
									'line-scale-pulse-out-rapid',
									'ball-scale-ripple',
									'ball-scale-ripple-multiple',
									'ball-spin-fade-loader',
									'line-spin-fade-loader',
									'triangle-skew-spin',
									'pacman',
									'ball-grid-beat',
									'semi-circle-spin',
								)
					?>
					<select name="loader">
						<?php
						foreach ( $loaders as $value ) {
							$selected = '';
								if ( $value == $loader ) {
									$selected = "selected";
								}
								echo "<option value='", $value, "'", $selected, ">", $value, "</option>\n";
						}
						?>
					</select>
				</td>
			</tr>
			</tr>
				<td><strong>Enable scroll to top Effect:</strong></td>
				<td><input type="checkbox" name="scrollTop" value="1" <?php checked( get_option( 'scrollTop' ), 1, true ); ?> /></td>
			</tr>
			<?php if ( is_plugin_active( 'ml-slider/ml-slider.php' ) ) { ?>
			<tr>
				<td><strong>Meta Slider numerical ID:</strong></td>
				<td><input type="text" name="ml_slider_id" value="<?php echo get_option( 'ml_slider_id' ); ?>" /></td>
			</tr>
			<?php } ?>
			<?php if ( is_plugin_active( 'disqus-comment-system/disqus.php' ) ) { ?>
			<tr>
				<td><strong>Disqus Forum Shortname:</strong></td>
				<td><input type="text" name="disqussite" value="<?php echo get_option( 'disqussite' ); ?>" /></td>
			</tr>
			<?php } ?>
			<?php do_action( 'add_more_fields' ); ?>
			<tr>
				<td></td>
				<td>
					<input class="button" id="option-save" name="option-save" type="submit" value="Save options"/>
				</td>
			</tr>
		</table>
	</form>
	<!-- AWS option table end here -->
	<div style="float: left;">
		<h3>Quick Links</h3>
		<p><a href="http://www.youngtechleads.com/ajaxify-wordpress-site-pro">AWS Pro plugin page</a></p>
		<p><a href="http://www.youngtechleads.com/install-ajaxify-wordpress-site-pro">Installation guide</a></p>
		<p><a href="http://www.youngtechleads.com/ajaxify-wordpress-site-pro-faq">FAQ</a></p>
		<h3>Contact</h3>
		<p><strong>Skype:</strong> mfsi_manish</p>
		<p><strong>Mail:</strong> <a href="mailto:manishkrag@yahoo.co.in">manishkrag@yahoo.co.in</a></p>
	</div>
	<div class="loader-animation">
		<iframe src="<?php echo $plugin_dir_path; ?>loader-animation/demo.html"></iframe>
	</div>

	<script>
	jQuery('#option-save').click(function(event) {
		var err = 0;
		jQuery('#aws-option-table input').each(function(){
			if(jQuery(this).val() == '') {
				err = 1;
				jQuery(this).css('border-color', '#ff0000');
			}
		});
		if( err === 1 ) {
			event.preventDefault();
			return false;
		}
	});
	</script>
	</div>
	<?php

} //End of aws_pro_option_form function

//calling aws_load_scripts function to load js files
add_action( 'wp_enqueue_scripts', 'aws_pro_load_scripts' );

/**
 * Function name: aws_load_scripts
 * Description: Loading the required js files and assing required php variable to js variable.
 */
function aws_pro_load_scripts() {
	global $plugin_dir_path;
	//Check whether the core jqury library enqued or not. If not enqued the enque this
	if ( !wp_script_is( 'jquery' ) ) {
		wp_enqueue_script( 'jquery' );
	}

	wp_enqueue_script( 'aws-jsui-js', 'http://code.jquery.com/ui/1.10.3/jquery-ui.js', array( 'jquery' ) );
	wp_enqueue_script( 'aws-history-js', $plugin_dir_path . 'js/history.js', array( 'jquery' ) );
	wp_enqueue_script( 'aws-ajaxify-js',  $plugin_dir_path . 'js/ajaxify.js', array( 'jquery' ) );
	wp_enqueue_script( 'aws-customjs-js',  $plugin_dir_path . 'js/customjs.js', array( 'jquery' ) );
	wp_enqueue_style( 'aws-style-css', $plugin_dir_path . 'css/aws-style.css' );
	wp_enqueue_style( 'aws-loader-css', $plugin_dir_path . 'css/loaders.min.css' );

	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	$bp_status = is_plugin_active( 'buddypress/bp-loader.php' );
	$ml_slider = is_plugin_active( 'ml-slider/ml-slider.php' );
	$nivo = is_plugin_active( 'nivo-slider-for-wordpress/nivoslider4wp.php' );
	$meteor_slides = is_plugin_active( 'meteor-slides/meteor-slides-plugin.php' );
	$rt_prettyphoto = is_plugin_active( 'rt-prettyphoto/rt-prettyphoto.php' );
	$contact_form_7 = is_plugin_active( 'contact-form-7/wp-contact-form-7.php' );
	$disqus = is_plugin_active( 'disqus-comment-system/disqus.php' );
	$fancybox = is_plugin_active( 'easy-fancybox/easy-fancybox.php' );

	$ids = array();
	$ids_arr = explode( ',', get_option( 'no-ajax-ids' ) );
	foreach ( $ids_arr as $key => $id ) {
		if ( trim( $id ) == '' )
			unset( $ids_arr[$key] );
		else
			$ids_arr[$key] =  trim( $id ) . ' a';
	}

	if ( $bp_status ) {
		$ids_arr[] = '.load-more a';
		$ids_arr[] = '.activity-meta a';
		$ids_arr[] = '.acomment-options a';
	}
	$ids = implode( ',', $ids_arr );
	$type = '';
	$theme = '';

	if ( $ml_slider ) {
		$ml_slider_id = get_option( 'ml_slider_id' );
		$ml_slider_settings = get_post_meta( $ml_slider_id, 'ml-slider_settings' );
		$type = $ml_slider_settings[0]['type'];
		$theme = $ml_slider_settings[0]['theme'];
	}

	$sub_ajax_container = get_option( 'sub_ajax_container' );
	if ( '' != $sub_ajax_container ) {
		$sub_ajax_container = explode( ',', $sub_ajax_container );
	} else{
		$sub_ajax_container = array();
	}


	$aws_data = array(
		'rootUrl' => site_url() . '/',
		'ids' => $ids,
		'container_id' => get_option( 'container-id' ),
		'sub_ajax_container' => $sub_ajax_container,
		'mcdc' => get_option( 'mcdc' ),
		'searchID' => get_option( 'search-form' ),
		'transitionupdown' => get_option( 'transitionupdown' ),
		'transitionfade' => get_option( 'transitionfade' ),
		'scrollTop' => get_option( 'scrollTop' ),
		'loader' => get_option('loader'),
		'plugin_dir_path' => $plugin_dir_path,
		'bp_status' => $bp_status,
		'disqus' => $disqus,
		'disqussite' => trim( get_option( 'disqussite' ) ),
		'ml_slider' => $ml_slider,
		'rt_prettyphoto' => $rt_prettyphoto,
		'fancybox' => $fancybox,
		'contact_form_7' => $contact_form_7,
		'type' => $type,
		'theme' => $theme,
		'nivo' => $nivo,
		'meteor_slides' => $meteor_slides,
	);

	wp_localize_script( 'aws-ajaxify-js', 'aws_data', $aws_data );
} // End of aws_load_scripts function