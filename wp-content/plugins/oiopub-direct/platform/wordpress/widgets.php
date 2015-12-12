<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();


class oiopub_widgets {

	function oiopub_widgets() {
		//load new widgets
		add_action('widgets_init', create_function('', 'register_widget("OIO_WP_Widget");'));
		//load old widgets?
		if(class_exists('oiopub_widgets_old')) {
			new oiopub_widgets_old();
		}
	}

}


/**
 * Widget OIO Ads Class
 *
 * @since 0.1.0
 *
 * @author FAPE
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 */

class OIO_WP_Widget extends WP_Widget {

	/**
	 * Set up the widget's unique name, ID, class, description, and other options.
	 * @since 0.1.0
	 */
	function __construct() {
		//widget options
		$widget_opts = array(
			'classname' => 'oio',
			'description' => __('Displays Ads from OIOpublisher.'),
		);
		//init widget
		parent::__construct('oio-widget', __('OIO Ad Zone'), $widget_opts);
	}

	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 * @since 0.1.0
	 */
	function widget($args, $instance) {
		//set vars
		extract($args);
		//get zone vars
		$zone_id = (int) $instance['zone'];
		$zone_func = 'oiopub_' . strtolower($instance['type']) . '_zone';
		//does function exist?
		if(!function_exists($zone_func)) {
			return;
		}
		//before widget
		echo $before_widget;
		//set title?
		if($instance['title']) {
			echo $before_title . apply_filters('widget_title', $instance['title']) . $after_title;
		} else {
			/* echo preg_replace("/<h[0-9].*?>(.*)?<\/h[0-9]>/imU", "$1", $before_title . $after_title); */
		}
		//display ad zone
		echo $zone_func($zone_id, array(
			'align' => (string) $instance['align'],
			'shuffle' => (bool) ($instance['shuffle'] !== 'No'),
			'empty' => (int) $instance['empty'],
			'fluid' => (bool) ($instance['fluid'] !== 'No'),
		));
		//after widget
		echo $after_widget;
	}

	/**
	 * Updates the widget control options for the particular instance of the widget.
	 * @since 0.1.0
	 */
	function update($new_instance, $old_instance) {
		return array_map('strip_tags', $new_instance);
	}

	/**
	 * Displays the widget control options in the Widgets admin screen.
	 * @since 0.1.0
	 */
	function form($instance) {
		//set defaults
		$defaults = array(
			'type' => 'banner',
			'zone' => '1',
			'title' => '',
			'align' => 'center',
			'empty' => '1',
			'shuffle' => true,
			'fluid' => true,
		);
		$instance = (array) $instance;
		$instance = wp_parse_args($instance, $defaults);
		?>
		<div class="hybrid-widget-controls columns-1">
		<p>
			<label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e( 'Zone type' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>">
				<?php foreach(array( 'banner' => "Banner Ad", 'link' => "Text Ad" ) as $option_value => $option_label) { ?>
					<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $instance['type'], $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'zone' ); ?>"><?php _e( 'Zone ID' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'zone' ); ?>" name="<?php echo $this->get_field_name( 'zone' ); ?>" value="<?php echo esc_attr( $instance['zone'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Zone title (optional)' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'align' ); ?>"><?php _e( 'Zone alignment'); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'align' ); ?>" name="<?php echo $this->get_field_name( 'align' ); ?>">
				<?php foreach(array( 'center', 'left', 'right', 'none' ) as $option_value => $option_label ) { ?>
					<option value="<?php echo esc_attr( $option_label ); ?>" <?php selected( $instance['align'], $option_label ); ?>><?php echo esc_html( $option_label ); ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'empty' ); ?>"><?php _e( 'Max empty ad slots to show?' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'empty' ); ?>" name="<?php echo $this->get_field_name( 'empty' ); ?>">
				<?php foreach(array( '0' => "None", '1' => "One", '-1' => "All" ) as $option_value => $option_label) { ?>
					<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $instance['empty'], $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'shuffle' ); ?>"><?php _e( 'Shuffle ads on each page load?' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'shuffle' ); ?>" name="<?php echo $this->get_field_name( 'shuffle' ); ?>">
				<?php foreach(array( "Yes", "No" ) as $option_value => $option_label) { ?>
					<option value="<?php echo esc_attr( $option_label ); ?>" <?php selected( $instance['shuffle'], $option_label ); ?>><?php echo esc_html( $option_label ); ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'fluid' ); ?>"><?php _e( 'Use fluid width?' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'fluid' ); ?>" name="<?php echo $this->get_field_name( 'fluid' ); ?>">
				<?php foreach(array( "Yes", "No" ) as $option_value => $option_label) { ?>
					<option value="<?php echo esc_attr( $option_label ); ?>" <?php selected( $instance['fluid'], $option_label ); ?>><?php echo esc_html( $option_label ); ?></option>
				<?php } ?>
			</select>
		</p>
		</div>
		<?php
	}

}


