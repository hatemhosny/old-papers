<?php
class XooGroup
{

	
	function __construct() 
	{
		
		$this->ini_module();		
		
	}
	
	public function ini_module()
	{
		global $wpdb;

		// Create table
		$query = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'usersultra_groups (
				`group_id` bigint(20) NOT NULL auto_increment,
				`group_creator_id` int(11) NOT NULL ,
				`group_parent_id` int(11) NOT NULL DEFAULT "-1",
				`group_admin_id` int(11) NOT NULL ,
				`group_privacy` int(1) NOT NULL DEFAULT "0" ,
				`group_name` varchar(60) NOT NULL,					
				`group_desc` text NOT NULL,
				`group_creation_date` datetime NOT NULL,			
				PRIMARY KEY (`group_id`)
			) COLLATE utf8_general_ci;';
	
		$wpdb->query( $query );
		
		// Create table for posts and groups
		$query = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'usersultra_groups_post_rel (
				`group_post_rel_id` bigint(20) NOT NULL auto_increment,
				`group_post_rel_post_id` int(11) NOT NULL ,
				`group_post_rel_group_id` int(11) NOT NULL ,							
				PRIMARY KEY (`group_post_rel_id`)
			) COLLATE utf8_general_ci;';
	
		$wpdb->query( $query );
		
		// Create table for users and groups
		$query = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'usersultra_groups_users_groups_rel (
				`group_user_group_rel_id` bigint(20) NOT NULL auto_increment,
				`group_user_group_rel_user_id` int(11) NOT NULL ,
				`group_user_group_rel_group_id` int(11) NOT NULL ,							
				PRIMARY KEY (`group_user_group_rel_id`)
			) COLLATE utf8_general_ci;';
	
		$wpdb->query( $query );
		
		
		//udpate table
		$this->update_tables();

	}
	
	function update_tables()
	{
		global $wpdb;
		
				
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_groups where field="group_parent_id" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	//photo_desc
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_groups add column group_parent_id int (11)  NOT NULL DEFAULT "-1"; ';
			$wpdb->query($sql);
		}
		
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_groups where field="group_privacy" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	//photo_desc
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_groups add column group_privacy int (1)  NOT NULL DEFAULT "1"; ';
			$wpdb->query($sql);
		}
		
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_groups where field="group_creation_date" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	//photo_desc
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_groups add column group_creation_date datetime  NOT NULL ; ';
			$wpdb->query($sql);
		}
		
		
		
		
		
		
	}
	

	

}
$key = "group";
$this->{$key} = new XooGroup();