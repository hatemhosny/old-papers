<?php
global $xoouserultra;
?>

<div class="uultra-directory3">
<ul>

<?php 

foreach($users_list['users'] as $user)
{
	$user_id = $user->ID;	
	$thumb = $xoouserultra->userpanel->get_profile_bg_url($user_id);			
	$permalink = $xoouserultra->userpanel->get_user_profile_permalink($user_id);
	
	?>
	<li>
			
	<?php	
			//check card background			
			$style_bg_thumb ="";
			$style_bg_thumb ="background-color:#24afb2;";
			
			if($thumb!="")
			{				
				$style_bg_thumb =  'background-image: url('.$thumb.');';			
			}
			
			?>
			
			
			
			<a style="<?php echo $style_bg_thumb; ?>" tabindex="-1" href="<?php echo $permalink?>" class="uultra-profile-card-bg-profile">  </a>	
			
			
			<div class="uultra-my-thumb">
               
                  <?php echo $xoouserultra->userpanel->get_user_pic( $user_id, 70, 'avatar', $pic_boder_type, 'fixed');?>   
              
            </div> 
            
			
			 <p class="uultra-user-name">
             <a href="<?php echo $permalink;?>">
			 <?php echo $xoouserultra->userpanel->get_display_name($user_id)?></a></p> 
			
			<div class="uultra-desc-info">
			 <?php echo $xoouserultra->userpanel->display_optional_fields_pro_minified( $user_id,$display_country_flag, $optional_fields_to_display)?>
			</div>
			
					
			</li>
			
<?php 

} //end for each
			?>
		
</ul>

</div>
