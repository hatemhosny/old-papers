<?php
global $xoouserultra;

$post_type = 'post';
		
$profile_customizing = $xoouserultra->customizer->get_profile_customizing();		
$howmany_messages = 5;
				
$main_cont_style = $xoouserultra->userpanel->get_fancy_template_style('main_cont');
$inferior_cont_style = $xoouserultra->userpanel->get_fancy_template_style('inferior_cont');		
		
if(!$xoouserultra->userpanel->has_profile_bg($user_id))
{
	//image background color		
	$user_prof_bg_color =$xoouserultra->userpanel->get_fancy_template_style('user_prof_bg_color');		
											
}

//check if paid membership for customizing.
$xoouserultra->customizer->uultra_is_paid_user($user_id);
		
?>
<div class="uultra-prof-cont" <?php echo  $main_cont_style?> >
		
	<div class="uultra-inner" <?php echo  $main_cont_style?>>
            <div class="uultra-inner-menu">             
                 <?php echo $xoouserultra->userpanel->get_top_profile_navigator_links($user_id);?>                 
            </div>
			
		
		<div class="uultra-card-bg" <?php echo $user_prof_bg_color?>> 	

		<?php echo  $xoouserultra->userpanel->get_profile_bg($user_id); ?>	        
        
		<div class="uultra-bg-pic">
                  <div class="uultra-avatar">
                      <?php echo $xoouserultra->userpanel->get_user_pic( $user_id, $pic_size, $pic_type, $pic_boder_type,  $pic_size_type);?>
                  </div>       
				  
				  <div class="uultra-display-name">             
                    <h1 class="uultra-name"> <?php echo $xoouserultra->userpanel->get_display_name($user_id);?></h1> 					
				 </div>
                 
                 <div class="uultra-profile-extra-info-badges-mobile">                        
                      				 
					<?php echo $xoouserultra->userpanel->display_optional_fields_pro( $user_id,$display_country_flag, $optional_fields_to_display); ?>
                
                 </div> 
                
            </div> 
            
      
      </div>
      
      </div> 	
            
            
		<?php if($disable_navigation_bar!='no'){?>
		  <div class="uultra-social-activities">
             <ul>			   
		 		<?php echo $xoouserultra->userpanel->get_profile_navitagor_links($user_id);  ?>
             </ul>
          </div>
          
        <?php } ?>	
		 
		  <div class="cont-profile"  <?php echo $inferior_cont_style;?>>
		 
		<?php if( !isset($_GET['gal_id']) && !isset($_GET['photo_id']) && !isset($_GET['my_galleries'])  && !isset($_GET['my_followers'] )  && !isset($_GET['my_posts']) && !isset($_GET['my_following'])  && !isset($_GET['my_friends'])  && !isset($_GET['my_videos']) && !isset($_GET['my_topics'])) 
		{
		
		?>
			
		 <?php // get dimension 
		  
		  $dimension_style = $xoouserultra->userpanel->get_width_of_column(count($cols));
			
		  //check how many columns         
		  if(in_array(1, $cols))
		  {
			  //det lenght			  
			  if(count($cols)==1 || count($cols)==2 || count($cols)==3)
		  	  {
				?>	
                		  
				 <!------ Column 1 ----->
                 <div class="col-uultra-1" <?php echo $dimension_style?>>
					<ul><?php echo $xoouserultra->customizer->get_profile_col_widgets($user_id,1, $cols, $atts)?></ul>
                 </div>
                 	
			 <?php 
			 }
			 
			            
          } 
		   
		  if(in_array(2, $cols))
		  {
			   //det lenght			   
			    if(count($cols)==2 || count($cols)==3)
		  		{
					
				?>
              
				  <!------ Column 2 ----->
                  <div class="col-uultra-2" <?php echo $dimension_style?>><ul><?php echo $xoouserultra->customizer->get_profile_col_widgets($user_id,2,$cols, $atts)?></ul></div>
                  
               <?php
			  } 
		  } 
		  
		  if(in_array(3, $cols))
		  {
			   //det lenght			   
			    if(count($cols)==3)
		  		{
					
				?>
			   
				 <!------ Column 3 ----->
                 <div class="col-uultra-3" <?php echo $dimension_style?>><ul>
					 <?php echo $xoouserultra->customizer->get_profile_col_widgets($user_id,3,$cols, $atts);?></ul>     
				  </div> 
			  <?php
			  }
		  }
		  
		  ?>
			                           
		 
			
		 <input type="hidden" id="howmany_messages" value=" <?php echo $howmany_messages;?>">	 
		 <script type="text/javascript" > xoo_load_wallmessags(<?php echo $user_id; ?> );</script>
		
		<?php }elseif(isset($_GET['my_galleries']) ){ ?>
			
			 <!------ Galleries 3 ----->
           	<div class="photolist">
			<h2><?php _e("My Photo Galleries", 'xoousers')?> </h2>
			<ul>
						
				<?php echo $xoouserultra->photogallery->reload_galleries_public($user_id, $gallery_type);?>
                			
			</ul>			
			</div>
			
		
		<?php }elseif(isset($_GET['gal_id']) && $_GET['gal_id']>0){
			
			//display photos in gallery			
			$gal_id = $_GET['gal_id'];
			
			//get selected gallery
		    $current_gal = $xoouserultra->photogallery->get_gallery_public($gal_id, $user_id);
			
			$xoouserultra->statistc->update_hits($gal_id, 'gallery');
			
			?>
			
			<div class="photo-gal-nav">
            <a href="<?php echo $xoouserultra->userpanel->get_user_profile_permalink( $user_id);?>">
			<?php _e("Main", 'xoousers')?></a>  / <?php echo $current_gal->gallery_name?> </div>
			 
			<div class="photos">			
			<ul>
						
			<?php echo $xoouserultra->photogallery->get_photos_of_gal_public($gal_id, $display_photo_rating, $display_photo_description, $display_photo_title, $gallery_type); ?>
			
			</ul>		
			</div>
		
		<?php }elseif(isset($_GET['photo_id']) && $_GET['photo_id']>0){
			
			$photo_id = $_GET['photo_id'];
			
			$current_photo = $xoouserultra->photogallery->get_photo($photo_id, $user_id);		
			 
			 //get selected gallery
		    $current_gal = $xoouserultra->photogallery->get_gallery_public( $current_photo->photo_gal_id, $user_id);
			
			if( $current_gal->gallery_name!="" && $photo_id > 0)
			{				  
				  $xoouserultra->statistc->update_hits($photo_id, 'photo');	
			}
			
			?>
			
			<div class="uultra_photo_single">
           		<?php echo $xoouserultra->photogallery->get_single_photo($photo_id, $user_id, $display_photo_rating, $display_photo_description);?>
             </div>
			
			 
		<?php }elseif(isset($_GET['my_followers']) ){ ?>
			
			<!------ Followers ----->
			
			<div class="my-follow">
                <h2><?php _e("Followers ", 'xoousers')?> </h2>
                <ul>
                            
                <?php echo  $xoouserultra->social->show_my_followers($user_id); ?>
                
                </ul>		
			</div>
            
	   <?php  }elseif(isset($_GET['my_videos']) ){ ?>
			
			
			<div class="videolist">
                <h2><?php _e("My Videos ", 'xoousers');?> </h2>
                <ul>
                            
                <?php echo $xoouserultra->photogallery->reload_videos_public($user_id);?>
                
                </ul>			
			</div> 
		
		<?php  }elseif(isset($_GET['my_following']) ){ ?>
			
			<!------ Following ----->			
			<div class="my-follow">
                <h2><?php _e("Following ", 'xoousers')?> </h2>
                <ul>
                            
                <?php echo $xoouserultra->social->show_my_following($user_id);?>
                
                </ul>			
			</div> 
		
		<?php }elseif(isset($_GET['my_friends']) ){ ?>
			
			<!------ My Friends ----->
			
			<div class="my-follow">
                <h2><?php _e("My Friends ", 'xoousers')?> </h2>
                <ul>
                            
                <?php echo $xoouserultra->social->show_my_friends($user_id);?>
                
                </ul>			
			</div>		
		
		
		<?php }elseif(isset($_GET['my_posts']) ){ ?>
			
			<!------ My Posts ----->		
			<div class="my-posts">
                <h2><?php _e("My Posts ", 'xoousers')?> </h2>
                <ul>
                            
                <?php echo $xoouserultra->publisher->show_my_posts_in_profile($user_id,'post'); ?>
                
                </ul>			
			</div> 
		
		<?php }elseif(isset($_GET['my_topics']) ){ ?>
			
			<!------ My Topics ----->		
			<div class="my-topics">
                <h2><?php _e("Forum Topics I've Started ", 'xoousers');?> </h2>
                <ul>
                            
                <?php echo $xoouserultra->bbpress->show_my_topics_in_profile($user_id,'post'); ?>
                
                </ul>			
			</div>
		
		<?php } ?>
		
		 </div>
		</div> <!------ end  uultra-prof-cont----->
		<input type="hidden" value="<?php echo $user_id?>" id="receiver_id">		 
		<?php echo $xoouserultra->userpanel->contact_me_public_form();  ?>
