
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
	

		<?php $options = get_option('evolve'); if (($evolve_post_links == "before") || ($evolve_post_links == "both")) { ?>

			<span class="nav-top">
				<?php get_template_part( 'navigation', 'index' ); ?>
			</span>
		
		<?php } ?>
		

		<!--BEGIN .hentry-->
		<div id="post-<?php the_ID(); ?>" class="<?php semantic_entries(); ?> col-md-12">

			<?php $options = get_option('evolve'); if (($evolve_header_meta == "") || ($evolve_header_meta == "single") || ($evolve_header_meta == "single_archive"))
			{ ?> 
			
				<h1 class="entry-title article-title"><?php if ( get_the_title() ){ the_title(); } ?></h1>
<!--BEGIN .entry-meta .entry-header--><span class="authors"><?php if( have_rows('authors') ): 
                        
    $first_author = TRUE; 
                         
    while( have_rows('authors') ): the_row(); 
        if ($first_author == FALSE) echo(", "); 
?>
<a href=""><?php the_sub_field('first_name'); ?> <?php the_sub_field('last_name'); ?></a>                           
<?php                              
        $first_author = FALSE; 
    endwhile;                                endif; ?></span><div class="publish-meta"><a href="<?php bloginfo('url') ?>/journal/<?php the_field('journal_issn'); ?>"><?php the_field('journal'); ?></a>  

<?php 
 if (strtotime(str_replace('/', '-', get_field('publication_date')))):
    
        $pubdate = date_create_from_format('d/m/Y', get_field('publication_date', FALSE, FALSE));
        $pubdate = date_format($pubdate, 'M d, Y');
?>
<span> - <?php echo($pubdate); ?></span>
<?php endif; ?>


				
					<?php if ( comments_open() ) : ?>
						<span class="comment-count"><?php comments_popup_link( __( 'Leave a Comment', 'evolve' ), __( '1 Comment', 'evolve' ), __( '% Comments', 'evolve' ) ); ?></span>
					<?php else : // comments are closed
					endif; ?>
				
				
					
					
					<?php edit_post_link( __( 'edit', 'evolve' ), '<span class="edit-post">', '</span>' ); ?>
				<!--END .entry-meta .entry-header-->
				</div> 
			
			<?php } else { ?>
			
				<h1 class="entry-title"><?php the_title(); ?></h1>
			
				<?php if ( current_user_can( 'edit_post', $post->ID ) ): ?>			
					<?php edit_post_link( __( 'EDIT', 'evolve' ), '<span class="edit-post edit-attach">', '</span>' ); ?>
				<?php endif; ?>
			
			<?php } ?>
			

			<?php
			if($evolve_blog_featured_image == "1" && has_post_thumbnail()) {
				echo '<span class="thumbnail-post-single">';
				the_post_thumbnail('post-thumbnail');
				echo '</span>';
			}
			?>

<div class="article-separator"></div>

    <?php include 'article-buttons.php'; ?>	


			<!--BEGIN .entry-content .article-->
			<div class="entry-content article">
                 <?php if( have_rows('abstract') ): ?>
                        
                    <?php  while( have_rows('abstract') ): the_row(); ?>
                        <div class="abstractSection"><strong><?php the_sub_field('abstract_section'); ?></strong></div>
                        <div class="abstractText"><?php the_sub_field('abstract_text'); ?></div>
                    <?php endwhile; ?>                                            <?php endif; ?>
                 <div class="copyright"><?php the_sub_field('copyright'); ?></div>

                <div class="entry-meta entry-header">PMID: <a href="<?php the_permalink() ?>"><?php the_field('pmid'); ?></a>                <?php                    $doi = '';                    if (get_field('doi')!='')                    {
                        $doi = get_field('doi');
                    }                    elseif (get_field('doi_-_alt')!='')                    {
                        $doi = get_field('doi_-_alt');
                    }                    if ($doi!=''):                    ?>                <span class="full-Text-link"> - <a href="http://doi.org/<?php echo($doi); ?>">Full text</a></a></span>		


    <?php include 'article-buttons.php'; ?>	


<?php endif ?>                </div>

<div class="article-separator"></div>

				<?php wp_link_pages( array( 'before' => '<div id="page-links"><p>' . __( '<strong>Pages:</strong>', 'evolve' ), 'after' => '</p></div>' ) ); ?>					
				<div class="clearfix"></div>
			</div><!--END .entry-content .article-->	
			
			
			<!--BEGIN .entry-meta .entry-footer-->
			<div class="entry-meta row">
				<div class="col-md-6">				
					<?php if ( evolve_get_terms( 'cats' ) ) { ?>
						<div class="entry-categories"> <?php echo evolve_get_terms( 'cats' ); ?></div>
					<?php } ?>
					<?php if ( evolve_get_terms( 'tags' ) ) { ?>
						<div class="entry-tags"> <?php echo evolve_get_terms( 'tags' ); ?></div>
					<?php } ?>							
				</div>
			
				<div class="col-md-6">
					<?php $options = get_option('evolve'); 
					if (($evolve_share_this == "") || ($evolve_share_this == "single") || ($evolve_share_this == "single_archive") || ($evolve_share_this == "all")) {
					evolve_sharethis(); } else { ?> <div class="margin-40"></div> <?php }?>
				</div>
			
			</div><!--END .entry-meta .entry-footer-->

			<!-- Auto Discovery Trackbacks
			<?php trackback_rdf(); ?>
			-->
		<!--END .hentry-->
		</div>
		
		<?php $options = get_option('evolve'); if (($evolve_similar_posts == "") || ($evolve_similar_posts == "disable")) {} else {
		evolve_similar_posts(); } ?>
			
		<?php $options = get_option('evolve'); if (($evolve_post_links == "") || ($evolve_post_links == "after") || ($evolve_post_links == "both")) { ?>		
			<?php get_template_part( 'navigation', 'index' ); ?>
		<?php } ?>
		
		<?php comments_template( '', true ); ?>
	
	<?php endwhile; else : ?>
	
		<!--BEGIN #post-0-->
		<div id="post-0" class="<?php semantic_entries(); ?>">
			<h1 class="entry-title"><?php _e( 'Not Found', 'evolve' ); ?></h1>
			<!--BEGIN .entry-content-->
			<div class="entry-content">
				<p><?php _e( 'Sorry, but you are looking for something that isn\'t here.', 'evolve' ); ?></p>
				<?php get_search_form(); ?>
			<!--END .entry-content-->
			</div>
			<!--END #post-0-->
		</div>
	<?php endif; ?>