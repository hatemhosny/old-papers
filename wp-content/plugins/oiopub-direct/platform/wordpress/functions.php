<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

/* ACTIVATION WRAPPERS */

//activate plugin
function oiopub_script_activate() {
	global $oiopub_set, $oiopub_db;
	global $wpdb;
	if(oiopub_is_admin() && $_GET['action'] == "activate") {
		if(empty($oiopub_set->version)) {
			$welcome = '&welcome=1';
			oiopub_install_wrapper();
			if(isset($wpdb)) {
				$oiopub_db->query("ALTER TABLE `" . $wpdb->posts . "` ADD FULLTEXT `post_related` (`post_name`,`post_content`)");
			}
		} else {
			$welcome = '';
		}
		oiopub_update_config('admin_redirect', oiopub_admin_home_url() . $welcome);
	}
}

//deactivate plugin
function oiopub_script_deactivate() {
	global $oiopub_set;
	$current = get_option('active_plugins');
	$plugin = $oiopub_set->folder_name."/wp.php";
	foreach($current as $key => $value) {
		if($value == $plugin) {
			unset($current[$key]);
		}
	}
	sort($current);
	update_option('active_plugins', $current);
	oiopub_flush_cache();
	@header("Location: plugins.php?deactivate=true");
	exit();
}

/* AUTH FUNCTIONS */

//auth check
function oiopub_auth_check() {
	global $oiopub_admin;
	$level = isset($oiopub_admin) ? $oiopub_admin->get_role() : "manage_options";
	if(function_exists('current_user_can')) {
		return current_user_can($level);
	}
	return false;
}

//is admin
function oiopub_is_admin() {
	if(defined('OIOPUB_ADMIN')) {
		return true;
	}
	if(function_exists('is_admin')) {
		return is_admin();
	}
	return false;
}

/* POST FUNCTIONS */

//post edit link
function oiopub_post_admin_edit($id=0) {
	global $oiopub_set;
	return $oiopub_set->admin_url . "/post.php?action=edit&post=" . $id;
}

//get current category
function oiopub_get_category() {
	global $oiopub_set, $posts;
	//static result
	static $result = array();
	//global cats?
	if(isset($oiopub_set->cats)) {
		return $oiopub_set->cats;
	}
	//check cache
	if(!empty($result)) {
		return $result;
	}
	//set type
	$type = 'children';
	//category page?
	if($cat_id = get_query_var('cat')) {
		//log ID
		$result[] = $cat_id;
	} else {
		//set posts array
		$posts = $posts ? $posts : array();
		//loop through posts
		foreach($posts as $p) {
			//categories found?
			if(!$cats = wp_get_post_categories($p->ID)) {
				continue;
			}
			//search categories
			foreach($cats as $cat) {
				if(!in_array($cat, $result)) {
					$result[] = $cat;
				}
			}
		}
	}
	//check for children?
	if($type == 'children') {
		foreach($result as $parent) {
			if($children = oiopub_category_tree($parent, false)) {
				foreach($children as $key => $val) {
					if($key > 0 && !in_array($key, $result)) {
						$result[] = $key;
					}
				}
			}
		}
	}
	//check for parents?
	if($type == 'parents') {
		foreach($result as $child) {
			if($parents = get_category_parents($child, false, ',')) {
				if($parents = explode(",", $parents)) {
					foreach($parents as $p) {
						$term = get_term_by('name', trim($p), 'category');
						if($term->term_id > 0 && !in_array($term->term_id, $result)) {
							$result[] = $term->term_id;
						}
					}
				}
			}
		}
	}
	//make all IDs unique
	$result = array_unique($result);
	//return result
	return $result;
}

