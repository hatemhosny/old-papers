<?php
class XooUserApi
{
	var $current_user = array();

	function __construct() 
	{			

	}
	
	/*Loads User Information*/
	function load_user($value, $method) 
	{
		global $xoouserultra;		
		$user = $this->get_user($value, $method);		
		return $user;
		
	}
	
	
	/******************************************
	Post wall message
	******************************************/
	function wall_post_message($receiver_id, $sender_id, $message)
	{
		global $xoouserultra;		
		$url = $xoouserultra->wall->api_wall_post_message($receiver_id, $sender_id, $message);		
		
	}
	
	/******************************************
	Get usermeta
	******************************************/
	function get($field, $user_id)
	{
		return get_user_meta($user_id, $field, true);
	}
	
	/******************************************
	Get user url
	******************************************/
	function get_user_url($user_id)
	{
		global $xoouserultra;		
		$url = $xoouserultra->userpanel->get_user_profile_permalink($user_id);		
		return $url;
		
	}
	
	/******************************************
	Get user meta
	******************************************/
	function get_user_field($user_id, $field)
	{		
		return $this->get($field, $user_id);			
	}
	
	
	/******************************************
	Get user avatar
	******************************************/
	function get_user_avatar($user_id, $args)
	{
		global $xoouserultra;
		
		extract($args);
		
		$avatar = $xoouserultra->userpanel->get_user_pic($user_id, $size, $pic_type, $pic_boder_type, $size_type);		
		return $avatar;		
		
	}
	
	/******************************************
	Get user badges
	******************************************/
	function get_user_badges($user_id)
	{
		global $xoouserultra;		
	
		$badges = $xoouserultra->badge->uultra_show_badges($user_id);		
		return $badges;		
		
	}
	
	/******************************************
	Get user rating
	******************************************/
	function get_user_rating($user_id)
	{
		global $xoouserultra;		
	
		$badges = $xoouserultra->rating->get_rating($user_id, 'user_id');	
		
		$html = '<div class="ratebox">';
		$html .=$badges ;		
		$html .= '</div>';
		
			
		return $html;		
		
	}
	
	/******************************************
	Get user
	******************************************/
	function get_user($value, $method)
	{
		if($method=='id')		
		{
			$user = get_user_by('id',$value);
			
		}elseif($method=='login'){
			
			$user = get_user_by('login',$value);
		
		}elseif($method=='email'){
			
			$user = get_user_by('email',$value);		
		
		}
		
		return $user;
		
	}
	
	/******************************************
	Set Custom Meta Info
	******************************************/
	function set_custom_meta_info($user_id, $meta, $value)
	{
		update_user_meta ($user_id, $meta, $value);		
		return $user_id;
		
	}
	
	/* Give medallions to members */
	function set_badge_to_user($user_id, $badge_url, $badge_title) 
	{
		
			$badges = get_user_meta($user_id, '_uultra_badges', true);
			
			// find if that medallion exists
			if (is_array($badges)){
				foreach($badges as $k => $badge){
					if ( $badge['badge_url'] == $badge_url ) {
						unset($badges[$k]);
					}
					if ( $badge['badge_title'] == $badge_title ) {
						unset($badges[$k]);
					}
				}
				update_user_meta($user_id, '_uultra_badges', true);
			}
			
			// add new medallion to member
			$badges[] = array(
				'badge_url' => $badge_url,
				'badge_title' => $badge_title
			);
			update_user_meta($user_id, '_uultra_badges', $badges);
		
	}
	

}
$key = "api";
$this->{$key} = new XooUserApi();