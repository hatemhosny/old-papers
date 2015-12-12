<?php
global $uultra_wall, $xoouserultra;

$group_id = $_GET["group_id"];




?>



<div class="user-ultra-sect " id="uultra-site-wide-wall-container">
 <?php echo $xoouserultra->wall->uultra_get_wall($user_id, $howmany, $template_width);?>
</div>
        