//category tree
function oiopub_category_tree($id=0, $level=0) {
	global $oiopub_set;
	//set vars
	$result = array();
	$args = array( 'orderby' => "name", 'hide_empty' => false, 'parent' => $id );
	//get categories
	if(!function_exists('get_categories') || !$cats = get_categories($args)) {
		return $result;
	}
	//loop through cats
	foreach($cats as $c) {
		//skip children?
		if(!$id && $c->parent > 0) {
			continue;
		}
		//blocked category?
		if(isset($oiopub_set->blocked_cats) && in_array($c->cat_ID, $oiopub_set->blocked_cats)) {
			continue;
		}
		//add to result?
		if(!isset($result[$c->cat_ID])) {
			$result[$c->cat_ID] = str_repeat('- - ', $level) . $c->cat_name;
		}
		//check for children
		$result += oiopub_category_tree($c->cat_ID, $level+1);
	}
	//return
	return $result;
}

//get category list
function oiopub_category_list($id=0) {
	$name = "-- " . (oiopub_is_admin() ?  __oio("All Categories") : __oio("Select a category")) . " --";
	$list = array( 0 => $name );
	$list += oiopub_category_tree($id);
	return $list;
}

//grab category data
function oiopub_post_categories() {
	return oiopub_category_tree();
}

//get post content
function oiopub_post_content($id) {
	global $oiopub_db, $wpdb;
	return $oiopub_db->GetOne("SELECT post_content FROM $wpdb->posts WHERE ID='$id'");
}

//insert post
function oiopub_insert_post($data=array()) {
	global $oiopub_set;
	$post = array();
	$post['post_status'] = 'draft';
	$post['post_category'] = array( empty($data['category']) ? intval($_POST['oio_category']) : intval($data['category']) );
	$post['post_title'] = empty($data['title']) ? $_POST['oio_title'] : $data['title'];
	$post['post_content'] = empty($data['content']) ? $_POST['oio_content'] : $data['content'];
	if($oiopub_set->posts['tags'] == 1) {
		$tags = empty($data['tags']) ? $_POST['oio_tags'] : $data['tags'];
		if(!empty($tags)) {
			$post['post_content'] = $post['post_content'] . "\n\n[tags]" . $tags . "[/tags]";
		}
	}
	if($oiopub_set->general_set['disclosure'] == 1) {
		$post['post_content'] = "<p>" . $oiopub_set->general_set['disclosure'] . "</p>" . $post['post_content'];
	}
	return wp_insert_post($post);
}

//get post ids
function oiopub_post_ids() {
	global $posts;
	$ids = array();
	$oioposts = $posts;
	if(!empty($oioposts)) {
		foreach($oioposts as $p) {
			$ids[] = intval($p->ID);
		}
	}
	return $ids;
}

//keyword data
function oiopub_keyword_data($keywords, $limit=10) {
	global $oiopub_set, $oiopub_db, $wpdb;
	if(strlen($keywords) > 0) {
		//build SQL query
		$sql = "SELECT ID, post_title, MATCH (post_name,post_content) AGAINST ('$keywords') AS score FROM $wpdb->posts WHERE MATCH (post_name,post_content) AGAINST ('$keywords') AND post_content LIKE '%$keywords%' AND post_status='publish' AND post_type='post' AND post_password='' ORDER BY score DESC LIMIT $limit";
		//run secondary query?
		if(!$results = $oiopub_db->GetAll($sql)) {
			$sql = "SELECT ID, post_title FROM $wpdb->posts WHERE (post_content LIKE '% $keywords %' OR post_content LIKE '% $keywords.%' OR post_content LIKE '% $keywords,%') AND post_status='publish' AND post_type='post' AND post_password='' LIMIT $limit";
			$results = $oiopub_db->GetAll($sql);
		}
	}
	$output .= "<table align='center' width='500' class='start' cellpadding='4' cellspacing='4'>\n";
	$output .= "<tr><td colspan='2'><b>&raquo; " . __oio("Search Results") . "</b></td></tr>\n";
	$output .= "<tr><td colspan='2' height='10'></td></tr>\n";
	if(oiopub_count($results) > 0) {
		$background = "#E0EEEE";
		$output .= "<tr><td><b>" . __oio("Post ID") . "</b></td><td><b>" . __oio("Post Link") . "</b></td></tr>\n";
		foreach($results as $res) {
			$output .= "<tr style='background:$background;'><td>" . $res->ID . "</td><td><a href=\"" . $oiopub_set->site_url . "/?p=" . $res->ID . "\" title=\"" . $res->post_title . "\" target=\"_blank\">" . oiopub_strlimit($res->post_title, 30) . "</a></td></tr>\n";
			if($background == "#FFFFFF") {
				$background = "#E0EEEE";
			} else {
				$background = "#FFFFFF";
			}		
		}
	} else {
		$output .= "<tr><td colspan='2'><b>" . __oio("No matches found") . "</b></td></tr>\n";
	}
	$output .= "</table>\n";
	return $output;
}

