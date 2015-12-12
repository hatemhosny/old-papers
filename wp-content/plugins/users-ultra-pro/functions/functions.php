<?php
// General Functions for Plugin
if (!defined('PHP_EOL')) {
    switch (strtoupper(substr(PHP_OS, 0, 3))) {
        // Windows
        case 'WIN':
            define('PHP_EOL', "\r\n");
			//echo "IS WINDOW SERVER";
            break;

        // Mac
        case 'DAR':
            define('PHP_EOL', "\r");
            break;

        // Unix
        default:
            define('PHP_EOL', "\n");
    }
}

//echo "OSD: " .PHP_OS;

if (!function_exists('is_post')) {

    function is_post() {
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
            return true;
        else
            return false;
    }

}


if(!function_exists('uultra_default_socail_links')) {
	
    function uultra_default_socail_links() 
	{
        add_filter('uultra_social_url_user_email', 'uultra_format_email_link');
        add_filter('uultra_social_url_twitter', 'uultra_format_twitter_link');
        add_filter('uultra_social_url_facebook', 'uultra_format_facebook_link');
        add_filter('uultra_social_url_googleplus', 'uultra_format_google_link');		
		add_filter('uultra_social_url_user_url', 'uultra_format_user_url_link');
    }
}

// Hooking default social url
uultra_default_socail_links();


if(!function_exists('uultra_format_email_link')) {
    function uultra_format_email_link($content){
        return 'mailto:'.$content;
    }
}

if(!function_exists('uultra_format_user_url_link')) 
{
    function uultra_format_user_url_link($content){
        return $content;
    }
}

if(!function_exists('uultra_format_twitter_link')) {
    function uultra_format_twitter_link($content){
        return 'http://twitter.com/'.$content;
    }
}

if(!function_exists('uultra_format_facebook_link')) {
    function uultra_format_facebook_link($content){
        return 'http://www.facebook.com/'.$content;
    }
}

if(!function_exists('uultra_format_google_link')) {
    function uultra_format_google_link($content){
        return 'https://plus.google.com/'.$content;
    }
}

if (!function_exists('is_in_post')) {

    function is_in_post($key='', $val='') {
        if ($key == '') {
            return false;
        } else {
            if (isset($_POST[$key])) {
                if ($val == '')
                    return true;
                else if ($_POST[$key] == $val)
                    return true;
                else
                    return false;
            }
            else
                return false;
        }
    }

}

if (!function_exists('is_get')) {

    function is_get() {
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'get')
            return true;
        else
            return false;
    }

}


if (!function_exists('is_in_get')) {

    function is_in_get($key='', $val='') {
        if ($key == '') {
            return false;
        } else {
            if (isset($_GET[$key])) {
                if ($val == '')
                    return true;
                else if ($_GET[$key] == $val)
                    return true;
                else
                    return false;
            }
            else
                return false;
        }
    }

}

if(!function_exists('not_null'))
{
    function not_null($value)
    {
        if (is_array($value))
        {
            if (sizeof($value) > 0)
                return true;
            else
                return false;
        }
        else
        {
            if ( (is_string($value) || is_int($value)) && ($value != '') && ($value != 'NULL') && (strlen(trim($value)) > 0))
                return true;
            else
                return false;
        }
    } 
}



if(!function_exists('get_value'))
{
    function get_value($key='')
    {
        if($key!='')
        {
            if(isset($_GET[$key]) && not_null($_GET[$key]))
            {
                if(!is_array($_GET[$key]))
                    return trim($_GET[$key]);
                else
                    return $_GET[$key];
            }
    
            else
                return '';
        }
        else
            return '';
    }
}


if (!function_exists('remove_script_tags')) {

    function remove_script_tags($text) {
        $text = str_ireplace("<script>", "", $text);
        $text = str_ireplace("</script>", "", $text);

        return $text;
    }

}


if(!function_exists('post_value'))
{
    function post_value($key='')
    {
        if($key!='')
        {
            if(isset($_POST[$key]) && not_null($_POST[$key]))
            {
                if(!is_array($_POST[$key]))
                    return trim($_POST[$key]);
                else
                    return $_POST[$key];
            }
            else
                return '';
        }
        else
            return '';
    }
}



