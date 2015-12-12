
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
	
		<!--BEGIN .hentry-->
		<div id="post-<?php the_ID(); ?>" class="<?php semantic_entries(); ?>">
		
			<?php $options = get_option('evolve'); if (($evolve_header_meta == "") || ($evolve_header_meta == "single_archive"))
			{ ?>
			
				<h1 class="entry-title"><a href="<?php echo get_permalink($post->post_parent); ?>" rev="attachment" class="attach-font">
				<?php echo get_the_title($post->post_parent); ?></a> &raquo; <?php if ( get_the_title() ){ the_title();} ?></h1>
			
				<!--BEGIN .entry-meta .entry-header-->
				<div class="entry-meta entry-header">
					<a href="<?php the_permalink() ?>"><span class="published updated"><?php the_time(get_option('date_format')); ?></span></a>
					
					<?php if ( comments_open() ) : ?>
						<span class="comment-count"><?php comments_popup_link( __( 'Leave a Comment', 'evolve' ), __( '1 Comment', 'evolve' ), __( '% Comments', 'evolve' ) ); ?></span>
						<?php else : // comments are closed
					endif; ?>
	
					<span class="author vcard">
					
						<?php $evolve_author_avatar = evolve_get_option('evl_author_avatar','0');
						if ($evolve_author_avatar == "1") { echo get_avatar( get_the_author_meta('email'), '30' ); } ?>
						
						<?php _e( 'By', 'evolve' ); ?> <strong><?php printf( '<a class="url fn" href="' . get_author_posts_url( $authordata->ID, $authordata->user_nicename ) . '" title="' . sprintf( 'View all posts by %s', $authordata->display_name ) . '">' . get_the_author() . '</a>' ) ?></strong>
					
					</span>
					
					<?php edit_post_link( __( 'edit', 'evolve' ), '<span class="edit-post">', '</span>' ); ?>
				
				<!--END .entry-meta .entry-header-->
				</div>
			
			<?php } else { ?>
			
				<h1 class="entry-title"><a href="<?php echo get_permalink($post->post_parent); ?>" rev="attachment"><?php echo get_the_title($post->post_parent); ?></a> &raquo; <?php the_title(); ?></h1>
				
				<?php if ( current_user_can( 'edit_post', $post->ID ) ): ?>
					<?php edit_post_link( __( 'EDIT', 'evolve' ), '<span class="edit-post edit-attach">', '</span>' ); ?>
				<?php endif; ?>
			
			<?php } ?>
			
	
			<!--BEGIN .entry-content .article-->
			<div class="entry-content article">
					
				<?php if ( wp_attachment_is_image() ) :
					$attachments = array_values( get_children( array( 'post_parent' => $post->post_parent, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID' ) ) );
					foreach ( $attachments as $k => $attachment ) {
					if ( $attachment->ID == $post->ID )
					break;
					}
					$k++;
					// If there is more than 1 image attachment in a gallery
					if ( count( $attachments ) > 1 ) {
					if ( isset( $attachments[ $k ] ) )
					// get the URL of the next image attachment
					$next_attachment_url = get_attachment_link( $attachments[ $k ]->ID );
					else
					// or get the URL of the first image attachment
					$next_attachment_url = get_attachment_link( $attachments[ 0 ]->ID );
					} else {
					// or, if there's only 1 image attachment, get the URL of the image
					$next_attachment_url = wp_get_attachment_url();
					}
					?>
					<p class="attachment" align="center"><a href="<?php echo wp_get_attachment_url(); ?>" title="<?php echo esc_attr( get_the_title() ); ?>" class="single-gallery-image"><?php
					echo wp_get_attachment_image( $post->ID, $size='medium' ); // filterable image width with, essentially, no limit for image height.
					?></a></p>
					
					<div class="navigation-links single-page-navigation clearfix row">
						<div class="col-sm-6 col-md-6 nav-previous"><?php previous_image_link ( false, '<div class="btn btn-left icon-arrow-left icon-big">Previous Image</div>' ); ?></div>
						<div class="col-sm-6 col-md-6 nav-next"><?php next_image_link ( false, '<div class="btn btn-right icon-arrow-right icon-big">Next Image</div>' ); ?></div>
						
						<!--END .navigation-links-->
					</div>
				
				<?php else : ?>
					<a href="<?php echo wp_get_attachment_url(); ?>" title="<?php echo esc_attr( get_the_title() ); ?>" rel="attachment"><?php echo basename( get_permalink() ); ?></a>
				<?php endif; ?>
				
				<div class="entry-caption"><?php if ( !empty( $post->post_excerpt ) ) the_excerpt(); ?></div>

				<div class="clearfix"></div>

			</div><!--END .entry-content .article-->

		<!--END .hentry-->
		</div>
		
		<?php $options = get_option('evolve'); if (($evolve_share_this == "single_archive") || ($evolve_share_this == "all")) {
		evolve_sharethis(); } else { ?> <div class="margin-40"></div> <?php }?>
		
		
		<?php comments_template( '', true ); ?>
	
	<?php endwhile; else : ?>
	
		<!--BEGIN #post-0-->
		<div id="post-0" class="<?php semantic_entries(); ?>">
			<h1 class="entry-title"><?php _e( 'Not Found', 'evolve' ); ?></h1>
			
			<!--BEGIN .entry-content-->
			<div class="entry-content">
				<p><?php _e( 'Sorry, no attachments matched your criteria.', 'evolve' ); ?></p>
				<!--END .entry-content-->
			</div>
			<!--END #post-0-->
		</div>
	


	<?php endif; ?>