//old widgets (deprecated)

class oiopub_widgets_old {

	//init
	function oiopub_widgets_old() {
		//add widgets
		$this->widgets();
	}

	//widgets
	function widgets() {
		//function exists?
		if(!function_exists('wp_register_sidebar_widget')) {
			return;
		}
		//core widgets
		wp_register_sidebar_widget('oio-ad-badge', 'OIO Ad Badge', array(&$this, 'ad_badge'));
		wp_register_sidebar_widget('oio-availabile-ads', 'OIO Available Ads', array(&$this, 'ad_slots'));
		//wp_register_sidebar_widget('oio-link-zone', 'OIOpub Link Zone', array(&$this, 'link_zone'));
		//wp_register_sidebar_widget('oio-banner-zone', 'OIOpub Banner Zone', array(&$this, 'banner_zone'));
		wp_register_widget_control('oio-ad-badge', 'OIO Ad Badge', array(&$this, 'ad_badge_edit'));
		wp_register_widget_control('oio-availabile-ads', 'OIO Available Ads', array(&$this, 'ad_slots_edit'));
		//wp_register_widget_control('oio-link-zone', 'OIOpub Link Zone', array(&$this, 'link_zone_edit'));
		//wp_register_widget_control('oio-banner-zone', 'OIOpub Banner Zone', array(&$this, 'banner_zone_edit'));
	}
	
	//ad badge
	function ad_badge($args) {
		if(function_exists('oiopub_ad_badge')) {
			extract($args);
			$options = get_option('widget_oiopub1');
			$res = oiopub_ad_badge($options['image'], $options['width'], $options['height']);
			if(!empty($res)) {
				echo $before_widget . "\n";
				echo $res . "\n";
				echo $after_widget . "\n";
			}
		}
	}
	
	//ad badge edit
	function ad_badge_edit() {
		$options = get_option('widget_oiopub1');
		if(isset($_POST['oiopub_ad_badge']) && $_POST['oiopub_ad_badge'] == "yes1") {
			$options['image'] = oiopub_clean($_POST['oiopub_ad_badge_image']);
			$options['width'] = intval($_POST['oiopub_ad_badge_width']);
			$options['height'] = intval($_POST['oiopub_ad_badge_height']);
			update_option('widget_oiopub1', $options);
		}
		echo '<input type="hidden" name="oiopub_ad_badge" value="yes1" />';
		echo '<table width="100%" border="0">';
		echo '<tr><td>Image URL:</td><td><input type="text" name="oiopub_ad_badge_image" value="' . $options['image'] . '" /></td></tr>';
		echo '<tr><td>Width:</td><td><input type="text" name="oiopub_ad_badge_width" value="' . $options['width'] . '" /></td></tr>';
		echo '<tr><td>Height:</td><td><input type="text" name="oiopub_ad_badge_height" value="' . $options['height'] . '" /></td></tr>';
		echo '<tr><td></td><td><input type="submit" value="Update Settings" /></td></tr>';
		echo '</table>';	
	}
	
	//ad slots
	function ad_slots($args) {
		if(function_exists('oiopub_ad_slots')) {
			extract($args);
			$options = get_option('widget_oiopub3');
			if(!empty($options['title'])) {
				$title = $before_title . $options['title'] . $after_title;
			} else {
				$title = "";
			}
			$res = oiopub_ad_slots($title);
			if(!empty($res)) {
				echo $before_widget . "\n";
				echo $res . "\n";
				echo $after_widget . "\n";
			}
		}
	}

	//ad slots edit
	function ad_slots_edit() {
		$options = get_option('widget_oiopub3');
		if(isset($_POST['oiopub_ad_slots']) && $_POST['oiopub_ad_slots'] == "yes3") {
			$options['title'] = strip_tags($_POST['oiopub_ad_slots_title']);
			update_option('widget_oiopub3', $options);
		}
		echo '<input type="hidden" name="oiopub_ad_slots" value="yes3" />';
		echo '<table width="100%" border="0">';
		echo '<tr><td>Title:</td><td><input type="text" name="oiopub_ad_slots_title" value="' . stripslashes($options['title']) . '" /></td></tr>';
		echo '<tr><td></td><td><input type="submit" value="Update Settings" /></td></tr>';
		echo '</table>';
	}
	