if (!function_exists('uultra_date_picker_setting')) {

    function uultra_date_picker_setting() {
        // Set date format from admin settings
        $uultra_settings = get_option('userultra_options'); 
        $uult_date_format = (string) isset($uultra_settings['uultra_date_format']) ? $uultra_settings['uultra_date_format'] : 'mm/dd/yy';
		
		
		
        $date_picker_array = array(
            'closeText' => 'Done',
            'prevText' => 'Prev',
            'nextText' => 'Next',
            'currentText' => 'Today',
            'monthNames' => array(
                'Jan' => 'January',
                'Feb' => 'February',
                'Mar' => 'March',
                'Apr' => 'April',
                'May' => 'May',
                'Jun' => 'June',
                'Jul' => 'July',
                'Aug' => 'August',
                'Sep' => 'September',
                'Oct' => 'October',
                'Nov' => 'November',
                'Dec' => 'December'
            ),
            'monthNamesShort' => array(
                'Jan' => 'Jan',
                'Feb' => 'Feb',
                'Mar' => 'Mar',
                'Apr' => 'Apr',
                'May' => 'May',
                'Jun' => 'Jun',
                'Jul' => 'Jul',
                'Aug' => 'Aug',
                'Sep' => 'Sep',
                'Oct' => 'Oct',
                'Nov' => 'Nov',
                'Dec' => 'Dec'
            ),
            'dayNames' => array(
                'Sun' => 'Sunday',
                'Mon' => 'Monday',
                'Tue' => 'Tuesday',
                'Wed' => 'Wednesday',
                'Thu' => 'Thursday',
                'Fri' => 'Friday',
                'Sat' => 'Saturday'
            ),
            'dayNamesShort' => array(
                'Sun' => 'Sun',
                'Mon' => 'Mon',
                'Tue' => 'Tue',
                'Wed' => 'Wed',
                'Thu' => 'Thu',
                'Fri' => 'Fri',
                'Sat' => 'Fri'
            ),
            'dayNamesMin' => array(
                'Sun' => 'Su',
                'Mon' => 'Mo',
                'Tue' => 'Tu',
                'Wed' => 'We',
                'Thu' => 'Th',
                'Fri' => 'Fr',
                'Sat' => 'Sa'
            ),
            'weekHeader' => 'Wk',
            'dateFormat' => $uult_date_format
        );

        return $date_picker_array;
    }

}


if(!function_exists('is_opera'))
{
    function is_opera()
    {
        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        return preg_match('/opera/i', $user_agent);
    }
}

if(!function_exists('is_safari'))
{
    function is_safari()
    {
        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        return (preg_match('/safari/i', $user_agent) && !preg_match('/chrome/i', $user_agent));
    }
}


// Check with the magic quotes functionality Start
function stripslashess(&$item)
{
    $item = stripslashes($item);
}

if(get_magic_quotes_gpc())
{
    array_walk_recursive($_GET, 'stripslashess' );
    array_walk_recursive($_POST, 'stripslashess');
    array_walk_recursive($_SERVER, 'stripslashess');
}
if(!function_exists('is_active'))
{

/* Check if user is active before login  */
	function is_active($user_id) 
	{
		$checkuser = get_user_meta($user_id, 'usersultra_account_status', true);
		if ($checkuser == 'active')
			return true;
		return false;
	}
}


/* Add a metabox in admin menu page */
add_action( 'admin_head-nav-menus.php', 'uultran_add_nav_menu_metabox' );
function uultran_add_nav_menu_metabox() 
{
	add_meta_box( 'uultrallm', __( 'Users Ultra Pro Login/Logout links' ) , 'uultrallm_add_nav_menu_metabox', 'nav-menus', 'side', 'default' );
	
}


function uultrallm_add_nav_menu_metabox( $object ) {
		global $nav_menu_selected_id;
	
		$elems = array(	'#uultralogin#' => __( 'Log In' ), 
						'#uultralogout#' => __( 'Log Out' ), 
						'#uultraloginout#' => __( 'Log In' ) . '|' . __( 'Log Out' ), 
						'#uultraregister#' => __( 'Register' ) 
					);
					
		
		class uultralogItems {
		public $db_id = 0;
		public $object = 'uultralog';
		public $object_id;
		public $menu_item_parent = 0;
		public $type = 'custom';
		public $title;
		public $url;
		public $target = '';
		public $attr_title = '';
		public $classes = array();
		public $xfn = '';
	}
			
		$elems_obj = array();
		foreach ( $elems as $value => $title ) {
			$elems_obj[ $title ] 				= new uultralogItems();
			$elems_obj[ $title ]->object_id		= esc_attr( $value );
			$elems_obj[ $title ]->title			= esc_attr( $title );
			$elems_obj[ $title ]->url			= esc_attr( $value );
		}
	
		$walker = new Walker_Nav_Menu_Checklist( array() );
		?>
		<div id="login-links" class="loginlinksdiv">
	
			<div id="tabs-panel-login-links-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
				<ul id="login-linkschecklist" class="list:login-links categorychecklist form-no-clear">
					<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $elems_obj ), 0, (object) array( 'walker' => $walker ) ); ?>
				</ul>
			</div>
	
			<p class="button-controls">
				<span class="list-controls hide-if-no-js">
					<a href="javascript:void(0);" class="help" onclick="jQuery( '#help-login-links' ).toggle();"><?php _e( 'Help' ); ?></a>
					<span class="hide-if-js" id="help-login-links"><br /><a name="help-login-links"></a>
						<?php
						
							_e( '&#9725; You can add a redirection page after the user\'s login/logout simply adding a relative link after the link\'s keyword, example <code>#uultraloginout#index.php</code>.','xoousers');
							
							_e( '<br />&#9725; You can also add <code>%actualpage%</code> to redirect the user on the actual visited page, example : <code>#uultraloginout#%actualpage%</code>.','xoousers');
						
						
						?>
					</span>
				</span>
	
				<span class="add-to-menu">
					<input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-login-links-menu-item" id="submit-login-links" />
					<span class="spinner"></span>
				</span>
			</p>
	
		</div>
		<?php
	}

