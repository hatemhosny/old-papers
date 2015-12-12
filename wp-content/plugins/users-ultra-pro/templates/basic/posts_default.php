<?php
global $xoouserultra;


//print_r($users_list);

$display_default_search = true;

?>


<div class="uultra-latest-posts-shortcode">
                       <ul>
		
<?php		foreach ( $dr as $row )
			{
				
				$permalink = get_permalink( $row->ID ); 
				
				$thumb = get_the_post_thumbnail($row->ID, 'thumbnail');
				$comment_count = wp_count_comments($row->ID);			
				$desc = $this->get_excerpt_by_id($row->post_content, 15);
				
				//$desc = apply_filters('the_content', $desc);
				
				?>
										   
						   
				    <li>
							   <span><a href="<?php echo $permalink?>"><?php echo $thumb?></a></span>
							   <div class="uultra-latest-descrip">
								<p class="uultra-tit"><a href="<?php echo $permalink?>" > <?php echo $row->post_title?></a></p>
								<p class="uultra-date"><?php echo date("m/d/Y",strtotime($row->post_date))?></p>
								<p class="uultra-text" ><?php echo $desc?></p>
							   </div> 
								<div class="uultra-comment-icons">
								 <div class="uultra-more-icons"><a href="<?php echo $permalink?>#comments"><i class="fa fa-lg fa-comments-o  uultra-small-icon"></i> <?php echo $comment_count->approved ?></a></div>
								 <div class="uultra-more-icons"><i class="fa fa-lg  fa-star  uultra-small-icon"></i></div>                             
								</div>
							 </li>';
			<?php }?>	 
			
		
		</ul>	    
		</div>