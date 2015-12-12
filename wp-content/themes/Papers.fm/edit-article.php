<?php
$edit_key = get_field('audio_key', 'option');
$pmid = $_POST['pmid'];
$voice = $_POST['voice'];
$has_audio = 1;
$audio_date = date('d/m/Y');
$audio_duration = $_POST['audio_duration'];
$audio_file_size = $_POST['audio_file_size'];

$article_id = '';

if  ($_POST['key'] != $edit_key)
{
    echo ('Access Denied');
}
else
{
    
    $args = array(
	    'post_type'   => 'article',
        'meta_key'    => 'pmid',
        'meta_value'  => $pmid,
        );

    //get the article to edit
    $articles = get_posts( $args );

    foreach ( $articles as $post ) : setup_postdata( $post ); 
        $article_id = get_the_ID();
    endforeach; 

    wp_reset_postdata();

    //update the fields
        (update_post_meta($article_id, 'has_audio', $has_audio)); 
        (update_post_meta($article_id, 'audio_voice', $voice));
        (update_post_meta($article_id, 'audio_date', $audio_date));
        (update_post_meta($article_id, 'audio_duration', $audio_duration));
        (update_post_meta($article_id, 'audio_file_size', $audio_file_size));
}
?>