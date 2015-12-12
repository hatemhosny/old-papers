<?php
/**
 * Template Name: Article RSS Template
 */


$feed_count = 20; // The default number of posts to show in the feed
if (get_field('feed_count','option') != '')
{
    $feed_count = get_field('feed_count','option');
}

$short_abstarct = FALSE;
$post_type = 'article';


//get mode
$mode = 'normal';   // normal/preview/playlist/no_audio
if ($_GET['mode'] != '')
{
    $mode = $_GET['mode'];
}

$post_status = 'publish';
$has_audio = '=';
if ($mode == 'no_audio')
{
    $has_audio = '!';
    $post_status = 'any';
    if (get_field('disable_conversion','option') == TRUE)
    {
        $post_type = '';
    }
    elseif (get_field('articles_to_convert','option') != '')
    {
        $feed_count = get_field('articles_to_convert','option');
    }
}

//default filters
$args = array(
	        'post_type'			=> $post_type,
	        'posts_per_page' 	=> $feed_count,
	        'post_status' 	    => $post_status,
            'meta_query'        => array(
		                                array(
			                                'key'     => 'has_audio',
			                                'value'   => 1,
			                                'compare' => $has_audio . '=',
		                                ),
	                                ), 

);

//get tag
$tag = '';
if ($_GET['tag'] != '')
{
    $tag = $_GET['tag'];
}

//get journal
$journal = '';
if ($_GET['journal'] != '')
{
    $journal = $_GET['journal'];
}


$journal_list = '';
if ($journal != '') //get articles in a journal by ISSN
{
    $args['meta_key'] = 'journal_issn';
    $args['meta_value'] = $journal;

} elseif ($tag != '')   //get articles in tagged journals
{
    //get tagged journals
        $tag_args = array(
	                'post_type'			=> 'journal',
	                'tag'	            => $tag
                    );

    $journals_tagged = new WP_Query( $tag_args );
    // The Loop
    if ( $journals_tagged->have_posts() ) 
    {
        //build list of tagged journals
	    while ( $journals_tagged->have_posts() ) 
        {
		    $journals_tagged->the_post();
            if ($journal_list != '')
            {
		        $journal_list .= ',';
            }
		    $journal_list .= get_field('issn');
	    }
    } 
    wp_reset_postdata();

    //get articles in this list of journals
    $args['meta_key'] = 'journal_issn';
    $args['meta_value'] = $journal_list;
    $args['meta_compare'] = 'IN';


} 

//playlist mode
$playlist = '';
if ($mode == 'playlist')
{
    $short_abstarct = TRUE;
    $playlist = get_user_favorites();
    $args['post__in'] = $playlist;

}

//get the feeds
$posts = query_posts($args);

//send page header as atom feed
header('Content-Type: ' . feed_content_type('atom') . '; charset=' . get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';

/** This action is documented in wp-includes/feed-rss2.php */
do_action( 'rss_tag_pre', 'atom' );
?>
<feed
  xmlns="http://www.w3.org/2005/Atom"
  xmlns:thr="http://purl.org/syndication/thread/1.0"
  xml:lang="<?php bloginfo_rss( 'language' ); ?>"
  xml:base="<?php bloginfo_rss('url') ?>/wp-atom.php"
  <?php
  /**
   * Fires at end of the Atom feed root to add namespaces.
   *
   * @since 2.0.0
   */
  do_action( 'atom_ns' );
  ?>
 >
<title type="text"><?php bloginfo_rss('name'); wp_title_rss(); ?></title>
<subtitle type="text"><?php bloginfo_rss("description") ?></subtitle>
<updated><?php echo mysql2date('Y-m-d\TH:i:s\Z', get_lastpostmodified('GMT'), false); ?></updated>
<link rel="alternate" type="<?php bloginfo_rss('html_type'); ?>" href="<?php bloginfo_rss('url') ?>" />
<id><?php bloginfo('atom_url'); ?></id>
<link rel="self" type="application/atom+xml" href="<?php self_link(); ?>" />
<?php
/**
	* Fires just before the first Atom feed entry.
	*
	* @since 2.0.0
	*/
do_action( 'atom_head' );
   
while(have_posts()) : the_post(); 
 
?>
<entry>
<id>urn:pmid:<?php the_field('pmid'); ?></id>
<title type="<?php html_type_rss(); ?>"><![CDATA[<?php the_title_rss(); ?>]]></title>
<link rel="alternate" type="<?php bloginfo_rss('html_type'); ?>" href="<?php the_permalink_rss() ?>" />
<?php 

//Publication date
$pubdate = '';
if (strtotime(str_replace('/', '-', get_field('publication_date')))):
    
$pubdate = date_create_from_format('d/m/Y', get_field('publication_date', FALSE, FALSE));
$pubdate = date_format($pubdate, 'Y-m-d\TH:i:s\Z');
endif;

if ($pubdate != ''):
 ?>
<published><?php echo($pubdate); ?></published>
<?php endif; 
 
 ?>
<updated><?php echo(get_the_modified_date('Y-m-d\TH:i:s\Z'));  ?></updated>
<?php

//get lastname of first author
$author = '';
if( get_field('authors') )
{
	while( has_sub_field('authors') )
	{ 
        $author = get_sub_field('last_name'); 
        break;
	}
}
 ?>
<author>
<name><?php echo($author); ?></name>
<?php			/**
			 * Fires at the end of each Atom feed author entry.
			 *
			 * @since 3.2.0
			 */
			do_action( 'atom_author' );
		?>
</author>
<?php 
 
 
//Abstract
$abstract = '';
if( get_field('abstract') )
{
	while( has_sub_field('abstract') )
	{ 
        if ((get_sub_field('abstract_section') != '') and (strtolower(get_sub_field('abstract_section')) != 'unassigned') )
        {
        $abstract .= get_sub_field('abstract_section') . ': '; 
        }
        $abstract .= get_sub_field('abstract_text'); 

	}

    if ($short_abstarct == TRUE)
    {
        $abstract = shorten_string($abstract,50);
    }
}
 ?>
<content type="<?php html_type_rss(); ?>" xml:base="<?php the_permalink_rss() ?>"><![CDATA[<?php echo($abstract); ?>]]></content>
<summary type="<?php html_type_rss(); ?>"><![CDATA[<?php echo shorten_string($abstract, 50); ?>]]></summary>
<?php 

 
?>
<source>
<id>urn:issn:<?php the_field('journal_issn'); ?></id>
<title><?php the_field('journal'); ?></title>
</source>
<rights type="html"><![CDATA[<?php the_field('copyright'); ?>]]></rights>
<?php 

if (get_field('has_audio')==TRUE):
$audio_url = bloginfo_rss('url');
$audio_url .= '/articles/play/';
$audio_url .= get_field('pmid');
$audio_url .= '.mp3';
?>
<link rel="enclosure" type="audio/mpeg" title="MP3" href="<?php  echo($audio_url); ?>"/>
<?php 
endif; 
 
?>
</entry>
<?php
	/**
	 * Fires at the end of each Atom feed item.
	 *
	 * @since 2.0.0
	 */
	do_action( 'atom_entry' );

 endwhile; ?>
</feed>