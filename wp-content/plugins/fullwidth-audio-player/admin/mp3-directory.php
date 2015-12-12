<div class="wrap">
	<div class="icon32" id="icon-link-manager"><br/></div>
	<h2><?php _e('MP3 Directory', 'radykal'); ?></h2>
	
	<?php
	//check if mp3 dir contains mp3 files
	$mp3_files = $this->getMp3Files();	
	if(sizeof($mp3_files) == 0): ?>
		<div class="error"><p><?php _e('Your MP3 directory does not contain any mp3 files. Move your MP3 files to: ', 'radykal'); ?><strong><?php echo $this->mp3_dir; ?></strong></</div>
		</div>
	<?php 
	exit();
	endif; ?>
	
	<h4><?php _e('Drag your mp3 tracks to an existing playlist:', 'radykal'); ?></h4>
	<?php
	//get all mp3 tracks to check if a mp3 is already attached to a playlist
	$args = array(
		'posts_per_page' => -1,
		'post_type' => 'track'
	);
	$tracks = get_posts( $args );
	$assigned_mp3_tracks = array();
	foreach ( $tracks as $track ) {
		$custom_fields = get_post_custom( $track->ID );
		array_push($assigned_mp3_tracks, $custom_fields['fap_track_path'][0]);
	} 
	
	?>
	<form action="" method="post">
		<ul id="start-mp3-list" class="mp3-list">
		<?php
		$mp3_dir_pathes = array(); 	
		foreach($mp3_files as $file): 
		array_push($mp3_dir_pathes, $file['path']);
		if(!in_array($file['path'], $assigned_mp3_tracks)): ?>
		<?php $img = $file['cover'] == null ? '' : '<img src="'. $file['cover'] .'" alt="cover" />'; ?>
		<li class="clearfix">
			<?php echo $img; ?>
			<div>
				<p class="title"><?php echo $file['title']; ?></p>
				<p class="meta"><?php echo $file['meta']; ?></p>
			</div>
			<input type="hidden" value="<?php echo $file['url']; ?>" name="url" />
			<input type="hidden" value="<?php echo $file['path']; ?>" name="path" />
			<input type="hidden" value="-1" name="id" />
		</li>
		<?php endif; endforeach; ?>
		</ul>
		
		<?php
		$args = array( 'hide_empty' => false );
		$terms = get_terms('dt_playlist', $args);
		
		if(sizeof($terms) == 0): ?>
			<div class="error"><p><?php _e('You need to create a playlist first to assign your mp3 tracks to the player!', 'radykal'); ?></p></div>
		
		<?php
		endif;	
		foreach($terms as $term):
		?>
		<div class="playlist-header clearfix">
			<span><?php echo $term->name; ?></span>
			<a href="#" class="closed"></a>
		</div>
		
		<ul id="<?php echo $term->term_id; ?>" class="mp3-list connected-mp3-list hidden">
		
		<?php
		$args = array(
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'posts_per_page' => -1,
			'post_type' => 'track',
			'tax_query' => array(
				array(
					'taxonomy' => 'dt_playlist',
					'field' => 'id',
					'terms' => $term->term_id
				)
			)
		);
		$query = new WP_Query( $args );
		if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post();
		$custom_fields = get_post_custom( get_the_ID() );
		if(in_array($custom_fields['fap_track_path'][0], $mp3_dir_pathes)): 
		$img = $custom_fields['fap_track_cover'][0] == null ? '' : '<img src="'. $custom_fields['fap_track_cover'][0] .'" alt="cover" />';
		?>
		<li class="clearfix">
			<?php echo $img; ?>
			<div>
				<p class="title"><?php the_title(); ?></p>
				<p class="meta"><?php echo strip_tags(get_the_content()); ?></p>
			</div>
			<div class="delete"></div>
			<input type="hidden" value="<?php echo $custom_fields['fap_track_url'][0]; ?>" name="url" />
			<input type="hidden" value="<?php echo $custom_fields['fap_track_path'][0]; ?>" name="path" />
			<input type="hidden" value="<?php the_ID(); ?>" name="id" />
		</li>
		<?php endif; endwhile; endif; wp_reset_query(); ?>
		</ul>
		<?php endforeach; ?>
		
	</form>
  
