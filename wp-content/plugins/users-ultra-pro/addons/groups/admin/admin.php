<?php
class UsersUltraGroup {

	var $options;
	
	var $_aPostableTypes = array(
        'post',
        'page',
        'attachment',
    );

	function __construct() {
	
		/* Plugin slug and version */
		$this->slug = 'userultra';
		$this->subslug = 'uultra-groups';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$this->plugin_data = get_plugin_data( uultra_groups_path . 'index.php', false, false);
		$this->version = $this->plugin_data['Version'];
		
		/* Priority actions */
		add_action('admin_menu', array(&$this, 'add_menu'), 9);
		add_action('admin_enqueue_scripts', array(&$this, 'add_styles'), 9);
		add_action('admin_head', array(&$this, 'admin_head'), 9 );
		add_action('admin_init', array(&$this, 'admin_init'), 9);
		
		add_action( 'wp_ajax_edit_group', array(&$this, 'edit_group' ));
		add_action( 'wp_ajax_edit_group_conf', array(&$this, 'edit_group_conf' ));
		add_action( 'wp_ajax_edit_group_del', array(&$this, 'edit_group_del' ));
		
		add_action( 'save_post',  array( &$this, 'uultra_save_post_groups' ), 65);	
		add_filter('the_posts', array(&$this, 'uultra_showPost'));	
		add_filter('get_pages', array(&$this, 'uultra_showPage'));		
		
		
		$this->uultra_post_protection();		
		
		
		
	}
	
	
	function uultra_post_protection() 
	{
	
		 add_filter('manage_posts_columns', array(&$this, 'addPostColumnsHeader'));
		 add_filter('manage_pages_columns', array(&$this, 'addPostColumnsHeader'));
		 add_action( 'add_meta_boxes', array(&$this, 'uultra_post_protection_add_meta_box' ));			 		
		
	}
	
