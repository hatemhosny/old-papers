<h2 class="page-title archive-title"><span id="journal-title">Journals</span></h2>
<?php


//default filters
$args = array(
	        'post_type'			=> 'journal',
	        'post_status' 	    => 'publish'
);


//get the journals
$wp_query = new WP_Query( $args );

if ( $wp_query->have_posts() ): while ( $wp_query->have_posts() ) : $wp_query->the_post() ; 

?>


<!--BEGIN .hentry-->
	<div id="post-<?php the_ID(); ?>" class="<?php semantic_entries(); ?> <?php evolve_post_class_2(); ?>">
	
		<h5 class="entry-list-title">
			<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>">
				<?php
				if ( get_the_title() ){ $title = the_title('', '', false);
					echo ($title); 
				} 
				?>
			</a>
		</h5>
		
		<!--BEGIN .entry-meta .entry-header-->
		<div class="entry-meta entry-header">


			            <!--END .entry-meta .entry-header-->
		</div>
	
		<?php  if ( current_user_can( 'edit_post', $post->ID ) ): ?>
			<?php edit_post_link( __( 'EDIT', 'evolve' ), '<span class="edit-post edit-attach">', '</span>' ); ?>
		<?php  endif; ?>

	
	<!--BEGIN .entry-content .article-->
	<div class="entry-content article">
	<?php the_tags() ?>
    <?php include 'article-buttons.php'; ?>	
		<div class="entry-meta entry-footer article-list-footer">
	

		</div>
		
		<!--END .entry-content .article-->
		<div class="clearfix"></div>
	</div>
	
	<!--END .hentry-->
</div>
<?php
 endwhile; 
 endif;
 ?>