/* Modify the "type_label" */
add_filter( 'wp_setup_nav_menu_item', 'uultran_nav_menu_type_label' );
function uultran_nav_menu_type_label( $menu_item )
{
	$elems = array( '#uultralogin#', '#uultralogout#', '#uultraloginout#', '#uultraregister#' );
	if ( isset( $menu_item->object, $menu_item->url ) && 'custom'== $menu_item->object && in_array( $menu_item->url, $elems ) ) 
	{
		$menu_item->type_label = ( __('Login','xoousers') );
	}

	return $menu_item;
}

/* Used to return the correct title for the double login/logout menu item */
function uultra_loginout_title( $title ) 
{
	$titles = explode( '|', $title );
	if ( ! is_user_logged_in() ) 
	{
		return esc_html( isset( $titles[0] ) ? $titles[0] : $title );
		
	} else {
		
		return esc_html( isset( $titles[1] ) ? $titles[1] : $title );
	}
}

/* The main code, this replace the #keyword# by the correct links with nonce ect */
add_filter( 'wp_setup_nav_menu_item', 'uultra_setup_nav_menu_item' );
function uultra_setup_nav_menu_item( $item ) 
{
	global $pagenow;
	if ( $pagenow != 'nav-menus.php' && ! defined( 'DOING_AJAX' ) && isset( $item->url ) && strstr( $item->url, '#uultra' ) != '' ) {
		$item_url = substr( $item->url, 0, strpos( $item->url, '#', 1 ) ) . '#';
		$item_redirect = str_replace( $item_url, '', $item->url );
		if( $item_redirect == '%actualpage%') {
			$item_redirect = $_SERVER['REQUEST_URI'];
		}
		switch ( $item_url ) {
			case '#uultraloginout#' : 	
									$item_redirect = explode( '|', $item_redirect );
									if ( count( $item_redirect ) != 2 ) {
										$item_redirect[1] = $item_redirect[0];
									}
									for ( $i = 0; $i <= 1; $i++ ) {
										if ( '%actualpage%' == $item_redirect[ $i ] ) {
											$item_redirect[ $i ] = $_SERVER['REQUEST_URI'];
										}
									}
									$item->url = is_user_logged_in() ? wp_logout_url( $item_redirect[1] ) : wp_login_url( $item_redirect[0] );
									$item->title = uultra_loginout_title( $item->title ) ; break;
			case '#uultralogin#' : 	$item->url = wp_login_url( $item_redirect ); break;
			case '#uultralogout#' : 	$item->url = wp_logout_url( $item_redirect ); break;
			case '#uultraregister#' : 	if( is_user_logged_in() ) {
										$item->title = '#uultraregister#'; 
									} else {
										$item->url = site_url( 'wp-login.php?action=register', 'login' );
									}
									$item = apply_filters( 'uultraregister_item', $item );
									break;
		}
		$item->url = esc_url( $item->url );
	}
	return $item;
}

add_filter( 'wp_nav_menu_objects', 'uultra_nl_wp_nav_menu_objects' );
function uultra_nl_wp_nav_menu_objects( $sorted_menu_items ) 
{
	foreach ( $sorted_menu_items as $k => $item ) {
		if ( $item->title==$item->url && '#uultraregister#' == $item->title ) {
			unset( $sorted_menu_items[ $k ] );
		}
	}
	return $sorted_menu_items;
}
