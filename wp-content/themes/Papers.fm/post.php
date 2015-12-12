
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
			
				<h1 class="entry-title"><?php if ( get_the_title() ){ the_title(); } ?></h1>

				<!--BEGIN .entry-meta .entry-header-->
				<div class="entry-meta entry-header">
					<a href="<?php the_permalink() ?>"><span class="published updated"><?php the_time(get_option('date_format')); ?></span></a>
				
					<?php if ( comments_open() ) : ?>
						<span class="comment-count"><?php comments_popup_link( __( 'Leave a Comment', 'evolve' ), __( '1 Comment', 'evolve' ), __( '% Comments', 'evolve' ) ); ?></span>
					<?php else : // comments are closed
					endif; ?>
				
				
					<span class="author vcard">
				
					<?php $evolve_author_avatar = evolve_get_option('evl_author_avatar','0');
					if ($evolve_author_avatar == "1") { echo get_avatar( get_the_author_meta('email'), '30' );
					} ?>
	
					<?php _e( 'Written by', 'evolve' ); ?> <strong><?php printf( '<a class="url fn" href="' . get_author_posts_url( $authordata->ID, $authordata->user_nicename ) . '" title="' . sprintf( 'View all posts by %s', $authordata->display_name ) . '">' . get_the_author() . '</a>' ) ?></strong></span>
	
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
			
			<!--BEGIN .entry-content .article-->
			<div class="entry-content article">
				<?php the_content( __('READ MORE &raquo;', 'evolve' ) ); ?>
				<?php wp_link_pages( array( 'before' => '<div id="page-links"><p>' . __( '<strong>Pages:</strong>', 'evolve' ), 'after' => '</p></div>' ) ); ?>					
				<div class="clearfix"></div>
			</div><!--END .entry-content .article-->	
			
			
			<!--BEGIN .entry-meta .entry-footer-->
			<div class="entry-meta entry-footer row">
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