	public function checkAccessToPost($post_id) 
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH. 'wp-admin/includes/user.php' );
		
		$res = true;
		
		$post_groups = $this->get_all_post_groups($post_id);
		
		//print_r($post_groups);
		
		//if this post in a group?		
		if (count($post_groups) == 0 ) 
		{
			$res = true;
			
		}else{
			
			//this post is in some group
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;
			
			//is this the super admin?
			if(is_super_admin( $user_id ))
			{
				$res = true;
			
			}else{
				
				//is this user allowed to see this post.				
				$user_groups = $this->get_all_user_groups($user_id);
				
				foreach ($user_groups as $group)
			 	{					
					if(in_array($group, $post_groups))
					{
						return true; //user belongs to this group					
					}				
				
				}
				
				$res = false;		
			
			}		
		
		}
		
		return $res;
		
	
	}
	
	  /**
     * The function for the get_pages filter.
     * 
     * @param array $aPages The pages.
     * 
     * @return array
     */
    public function uultra_showPage($aPages = array())
    {
		global $xoouserultra;
		
        $aShowPages = array();       
       
        foreach ($aPages as $oPage) 
		{
            if ($xoouserultra->get_option('uultra_groups_hide_complete_page') == 'yes'   ) 
			{
				
               // $oPage->post_title .= $this->adminOutput( $oPage->post_type, $oPage->ID );
			   
			    if ($this->checkAccessToPost($oPage->ID)) 
				{
               	 	$aShowPages[] = $oPage;  
				
				}            
				
				
            } else {
				
                if (!$this->checkAccessToPost($oPage->ID)) 
				{
					if ($xoouserultra->get_option('uultra_groups_hide_page_title') == 'yes') 
					{
						$oPage->post_title =$xoouserultra->get_option('uultra_groups_page_title');
					}

                    $oPage->post_content = $xoouserultra->get_option('uultra_groups_page_content');;
                }

               // $oPage->post_title .= $this->adminOutput($oPage->post_type, $oPage->ID);
                $aShowPages[] = $oPage;
            }
        }
        
        $aPages = $aShowPages;
        
        return $aPages;
    }
	
	 /**
     * Modifies the content of the post by the given settings.
     * 
     * @param object $oPost The current post.
     * 
     * @return object|null
     */
    protected function _getPost($oPost)
    {
		global $xoouserultra;
		
       
        $sPostType = $oPost->post_type;

		if ($sPostType != 'post' && $sPostType != 'page')
		{			
			   $sPostType = 'post';
			
        } elseif ($sPostType != 'post' && $sPostType != 'page') 
		{
            return $oPost;
        }
        
        if ($xoouserultra->get_option('uultra_groups_hide_complete_post') == 'yes' ) 
		{
           
		    if ($this->checkAccessToPost($oPost->ID)) 
			{
				return $oPost;
			
			}
            
			
        } else {
			
            if (!$this->checkAccessToPost($oPost->ID)) 
			{
                $oPost->isLocked = true;
                
                $uultraPostContent = $xoouserultra->get_option('uultra_groups_'.$sPostType.'_content');
                
                if ($xoouserultra->get_option('uultra_groups_hide_'.$sPostType.'_title') == 'yes') 
				{
                    $oPost->post_title =$xoouserultra->get_option('uultra_groups_'.$sPostType.'_title');
                }
                
                if ($xoouserultra->get_option('uultra_groups_allow_'.$sPostType.'_comments') == 'no')
				{
                    $oPost->comment_status = 'close';
                }

                if ($xoouserultra->get_option('uultra_groups_post_content_before_more') == 'yes'
                    && $sPostType == "post" && preg_match('/<!--more(.*?)?-->/', $oPost->post_content, $aMatches)
                ) 
				{
                    $oPost->post_content = explode($aMatches[0], $oPost->post_content, 2);
                    $uultraPostContent = $oPost->post_content[0] . " " . $uultraPostContent;
                }

                $oPost->post_content = stripslashes($uultraPostContent);
            }

            
            return $oPost;
        }
		
		
        
        return null;
    }
    
	
	 /**
     * The function for the the_posts filter.
     * 
     * @param array $aPosts The posts.
     * 
     * @return array
     */
    public function uultra_showPost($aPosts = array())
    {
		global $xoouserultra;
        $aShowPosts = array();		
       
        if (!is_feed() || ($xoouserultra->get_option('uultra_groups_protect_feed') == 'yes'  && is_feed())) 
		{
			//echo "HERE ";
            foreach ($aPosts as $iPostId)
			 {
                if ($iPostId !== null) 
				{
                    $oPost = $this->_getPost($iPostId);

                    if ($oPost !== null)
					{
                        $aShowPosts[] = $oPost;
                    }
                }
            }

            $aPosts = $aShowPosts;
        }
        
        return $aPosts;
    }
	
	function uultra_save_post_groups( $post_id ) 
	{	
		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave($post_id) )
			return;
			
				 
		 $post = get_post($post_id);
        if($post->post_status == 'trash' ){
                return $post_id;
        }
		
		$aFormData = array();
		
		
		 if (isset($_POST['uultra_update_groups'])) {
            $aFormData = $_POST;
			
        } elseif (isset($_GET['uultra_update_groups'])) {
			
            $aFormData = $_GET;
        }
				
		
		if (isset($aFormData['uultra_update_groups']))
		{
			$selected_groups = isset($aFormData['uultra_group_list']) ? $aFormData['uultra_group_list'] : array();
			 
			//loop through selected groups
			$this->post_group_rel($post_id);
			 
			foreach ($selected_groups as $group_id) 
			{
				$this->save_post_group_rel($post_id, $group_id);			 
			
			}	
		}			
	}
	
	public function post_group_rel ($post_id)
	{
		global $wpdb;
		
		$group_id = $_POST["group_id"];		
		$query = "DELETE FROM " . $wpdb->prefix ."usersultra_groups_post_rel  WHERE  `group_post_rel_post_id` = '$post_id'  ";					
		$wpdb->query( $query );	
	}
	
	public function groups_and_users_rel_del ($user_id)
	{
		global $wpdb;
		
		$group_id = $_POST["group_id"];		
		$query = "DELETE FROM " . $wpdb->prefix ."usersultra_groups_users_groups_rel  WHERE  `group_user_group_rel_user_id` = '$user_id'  ";					
		$wpdb->query( $query );	
	}
	
	public function save_post_group_rel($post_id, $group_id)
	{
		global $wpdb;	

		$new_array = array(
							'group_post_rel_id'     => null,
							'group_post_rel_post_id'   => $post_id,
							'group_post_rel_group_id'   => $group_id						
							
						);						
				
		$wpdb->insert( $wpdb->prefix . 'usersultra_groups_post_rel', $new_array, array( '%d', '%s' , '%s'));
		
	}
	
	public function save_user_group_rel($user_id, $group_id)
	{
		global $wpdb;
		
		//check if already added to the group
		$sql = ' SELECT * FROM ' . $wpdb->prefix . 'usersultra_groups_users_groups_rel WHERE group_user_group_rel_user_id= "'.$user_id.'"  AND group_user_group_rel_group_id= "'.$group_id.'"  ' ;
		$res = $wpdb->get_results($sql);
		
		if ( empty( $res ) )
		{	
	
			$new_array = array(
								'group_user_group_rel_id'     => null,
								'group_user_group_rel_user_id'   => $user_id,
								'group_user_group_rel_group_id'   => $group_id						
								
							);						
					
			$wpdb->insert( $wpdb->prefix . 'usersultra_groups_users_groups_rel', $new_array, array( '%d', '%s' , '%s'));
		
		}
		
	}
	
	
	public function get_all_users_group_total( $group_id)
	{
		global $wpdb;
		
		//check if already added to the group
$sql = ' SELECT count(*) as total FROM ' . $wpdb->prefix . 'usersultra_groups_users_groups_rel WHERE group_user_group_rel_group_id= "'.$group_id.'"  ' ;
		$rows = $wpdb->get_results($sql);
		
		if ( !empty( $rows ) )
		{
			foreach ( $rows as $item )
			{
				$total = $item->total;			
			
			}
		
		}else{
			
			$total = 0;	
			
	    }
			
		return $total;
		
	}
	function admin_init() 
	{
	
		$this->tabs = array(
			'manage' => __('Manage Groups','xoousers')
			
		);
		$this->default_tab = 'manage';		
		
	}
	
	
	function uultra_post_protection_add_meta_box() 
	{
		$this->_aPostableTypes = array_merge($this->_aPostableTypes, get_post_types(array('publicly_queryable' => true), 'names'));
        $this->_aPostableTypes = array_unique($this->_aPostableTypes);
	
		$aPostableTypes = $this->getPostableTypes();
                
        foreach ($aPostableTypes as $sPostableType) 
		{
			add_meta_box('uultra_post_access', 'Users Ultra Group Access', array(&$this, 'editPostContent'), $sPostableType, 'side');
			
        }
		
	}
	
	public function editPostContent($oPost)
    {
		
        $iObjectId = $oPost->ID;
	   
		if (isset($_GET['attachment_id'])) {
				$iObjectId = $_GET['attachment_id'];
		} elseif (!isset($iObjectId)) {
				$iObjectId = 0;
		}
			
		$oPost = get_post($iObjectId);
		$sObjectType = $oPost->post_type;
		
		//get all groups		
		$aUUltraUserGroups = $this->get_all();
		
		$groups_list = array();		
		$groups_list = $this->get_all_post_groups($iObjectId);
		
		$html = '';
		
		$html .= '<div class="uultra-protect-group-options">	';
				
		if (count($aUUltraUserGroups) > 0) 
		{
			$html .= '<input type="hidden" name="uultra_update_groups" value="true" />	';	
		
			foreach ($aUUltraUserGroups as $oUUltraUserGroup) 
			{
				 $checked = '';
				 $sAttributes = '';	
				 
				if (in_array($oUUltraUserGroup->group_id, $groups_list))
				{
					$checked = 'checked="checked"';
				}
				 
				$html .= ' <li>';
				
				$html .= '<input type="checkbox" id="'.$oUUltraUserGroup->group_name.'-'.$oUUltraUserGroup->group_id.'" value="'.$oUUltraUserGroup->group_id.'" name="uultra_group_list[]" '.$checked.' /> ';
                 
       $html .= '  <label for="'.$oUUltraUserGroup->group_name.'-'.$oUUltraUserGroup->group_id.'" class="selectit" style="display:inline;" >
            '.$oUUltraUserGroup->group_name.$sAddition.'
        </label>';
				
				$html .= ' </li>';
			
			}
		
		} else {
			
				$html .= "<a href='admin.php?page=uultra-groups'>".__('Please create a user group first.','xoousers')."</a>";
		
		} //end if
		
		$html .= ' </div>';
		
		
		echo $html;	

    }
	
	public function get_all_user_groups ($user_id) 
	{
		global $wpdb;
		
		$groups_list = array();
		
		$sql = ' SELECT * FROM ' . $wpdb->prefix . 'usersultra_groups_users_groups_rel WHERE group_user_group_rel_user_id= "'.$user_id.'"  ' ;
		$res = $wpdb->get_results($sql);
		
		if ( !empty( $res ) )
		{
			foreach ( $res as $group )
			{
				$groups_list[] = $group->group_user_group_rel_group_id;
			
			}					
				
				
		}
		
		return $groups_list;	
	
	}
	
	public function get_all_post_groups ($post_id) 
	{
		global $wpdb;
		
		$groups_list = array();
		
		$sql = ' SELECT * FROM ' . $wpdb->prefix . 'usersultra_groups_post_rel WHERE group_post_rel_post_id= "'.$post_id.'"  ' ;
		$res = $wpdb->get_results($sql);
		
		if ( !empty( $res ) )
		{
			foreach ( $res as $group )
			{
				$groups_list[] = $group->group_post_rel_group_id;
			
			}					
				
				
		}
		
		return $groups_list;	
	
	}
	
	 public function getPostableTypes()
    {
        return $this->_aPostableTypes;
    }
	
	 public function addPostColumnsHeader($aDefaults)
    {
        $aDefaults['uultra_access'] = __('Users Ultra Access', 'xoousers');
        return $aDefaults;
    }
	
	
	
	public function edit_group_del ()
	{
		global $wpdb;
		
		$group_id = $_POST["group_id"];		
		$query = "DELETE FROM " . $wpdb->prefix ."usersultra_groups  WHERE  `group_id` = '$group_id'  ";			
			
		$wpdb->query( $query );	
		
		$html = __('Deleted', 'xoousers');
		
		echo $html;
		die();
		
	}
	public function edit_group_conf ()
	{
		global $wpdb;
		
		$group_id = $_POST["group_id"];
		$group_name= $_POST["group_name"];
		$group_desc= $_POST["group_desc"];		
		$group_privacy = $_POST['group_privacy'];	
				
		
		if(isset($_POST['group_name']) && $_POST['group_name']!="")
		{
		
			$query = "UPDATE " . $wpdb->prefix ."usersultra_groups SET `group_name` = '$group_name', `group_desc` = '$group_desc' , `group_privacy` = '$group_privacy'  WHERE  `group_id` = '$group_id'  ";			
			
			$wpdb->query( $query );
						
			echo '<div class="updated"><p><strong>'.__('The group has been updated.','xoousers').'</strong></p></div>';
		}else{
			
			echo '<div class="error"><p><strong>'.__('Please input a name.','xoousers').'</strong></p></div>';
			
			
		}
		
		//echo $html;
		//die();
		
	}
	
	
	
	public function edit_group ()
	{
		global $wpdb;
		
		$group_id = $_POST["group_id"];
		
		if($group_id!="")
		{
		
			$res = $wpdb->get_results( 'SELECT *  FROM ' . $wpdb->prefix . 'usersultra_groups WHERE `group_id` = ' . $group_id . '  ' );
			
			$html="";
			foreach ( $res as $photo )
			{
				
				$html .="<p>".__( 'Name:', 'xoousers' )."</p>";
				
				$html .="<p><input type='text' value='".$photo->group_name."' class='xoouserultra-input' id='uultra_group_name_edit_".$photo->photo_cat_id."'></p>";
				
				
				$html .="<p><input type='button' class='button-primary uultra-group-close' value='".__( 'Close', 'xoousers' )."' data-id= ".$photo->group_id."> <input type='button'  class='button-primary uultra-group-modify' data-id= ".$photo->group_id." value='".__( 'Save', 'xoousers' )."'> </p>";
				
								
			}		
			
					
		}
		
		echo $html;
		die();
		
	}
	
	public function get_all () 
	{
		global $wpdb;
		
		$sql = ' SELECT * FROM ' . $wpdb->prefix . 'usersultra_groups ORDER BY group_name ASC  ' ;
		$res = $wpdb->get_results($sql);
		
		return $res ;	
		
		
	
	}
	
	/* Setting for available user groups at registration */
    public function uultra_available_user_groups_registration()
	{
      		
      	 $user_groups = array();
		$groups = $this->get_all();

        foreach( $groups as $group ) 
		{
            $user_groups[$group->group_id] = $group->group_name;
        }

        return $user_groups;
    }
	
	public function get_one ($id) 
	{
		global $wpdb;
		
		$sql = ' SELECT * FROM ' . $wpdb->prefix . 'usersultra_groups WHERE group_id= "'.$id.'"  ' ;
		$res = $wpdb->get_results($sql);
		
		if ( !empty( $res ) )
		{
			foreach ( $res as $group )
			{
				return $group ;
			
			}			
				
		}
		
		
	
	}
	
	function admin_head(){

	}

	function add_styles(){
	
		wp_register_script( 'uultra_group_js', uultra_groups_url . 'admin/scripts/admin.js', array( 
			'jquery'
		) );
		wp_enqueue_script( 'uultra_group_js' );
	
		wp_register_style('uultra_group_css', uultra_groups_url . 'admin/css/admin.css');
		wp_enqueue_style('uultra_group_css');
		
	}
	
	function add_menu()
	{
		add_submenu_page( 'userultra', __('Groups','xoousers'), __('Groups','xoousers'), 'manage_options', 'uultra-groups', array(&$this, 'admin_page') );
		
		do_action('userultra_admin_menu_hook');
		
		
	}

	function admin_tabs( $current = null ) {
			$tabs = $this->tabs;
			$links = array();
			if ( isset ( $_GET['tab'] ) ) {
				$current = $_GET['tab'];
			} else {
				$current = $this->default_tab;
			}
			foreach( $tabs as $tab => $name ) :
				if ( $tab == $current ) :
					$links[] = "<a class='nav-tab nav-tab-active' href='?page=".$this->subslug."&tab=$tab'>$name</a>";
				else :
					$links[] = "<a class='nav-tab' href='?page=".$this->subslug."&tab=$tab'>$name</a>";
				endif;
			endforeach;
			foreach ( $links as $link )
				echo $link;
	}

	function get_tab_content() {
		$screen = get_current_screen();
		if( strstr($screen->id, $this->subslug ) ) {
			if ( isset ( $_GET['tab'] ) ) {
				$tab = $_GET['tab'];
			} else {
				$tab = $this->default_tab;
			}
			require_once uultra_groups_path.'admin/panels/'.$tab.'.php';
		}
	}
	
	public function save()
	{
		global $wpdb;
		
		if(isset($_POST['group_name']) && $_POST['group_name']!="")
		{
		
			$group_name = $_POST['group_name'];	
			$group_privacy = $_POST['group_privacy'];		
			
				
			$new_array = array(
							'group_id'     => null,
							'group_name'   => $group_name,
							'group_privacy'   => $group_privacy						
							
							
						);
						
				
			$wpdb->insert( $wpdb->prefix . 'usersultra_groups', $new_array, array( '%d', '%s' , '%s'));
			echo '<div class="updated"><p><strong>'.__('New group has been created.','xoousers').'</strong></p></div>';
		}else{
			
			echo '<div class="error"><p><strong>'.__('Please input a name.','xoousers').'</strong></p></div>';
			
			
		}
	
	
	}
	
	
	function admin_page() {
	
		
		if (isset($_POST['add-group']) && $_POST['add-group']=='add-group') 
		{
			$this->save();
		}
		
		if (isset($_POST['edit-group']) && $_POST['group_id']!='') 
		{
			$this->edit_group_conf();
		}

		
		
	?>
	
		<div class="wrap <?php echo $this->slug; ?>-admin">
        
           <h2>USERS ULTRA PRO - GROUPS</h2>
           
           <div id="icon-users" class="icon32"></div>
			
						
			<h2 class="nav-tab-wrapper"><?php $this->admin_tabs(); ?></h2>

			<div class="<?php echo $this->slug; ?>-admin-contain">
				
				<?php $this->get_tab_content(); ?>
				
				<div class="clear"></div>
				
			</div>
			
		</div>

	<?php }

}
$uultra_group = new UsersUltraGroup();