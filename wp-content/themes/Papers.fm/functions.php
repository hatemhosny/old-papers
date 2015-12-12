<?php
    

/**
 * Enqueues child theme stylesheet, loading first the parent theme stylesheet.
 */
function themify_custom_enqueue_child_theme_styles() {
	wp_enqueue_style( 'parent-theme-css', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'themify_custom_enqueue_child_theme_styles' );


//hide admin bar from frontend
//add_filter('show_admin_bar', '__return_false');

/*
* Add Options pages in admin menu 
*/


if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page(array(
		'page_title' 	=> 'Site Settings',
		'menu_title'	=> 'Site Settings',
		'menu_slug' 	=> 'site-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
	
	acf_add_options_sub_page(array(
		'page_title' 	=> 'Import Settings',
		'menu_title'	=> 'Import Settings',
		'parent_slug'	=> 'site-settings',
	));
	
	
}



/*
* Add custom rss for articles 
*/

add_action('init', 'articlesFeed');
function articlesFeed(){
        add_feed('articles/feed', 'articlesFeedFunc');
}

function articlesFeedFunc(){
        get_template_part('feed', 'articles');
}


//Get short abstract
function shorten_string($string, $wordsreturned)
/*  Returns the first $wordsreturned out of $string.  If string
    contains more words than $wordsreturned, the entire string
    is returned.
    */
{
    $retval = $string;  //    Just in case of a problem
    $array = explode(" ", $string);
    if (count($array)<=$wordsreturned)
    /*  Already short enough, return the whole thing
        */
        {
        $retval = $string;
        }
    else
    /*  Need to chop of some words
        */
        {
        array_splice($array, $wordsreturned);
        $retval = implode(" ", $array)." ...";
        }
    return $retval;
}

function journal_import_rewrite() {
  add_rewrite_rule('^journal_import', 'index.php?post_type=journal_import', 'top');
}
add_action('init', 'journal_import_rewrite');

