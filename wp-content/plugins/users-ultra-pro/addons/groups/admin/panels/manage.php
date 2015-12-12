<?php
global $uultra_group;

$group_id = $_GET["group_id"];




?>
<div class="user-ultra-sect ">
        
<?php 

if($group_id!=''){
	
	
	
	//get group	
	$group = $uultra_group->get_one($group_id);
	
	?>
	<form action="" method="post" id="uultra-userslist">
          <input type="hidden" name="edit-group" value="edit-group" />
           <input type="hidden" name="group_id" value="<?php echo $group_id?>" />
          
          
          <h3><?php _e('Edit Group ','xoousers'); ?></h3>
          
          
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
           <tr>
             <td width="6%"><?php _e('Name: ','xoousers'); ?></td>
             <td width="94%"><input type="text" id="group_name"  name="group_name" value="<?php echo $group->group_name?>"  /></td>
           </tr>
           
            <tr>
             <td width="6%"><?php _e('Description: ','xoousers'); ?></td>
             <td width="94%">
                            <textarea name="group_desc" id="group_desc" cols="45" rows="5"><?php echo $group->group_desc?></textarea></td>
           </tr>
           
           <tr>
             <td width="6%"><?php _e('Visibility: ','xoousers'); ?></td>
             <td width="94%"><label for="select"></label>
               <select name="group_privacy" id="group_privacy">
                 <option value="1"  <?php if($group->group_privacy== 1){ echo 'selected="selected"';}?>>Public Group</option>
                 <option value="0" <?php if($group->group_privacy== 0){ echo 'selected="selected"';}?>>Private Group</option>
             </select></td>
           </tr>
          </table>
          
           <p>
            <a href="?page=uultra-groups&tab=manage" class="button button-secondary "   ><i class="uultra-icon-plus"></i>&nbsp;&nbsp;<?php _e('Back','xoousers'); ?>
</a>
           <input name="submit" type="submit"  class="button-primary" value="<?php _e('Confirm','xoousers'); ?>"/>
          
    </p>
          
          </form>
	
<?php 	
}else{

$groups = $uultra_group->get_all();

?>
<form action="" method="post" id="uultra-userslist">
          <input type="hidden" name="add-group" value="add-group" />
        
        <div class="user-ultra-success uultra-notification"><?php _e('Success ','xoousers'); ?></div>
        
    <h3><?php _e('Add New Group ','xoousers'); ?></h3>
         
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
           <tr>
             <td width="6%"><?php _e('Name: ','xoousers'); ?></td>
             <td width="94%"><input type="text" id="group_name"  name="group_name"  /></td>
           </tr>
           
            <tr>
             <td width="6%"><?php _e('Description: ','xoousers'); ?></td>
             <td width="94%">
                            <textarea name="group_desc" id="group_desc" cols="45" rows="5"></textarea></td>
           </tr>
           
            <tr>
             <td width="6%"><?php _e('Visibility: ','xoousers'); ?></td>
             <td width="94%"><label for="select"></label>
               <select name="group_privacy" id="group_privacy">
                 <option value="1" selected="selected">Public Group</option>
                 <option value="0">Private Group</option>
             </select></td>
           </tr>
          </table>
          
           <p>
           
         
           <input name="submit" type="submit"  class="button-primary" value="<?php _e('Confirm','xoousers'); ?>"/>
          
    </p>
          
   
        </form>
        
                 <?php
			
			
				
				if (!empty($groups)){
				
				
				?>
       
           <table width="100%" class="wp-list-table widefat fixed posts table-generic">
            <thead>
                <tr>
                    <th width="4%" style="color:# 333"><?php _e('#', 'xoousers'); ?></th>
                    <th width="12%"><?php _e('Name', 'xoousers'); ?></th>
                    <th width="24%"><?php _e('Visibility', 'xoousers'); ?></th>
                     <th width="19%"><?php _e('Users', 'xoousers'); ?></th>
                    <th width="13%">&nbsp;</th>
                    <th width="8%">&nbsp;</th>
                    <th width="20%"><?php _e('Actions', 'xoousers'); ?></th>
                </tr>
            </thead>
            
            <tbody>
            
            <?php 
			
				foreach ( $groups as $c )
				{
					
					if($c->group_privacy==1){
						
						$visibility = 'Public';
						
					}elseif($c->group_privacy==0){
						
						$visibility = 'Private';
					
					}
					
					//get group users
					$total_users = $uultra_group->get_all_users_group_total($c->group_id);
			?>
              

                <tr  id="uu-edit-cate-row-<?php echo $c->group_id; ?>">
                    <td><?php echo $c->group_id; ?></td>
                    <td  id="uu-edit-cate-row-name-<?php echo $c->group_id; ?>"><?php echo $c->group_name; ?></td>
                    <td><?php echo $visibility?></td>
                    <td><?php echo $total_users?></td>
                    <td>&nbsp;</td>
                     <td>&nbsp;</td>
                   <td> <a href="#" class="button button-secondary user-ultra-btn-red uultra-group-del"  id="" data-gal="<?php echo $c->group_id; ?>"><i class="uultra-icon-plus"></i>&nbsp;&nbsp;<?php _e('Delete','xoousers'); ?>
                   </a>  <a href="?page=uultra-groups&tab=manage&group_id=<?php echo $c->group_id; ?>" class="button button-secondary "  id="" data-gal="<?php echo $c->group_id; ?>"><i class="uultra-icon-plus"></i>&nbsp;&nbsp;<?php _e('Edit','xoousers'); ?>
</a> </td>
                </tr>
                
                
                <tr>
                
                 <td colspan="7" ><div id='uu-edit-cate-box-<?php echo $c->group_id; ?>'></div> </td>
                
                </tr>
                <?php
					}
					
					} else {
			?>
			<p><?php _e('There are no groups yet.','xoousers'); ?></p>
			<?php	} ?>

            </tbody>
        </table>
        
 <?php	} ?>            

</div>