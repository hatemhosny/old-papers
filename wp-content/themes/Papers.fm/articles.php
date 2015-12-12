<?php


//default filters
$args = array(
	        'post_type'			=> 'article',
	        'post_status' 	    => 'publish',
            //'meta_key'			=> 'pubdate',
	        //'orderby'			=> 'meta_value_num',
	        //'order'				=> 'DESC',
            'paged'             => get_query_var('paged'), 
            'meta_query'        => array(
		                                array(
			                                'key'     => 'has_audio',
			                                'value'   => 1,
	  	                                     ),
	                                     ), 

         );

//get tag
$tag = '';
if (single_tag_title('',FALSE) != '')
{
    $tag = single_tag_title('',FALSE);
}

//get journal
$journal_issn = '';
if (get_field('issn') != '')
{
    $journal_issn = get_field('issn');
}


$journal_list = '';
if ($journal_issn != '') //get articles in a journal by ISSN
{
    $args['meta_key'] = 'journal_issn';
    $args['meta_value'] = $journal_issn;

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

if ($_GET['s'] != '' )
{
    $mode = 'search';
    $search_term = $_GET['s'];
    $args['s'] = $search_term;
}


$panel_expanded = TRUE;
$panel_expanded_class = 'in';

if ($mode == 'playlist' || $mode == 'search')
{
    $panel_expanded = FALSE;
    $panel_expanded_class = '';
}


//get the feeds
$wp_query = new WP_Query( $args );

// The Loop 
if ( $wp_query->have_posts() ): ?> 
 
<div class="row">
    <a href="#" class="btn btn-default collapse-toggle col-md-2 col-xs-3 pull-right">
        <?php if ($panel_expanded == TRUE) {?>
            Collapse All
        <?php }
              else { ?>
            Expand All
        <?php } ?>
    </a>
</div>
<div class="panel-group" id="accordion">
 
<?php while ( $wp_query->have_posts() ) : $wp_query->the_post() ; 

include 'article-item.php';

 endwhile; ?>
</div>
<div class="row">
    <a href="#" class="btn btn-default collapse-toggle col-md-2 col-xs-3 pull-right">
        <?php if ($panel_expanded == TRUE) {?>
            Collapse All
        <?php }
              else { ?>
            Expand All
        <?php } ?>
    </a>
</div>

<script type="text/javascript">
    <?php if ($panel_expanded == TRUE) {?>
    var toggleStatus = 'expanded';
    <?php }
            else { ?>
    var toggleStatus = 'collapsed';
    <?php } ?>
</script>

<script type="text/javascript">

jQuery('.collapse-toggle').click(function () {

    if (toggleStatus != 'expanded') {
        jQuery('.panel-collapse:not(".in")').collapse('show');
        jQuery('.collapse-toggle').html('Collapse All');
        toggleStatus = 'expanded';
        return false;
    }
    else {
        jQuery('.panel-collapse.in').collapse('hide');
        jQuery('.collapse-toggle').html('Expand All');
        toggleStatus = 'collapsed';
        return false;
    }
});


jQuery(document).ready(function(){    
    jQuery('.collapse').on('shown.bs.collapse', function () {
       jQuery(this).parent().find(".fa-plus-circle").removeClass("fa-plus-circle").addClass("fa-minus-circle");
    });
    
    jQuery('.collapse').on('hidden.bs.collapse', function () {
       jQuery(this).parent().find(".fa-minus-circle").removeClass("fa-minus-circle").addClass("fa-plus-circle");
    });
});
</script>


<?php 
 
//wp_pagenavi( array( 'query' => $query ) );

get_template_part( 'navigation', 'index' ); 

endif;

 wp_reset_postdata();

 ?>
