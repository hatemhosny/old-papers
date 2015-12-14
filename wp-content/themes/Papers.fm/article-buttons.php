<script type="text/javascript">
    //paperPath["<?php the_ID(); ?>"] = "<?php echo( get_field('journal_issn') ); ?>/<?php echo( get_field('pmid') ); ?>";
</script>

<div class="entry-content article-buttons row">
    <?php if (!is_singular( 'article' )): ?>
	<div class="read-more btn col-xs-4 col-sm-10 col-md-4 col-lg-2 col-xs-offset-1">
		<a class="col-xs-12" href="<?php the_permalink(); ?>"><span class="fa fa-file-text fa-lg"></span> Read More</a>
	</div>
    <?php endif ?> 
	<div class="read-more btn col-xs-4 col-sm-10 col-md-4 col-lg-2 col-xs-offset-1 col-lg-offset-0">
		<a class="col-xs-12 article-play article-play-button-<?php the_ID(); ?>" href="#" data-item-id="<?php the_ID(); ?>">
            <span class="fa fa-play-circle fa-lg"></span> Play
        </a>
	</div>
	<div class="read-more btn col-xs-4 col-sm-10 col-md-4 col-lg-2 col-xs-offset-1 col-lg-offset-0">
		<a class="col-xs-12 article-addToPlaylist" href="<?php the_permalink(); ?>">
            <span class="add-to-playlist-icon dashicons dashicons-playlist-video"></span> Add to Playlist
        </a>
	</div>
	<div class="read-more btn col-xs-4 col-sm-10 col-md-4 col-lg-2 col-xs-offset-1 col-lg-offset-0">
		<a class="article-share col-xs-12" href="<?php the_permalink(); ?>">
            <span class="fa fa-share-alt fa-lg"></span> Share
        </a>
	</div>
</div>