//preview data
function oiopub_preview_data($id, $published=0) {
	global $oiopub_set, $oiopub_db, $wpdb;
	//already published?
	if($published == 1 && function_exists('get_permalink')) {
		header("Location: " . get_permalink($id));
		exit();
	}
	//grab post data
	$data = $oiopub_db->GetRow("SELECT post_title,post_content FROM $wpdb->posts WHERE ID='" . intval($id) . "'");
	//start output
	$output  = "<html>\n";
	$output .= "<body style='margin:0; padding:10px;'>\n";
	//preview available?
	if(!empty($data)) {
		$output .= "<b>" . __oio("Below is a preview of the post content") . "</b>\n";
		$output .= "<br />\n";
		$output .= "<i>" . __oio("Please note that the post may not yet be complete") . "</i>\n";
		$output .= "<br /><br /><br />\n";
		$output .= "<div style='text-align:left;'>\n";
		$output .= "<font size='4'><b>" . $data->post_title . "</b></font>\n";
		$output .= "<br /><br />\n";
		$output .= $data->post_content . "\n";
		$output .= "</div>\n";
	} else {
		$output = "No valid preview data found!\n";
	}
	//end output
	$output .= "</body>\n";
	$output .= "</html>\n";
	//return
	return $output;
}

//wysiwyg editor
function oiopub_wysiwyg($content, $id, $settings=array()) {
	global $oiopub_set;
	//editor name
	$name = isset($settings['textarea_name']) ? $settings['textarea_name'] : $id;
	//load tinymce
	oiopub_textarea_editor();
	//display textarea
	echo '<textarea name="' . $name . '" id="' . $id . '" tabindex="8" cols="60" rows="9">' . $content . '</textarea>' . "\n";
}

//textarea editor (deprecated)
function oiopub_textarea_editor($base_url='', $id='wysiwyg') {
	global $oiopub_set;
	//send output
	echo '<script type="text/javascript" src="//tinymce.cachefly.net/4.0/tinymce.min.js"></script>' . "\n";
    echo '<script type="text/javascript">' . "\n";
	echo 'tinymce.init({' . "\n";
	echo '	selector: "#' . $id . '",' . "\n";
	echo '	plugins: "link, image, media, code, spellchecker",' . "\n";
	echo '	toolbar1: "bold italic underline | alignleft aligncenter alignright | numlist bullist | link image media | code",' . "\n";
	echo '	menubar: false,' . "\n";
	//echo '	statusbar: false,' . "\n";
	//echo '	external_plugins: { "tinyupload": "' . $oiopub_set->plugin_url . '/libs/tinyupload/tinyupload.js" },' . "\n";
	//echo '	file_browser_callback: tinyupload,' . "\n";
	echo '	relative_urls: false' . "\n";
	echo '});' . "\n";
    echo '</script>' . "\n";
}

/* MISC FUNCTIONS */

//admin home url
function oiopub_admin_home_url() {
	global $oiopub_set;
	$page = $oiopub_set->folder_name . "/wp.php";
	return $oiopub_set->admin_url . "/admin.php?page=" . $page;
}

//is feed
function oiopub_is_feed() {
	return is_feed();
}

//is post page
function oiopub_is_single() {
	return is_single();
}

//get permalink
function oiopub_permalink($id) {
	return get_permalink($id);
}

//get sidebar
function oiopub_theme_sidebar() {
	global $oiopub_set;
	if($oiopub_set->template == "wordpress") {
		return get_sidebar();
	}
}

?>