	//link zone
	function link_zone($args) {
		if(function_exists('oiopub_link_zone')) {
			extract($args);
			$options = get_option('widget_oiopub2');
			$zone = (empty($options['zone']) ? 1 : $options['zone']);
			$position = (empty($options['position']) ? "center" : $options['position']);
			if(!empty($options['title'])) {
				$title = $before_title . $options['title'] . $after_title;
			} else {
				$title = "";
			}
			$res = oiopub_link_zone($zone, $position, $title, 1);
			if(!empty($res)) {
				echo $before_widget . "\n";
				echo $res . "\n";
				echo $after_widget . "\n";
			}
		}
	}
	
	//link zone edit
	function link_zone_edit() {
		$options = get_option('widget_oiopub2');
		if(isset($_POST['oiopub_link_zone']) && $_POST['oiopub_link_zone'] == "yes2") {
			$options['zone'] = intval($_POST['oiopub_link_zone_zone']);
			$options['title'] = strip_tags($_POST['oiopub_link_zone_title']);
			$options['position'] = oiopub_clean($_POST['oiopub_link_zone_position']);
			update_option('widget_oiopub2', $options);
		}
		$options['zone'] = (empty($options['zone']) ? 1 : $options['zone']);
		$options['position'] = (empty($options['position']) ? "center" : $options['position']);
		$pos_array = array( "left", "center", "right" );
		echo '<input type="hidden" name="oiopub_link_zone" value="yes2" />';
		echo '<table width="100%" border="0">';
		echo '<tr><td>Zone ID:</td><td><input type="text" name="oiopub_link_zone_zone" value="' . $options['zone'] . '" /></td></tr>';
		echo '<tr><td>Title:</td><td><input type="text" name="oiopub_link_zone_title" value="' . stripslashes($options['title']) . '" /></td></tr>';
		echo '<tr><td>Position:</td><td>' . oiopub_dropmenu_k($pos_array, "oiopub_link_zone_position", $options['position']) . '</td></tr>';
		echo '<tr><td></td><td><input type="submit" value="Update Settings" /></td></tr>';
		echo '</table>';
		echo '<small>To add more ad zones using widgets, please install <a href="http://wordpress.org/extend/plugins/php-code-widget/" target="_blank">this plugin</a>, which lets you use the OIOpub link <a href="admin.php?page=oiopub-opts.php&opt=link" target="_blank">output code</a> in widgets easily (php version).</small>';
	}
	
	//banner zone
	function banner_zone($args) {
		if(function_exists('oiopub_banner_zone')) {
			extract($args);
			$options = get_option('widget_oiopub4');
			$zone = (empty($options['zone']) ? 1 : $options['zone']);
			$position = (empty($options['position']) ? "center" : $options['position']);
			if(!empty($options['title'])) {
				$title = $before_title . $options['title'] . $after_title;
			} else {
				$title = "";
			}
			$res = oiopub_banner_zone($zone, $position, $title, 1);
			if(!empty($res)) {
				echo $before_widget . "\n";
				echo $res . "\n";
				echo $after_widget . "\n";
			}
		}
	}
	
	//banner zone edit
	function banner_zone_edit() {
		$options = get_option('widget_oiopub4');
		if(isset($_POST['oiopub_banner_zone']) && $_POST['oiopub_banner_zone'] == "yes4") {
			$options['zone'] = intval($_POST['oiopub_banner_zone_zone']);
			$options['title'] = strip_tags($_POST['oiopub_banner_zone_title']);
			$options['position'] = oiopub_clean($_POST['oiopub_banner_zone_position']);
			update_option('widget_oiopub4', $options);
		}
		$options['zone'] = (empty($options['zone']) ? 1 : $options['zone']);
		$options['position'] = (empty($options['position']) ? "center" : $options['position']);
		$pos_array = array( "left", "center", "right" );
		echo '<input type="hidden" name="oiopub_banner_zone" value="yes4" />';
		echo '<table width="100%" border="0">';
		echo '<tr><td>Zone ID:</td><td><input type="text" name="oiopub_banner_zone_zone" value="' . $options['zone'] . '" /></td></tr>';
		echo '<tr><td>Title:</td><td><input type="text" name="oiopub_banner_zone_title" value="' . stripslashes($options['title']) . '" /></td></tr>';
		echo '<tr><td>Position:</td><td>' . oiopub_dropmenu_k($pos_array, "oiopub_banner_zone_position", $options['position']) . '</td></tr>';
		echo '<tr><td></td><td><input type="submit" value="Update Settings" /></td></tr>';
		echo '</table>';
		echo '<small>To add more ad zones using widgets, please install <a href="http://wordpress.org/extend/plugins/php-code-widget/" target="_blank">this plugin</a>, which lets you use the OIOpub banner <a href="admin.php?page=oiopub-opts.php&opt=banner" target="_blank">output code</a> in widgets easily (php version).</small>';
	}

}