</div>

<script>
jQuery(document).ready(function($) {

	$( ".mp3-list" ).sortable({
		dropOnEmpty: true,
		connectWith: ".connected-mp3-list",
		placeholder: "placeholder-highlight",
		containment: '.wrap',
		receive: function(event, ui) {
			
			//create post track
			if(ui.item.parent().attr('id') != "start-mp3-list" && ui.item.find('input[name="id"]').val() == '-1') {
				//console.log('insert');
				var playlistId = ui.item.parent().attr('id'),
					title = ui.item.find('.title').text(),
					meta = ui.item.find('.meta').text(),
					cover = ui.item.children('img').attr('src'),
					url = ui.item.find('input[name="url"]').val(),
					path = ui.item.find('input[name="path"]').val(),
					id = ui.item.find('input[name="id"]').val();
				
										
				$.ajax({ 
					url: '<?php echo admin_url( 'admin-ajax.php'); ?>', 
					data: { action: 'settrack', playlistId: playlistId, title: title, meta: meta, cover: cover, url: url, path:path, id:id }, 
					type: 'post', 
					success: function(data) {
						if(data) {
							//set post id
							ui.item.find('input[name="id"]').val(data);
							ui.item.children('div:first').after('<div class="delete"></div>');
							_updateSort(ui.item.parent().find('input[name="id"]'));	
						}
						else {
							alert('MP3 track could not be set. Please try again!');
						}
					}
				});
			}
		},
		update: function(event, ui) {
			//update post track
			if(ui.sender == null && ui.item.find('input[name="id"]').val() != '-1' && ui.item.parent().attr('id') != "start-mp3-list") {
				//console.log('update');
				var playlistId = ui.item.parent().attr('id'),
					id = ui.item.find('input[name="id"]').val();
				
				$.ajax({ 
					url: '<?php echo admin_url( 'admin-ajax.php'); ?>', 
					data: { action: 'settrack', playlistId: playlistId, id:id }, 
					type: 'post', 
					success: function(data) {
						if(data) {
							_updateSort(ui.item.parent().find('input[name="id"]'));	
						}
						else {
							alert('MP3 track could not be updated. Please try again!');
						}
					}
				});
			}
		}
	}).disableSelection();
	
	//delete post track
	$('div.delete').on('click', function() {
		var $this = $(this),
			$item = $this.parent().remove(),
			id = $item.find('input[name="id"]').val();
		
		$.ajax({ 
			url: '<?php echo admin_url( 'admin-ajax.php'); ?>', 
			data: { action: 'deletetrack', id:id }, 
			type: 'post', 
			success: function(data) {
				if(data) {
					//set post id
					$this.remove();
					$item.remove().find('input[name="id"]').val('-1');
					$('#start-mp3-list').append($item);
				}
				else {
					alert('MP3 track could not be deleted. Please try again!');
				}
			}
		});
	});
	
	//open/close playlist
	$('.playlist-header > a').click(function() {
		var $this = $(this),
			$list = $this.parent().next('ul');
		
		if($list.is(':animated')) {return false;}
		
		if($this.hasClass('closed')) {
			$this.removeClass('closed').addClass('opened');
			$list.slideDown(300);
		}
		else {
			$this.removeClass('opened').addClass('closed');
			$list.slideUp(200);
		}
		return false;
	});
	
	//update sort order
	function _updateSort(idFields) {
		var ids = [];
		idFields.each(function(i, item) {
			ids.push(item.value);
		});
		
		$.ajax({ 
			url: '<?php echo admin_url( 'admin-ajax.php'); ?>', 
			data: { action: 'updatesort', ids: ids }, 
			type: 'post', 
			success: function(data) {
				if(!data) {
					alert('Playlist could not be sorted. Please try again!');
				}
			}
		});
	};
	
	<?php
	if(!current_user_can('manage_options')) {
		echo '$( ".mp3-list" ).sortable( "disable" ); $("div.delete").off("click"); ';
		
	}
	?>
	
});
</script>