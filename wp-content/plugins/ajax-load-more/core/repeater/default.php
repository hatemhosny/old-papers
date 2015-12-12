<!--BEGIN .hentry-->
	<div id="post-<?php the_ID(); ?>" class="article-item panel panel-default">
      <div class="panel-heading" onclick="jQuery('#collapse-<?php the_ID(); ?>').collapse('toggle');" style="cursor: pointer;">

		<h5 class="entry-list-title panel-title">
			<a href="<?php the_permalink(); ?>" onclick="return false;" rel="bookmark" title="Permanent Link to <?php the_title(); ?>">
				<?php
				if ( get_the_title() ){ $title = the_title('', '', false);
					echo ($title); 
				} 
				?>
                </a>
		</h5>
		<!--BEGIN .entry-meta .entry-header-->
		<div class="entry-meta article-item-subtitle">
<span class="published updated">
<a href="<?php bloginfo('url') ?>/journal/<?php the_field('journal_issn'); ?>"><?php the_field('journal'); ?></a>  
</span>

<?php 
 if (strtotime(str_replace('/', '-', get_field('publication_date')))):
    
        $pubdate = date_create_from_format('d/m/Y', get_field('publication_date', FALSE, FALSE));
        $pubdate = date_format($pubdate, 'M d, Y');
?>
<span class="published updated"><?php echo($pubdate); ?></span>
<?php endif; ?>

<?php if( have_rows('authors') ): 
 
    $author = '';

    while( have_rows('authors') ): the_row(); 

       if ($author == '')
       {
            $author = get_sub_field('first_name') . ' ' . get_sub_field('last_name'); 
       } 
       else
       {
            $author .= ' et al.'; 
            break;
       }

    endwhile; ?>
    
			<span class="author vcard">Author: <strong><?php echo $author; ?></strong></span>    
<?php endif; ?>
			            <!--END .entry-meta .entry-header-->
            	    <a class="abstract-title-toggle" data-toggle="collapse" href="#collapse-<?php the_ID(); ?>" >toggle</a>

		</div>
	
		<?php  if ( current_user_can( 'edit_post', get_the_ID() ) ): ?>
			<?php edit_post_link( __( 'EDIT', 'evolve' ), '<span class="edit-post edit-attach">', '</span>' ); ?>
		<?php  endif; ?>

		</div>

	<!--BEGIN .entry-content .article-->
	<div id="collapse-<?php the_ID(); ?>" class="entry-content abstractText panel-collapse collapse in">

<div class="panel-body">

<?php if( have_rows('abstract') ): 
 
    $content = '';

    while( have_rows('abstract') ): the_row(); 
        
        $content .= get_sub_field('abstract_section') . ': '; 
        $content .= get_sub_field('abstract_text'); 
        
    endwhile; 
    
 		echo shorten_string(strip_tags($content), 70);     
  endif; ?>
	</div>

    <?php include 'article-buttons.php'; ?>	
        	
		<!--END .entry-content .article-->
		<div class="clearfix"></div>
	</div>
	
	<!--END .hentry-->
</div>