<script type="text/javascript">
    var paper_playing = false;
    var paper_playing_id = '';
    var currentTime = 0;
    var playerReady = false;

    // holder for papers data
    var papers = {};




jQuery(document).ready(function($){


    
        //bind the click event of the play button and icon to the player
        $(".article-play").click(function ()
        {
            playPaper($(this).attr("data-item-id"));
            return false;
        });




        $(".article-addToPlaylist").click(function ()
        {
            $('#modal-message').html('Adding to playlist: My Playlist' +
                     '<br /><a href="#">Change</a>');
            $('#notification-modal').modal('show');
            return false;
        });


        $(".article-share").click(function ()
        {
            alert("Not implemented yet!");
            return false;
        });

        $("#player-bar-play").click(function ()
        {
            playPaper();
            return false;
        });

        $("#full-player-play").click(function ()
        {
            playPaper();
            return false;
        });



        $(function ()
        {
            $('#notification-modal').on('show.bs.modal', function ()
            {
                var myModal = $(this);
                clearTimeout(myModal.data('hideInterval'));
                myModal.data('hideInterval', setTimeout(function ()
                {
                    myModal.modal('hide');
                }, 3000));
            });

            $("#player-slider").slider({
                range: "min",
                value: 37,
                min: 1,
                max: 700,
                slide: function (event, ui)
                {
                    $("#amount").val("$" + ui.value);
                }
            });
            $("#amount").val("$" + $("#player-slider").slider("value"));
        });

        //testing
        //$('#full-player').modal('show');




	var myPlayer = $("#papers_player"),
		myPlayerData,
		fixFlash_mp4, // Flag: The m4a and m4v Flash player gives some old currentTime values when changed.
		fixFlash_mp4_id, // Timeout ID used with fixFlash_mp4
		ignore_timeupdate, // Flag used with fixFlash_mp4
		options = {
			ready: function (event) {

                // tell the page that the player is ready
                playerReady = true;

				// Hide the volume slider on mobile browsers. ie., They have no effect.
				if(event.jPlayer.status.noVolume) {
					// Add a class and then CSS rules deal with it.
					$(".jp-gui").addClass("jp-no-volume");
				}
				// Determine if Flash is being used and the mp4 media type is supplied. BTW, Supplying both mp3 and mp4 is pointless.
				fixFlash_mp4 = event.jPlayer.flash.used && /m4a|m4v/.test(event.jPlayer.options.supplied);
				// Setup the player with media.
				$(this).jPlayer("setMedia", {
					mp3: "http://www.jplayer.org/audio/mp3/Miaow-07-Bubble.mp3"
				});
			},
			timeupdate: function(event) {

                // update play-bar progress
                updatePlayerProgress();
				
                
                if(!ignore_timeupdate) {
					myControl.progress.slider("value", event.jPlayer.status.currentPercentAbsolute);
				}
			},
			volumechange: function(event) {
				if(event.jPlayer.options.muted) {
					myControl.volume.slider("value", 0);
				} else {
					myControl.volume.slider("value", event.jPlayer.options.volume);
				}
			},
            swfPath: '/player',
            solution: 'html, flash',
            supplied: 'mp3',
            preload: 'metadata',
            volume: 0.8,
            muted: false,
            cssSelectorAncestor: '#jp_container_1',
            errorAlerts: false,
            warningAlerts: false,
			wmode: "window",
			keyEnabled: true
		},
		myControl = {
			progress: $(options.cssSelectorAncestor + " .jp-progress-slider"),
			volume: $(options.cssSelectorAncestor + " .jp-volume-slider")
		};

	// Instance jPlayer
	myPlayer.jPlayer(options);

	// A pointer to the jPlayer data object
	myPlayerData = myPlayer.data("jPlayer");

	// Define hover states of the buttons
	$('.jp-gui ul li').hover(
		function() { $(this).addClass('ui-state-hover'); },
		function() { $(this).removeClass('ui-state-hover'); }
	);

	// Create the progress slider control
	myControl.progress.slider({
		animate: "fast",
		max: 100,
		range: "min",
		step: 0.1,
		value : 0,
		slide: function(event, ui) {
			var sp = myPlayerData.status.seekPercent;
			if(sp > 0) {
				// Apply a fix to mp4 formats when the Flash is used.
				if(fixFlash_mp4) {
					ignore_timeupdate = true;
					clearTimeout(fixFlash_mp4_id);
					fixFlash_mp4_id = setTimeout(function() {
						ignore_timeupdate = false;
					},1000);
				}
				// Move the play-head to the value and factor in the seek percent.
				myPlayer.jPlayer("playHead", ui.value * (100 / sp));
			} else {
				// Create a timeout to reset this slider to zero.
				setTimeout(function() {
					myControl.progress.slider("value", 0);
				}, 0);
			}
		}
	});

	// Create the volume slider control
	myControl.volume.slider({
		animate: "fast",
		max: 1,
		range: "min",
		step: 0.01,
		value : $.jPlayer.prototype.options.volume,
		slide: function(event, ui) {
			myPlayer.jPlayer("option", "muted", false);
			myPlayer.jPlayer("option", "volume", ui.value);
		}
	});

});









    function playPaper(paper_id)
    {
        // if the play button of the player is pressed
        if (paper_id === undefined || paper_id === null)
        {
            paper_id = paper_playing_id;
        }


        // if playing, then pause
        if ((paper_playing == true) && (paper_playing_id == paper_id))
        {
            currentTime = jQuery("#papers_player").data("jPlayer").status.currentTime;
            jQuery('#papers_player').jPlayer('pause');
            jQuery('.article-play-button-' + paper_id)
                         .html('<span class="fa fa-play-circle fa-lg"></span> Play');
            jQuery('.article-play-icon-' + paper_id)
                         .html('<i class="fa fa-play-circle fa-2x"></i>')
                         .attr('title', 'Play')
                         .removeClass("playing");
            jQuery('#player-bar-play')
                             .html('<i class="fa fa-play-circle fa-3x"></i>')
                             .attr('title', 'Play');
            jQuery('#full-player-play')
                             .html('<i class="fa fa-play-circle fa-3x"></i>')
                             .attr('title', 'Play');

            paper_playing = false;

        }
        else    // else play
        {
            if (playerReady == true)
            {
                jQuery('#papers_player').jPlayer("setMedia", {
                    title: papers[paper_id].title,
                    mp3: "https://s3.amazonaws.com/cdn01.papers.fm/" + papers[paper_id].journalISSN + "/" + papers[paper_id].pmid + ".mp3"
                });

                // if a new paper, reset timer, article button and icon
                if (paper_playing_id != paper_id)
                {
                    currentTime = 0;
                    jQuery('.article-play-button-' + paper_playing_id)
                                 .html('<span class="fa fa-play-circle fa-lg"></span> Play');
                    jQuery('.article-play-icon-' + paper_playing_id)
                                 .html('<i class="fa fa-play-circle fa-2x"></i>')
                                 .attr('title', 'Play')
                                 .removeClass("playing");
                }

                // play
                jQuery('#papers_player').jPlayer('play', currentTime);


                // update article item button and icon and player
                jQuery('.article-play-button-' + paper_id)
                             .html('<span class="fa fa-pause-circle fa-lg"></span> Pause');
                jQuery('.article-play-icon-' + paper_id)
                             .html('<i class="fa fa-pause-circle fa-2x"></i>')
                             .attr('title', 'Pause')
                             .addClass("playing");
                jQuery('#player-bar-play')
                             .html('<i class="fa fa-pause-circle fa-3x"></i>')
                             .attr('title', 'Pause');
                jQuery('#full-player-play')
                             .html('<i class="fa fa-pause-circle fa-3x"></i>')
                             .attr('title', 'Pause');
                jQuery('#player-bar-title')
                         .html(
                                 papers[paper_id].title +
                                 '<div class="small">' + papers[paper_id].journal + '</div>'
                             );
                jQuery('#player-bar.hidden').css('visibility', 'visible').hide().fadeIn().removeClass('hidden');

                paper_playing = true;
                paper_playing_id = paper_id;

            }
        }

        return false;
    }

    function updatePlayerProgress()
    {
        progress = (jQuery("#papers_player").data("jPlayer").status.currentTime / jQuery("#papers_player").data("jPlayer").status.duration) * 100
        jQuery('#player-bar-progress').css('width', progress + '%');
    }

</script>


<div id="papers_player" class="jp-jplayer"></div>
<div id="player-bar" class="navbar navbar-fixed-bottom hidden">
    <div id="player-bar-progress-bar">
        <div id="player-bar-progress"></div>
    </div>
    <div>
      <a id="player-bar-title" class="pull-left col-xs-10" data-toggle="modal" href="#full-player">
      </a>

      <div id="player-bar-controls" class="pull-right col-xs-2">
            	<a id="player-bar-backward" title="Previous" class="hidden-xs hidden-sm" href="#">
                    <i class="glyphicon glyphicon-fast-backward fa-lg"></i>
                </a>
            	<a id="player-bar-play" title="Play" href="#">
                    <i class="fa fa-play-circle fa-3x"></i>
                </a>
            	<a id="player-bar-forward" title="Next" class="hidden-xs hidden-sm" href="#">
                    <i class="glyphicon glyphicon-fast-forward fa-lg"></i>
                </a>
            	<a id="player-bar-fullscreen" class="pull-right visible-lg" title="Fullscreen" data-toggle="modal" href="#full-player">
                    <i class="fa fa-expand fa-lg"></i>
                </a>
      </div>


        <!-- for testing 
        <p class="pull-right">
            <span class="visible-xs">XS</span>
            <span class="visible-sm">SM</span>
            <span class="visible-md">MD</span>
            <span class="visible-lg">LG</span>
        </p>
         -->

    </div>
</div>

<!-- Modal -->
<div id="notification-modal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-sm">

    <!-- Modal content-->
    <div class="modal-content">
      <div id="modal-message" class="modal-body">
      </div>
    </div>

  </div>
</div>



<!-- modal -->
<div id="full-player"
     class="modal"
     tabindex="-1"
     role="dialog"
     aria-labelledby="myModalLabel"
     aria-hidden="true">

  <!-- dialog -->
  <div class="modal-dialog">

    <!-- content -->
    <div class="modal-content">

      <!-- header -->
      <div class="modal-header">
        <h1 id="myModalLabel"
            class="modal-title">
          Modal title
        <a href="#" class="close" data-dismiss="modal" aria-label="close">&times;</a>
        </h1>
      </div>
      <!-- header -->
      
      <!-- body -->
      <div class="modal-body">
        <h2>1. Modal sub-title</h2>

        <p>Liquor ipsum dolor sit amet bearded lady, grog murphy's bourbon lancer. Kamikaze vodka gimlet; old rip van winkle, lemon drop martell salty dog tom collins smoky martini ben nevis man o'war. Strathmill grand marnier sea breeze b & b mickey slim. Cactus jack aberlour seven and seven, beefeater early times beefeater kalimotxo royal arrival jack rose. Cutty sark scots whisky b & b harper's finlandia agent orange pink lady three wise men gin fizz murphy's. Chartreuse french 75 brandy daisy widow's cork 7 crown ketel one captain morgan fleischmann's, hayride, edradour godfather. Long island iced tea choking hazard black bison, greyhound harvey wallbanger, "gibbon kir royale salty dog tonic and tequila."</p>


      </div>
      <!-- body -->

      <!-- footer -->
      <div class="modal-footer">
          <div class="container-fluid"> 

		<div id="jp_container_1">
			<div class="jp-gui">
                <div class="row col-xs-12">
				    <div class="jp-progress-slider col-xs-11 col-md-8 col-lg-9" style="margin: auto 20px;"></div>
				    <div class="jp-volume-slider col-md-2 hidden-xs hidden-sm" style="margin: auto 20px;"></div>
                </div>
                <div class="row col-xs-12">
                    <div class="col-xs-11 col-md-8 col-lg-9" style="margin: auto 20px;">
				        <div class="jp-current-time pull-left"></div>
				        <div class="jp-duration pull-right"></div>
                    </div>
                    <div id="full-player-volume-control" class="col-md-2 hidden-xs hidden-sm">
				        <div class="pull-left">
                            <a href="javascript:;" class="jp-mute" tabindex="1" title="mute">
                               <i class="fa fa-volume-off fa-lg"></i>
                            </a>
                            <a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">
                               <i class="fa fa-volume-down fa-lg"></i>
                            </a>
                        </div>
				        <div class="pull-right" style="margin-right: -40px;">
                            <a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">
                               <i class="fa fa-volume-up fa-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>



                <div id="full-player-controls">
                <div class="row col-xs-12">
            	    <a id="full-player-previous" title="Previous" href="#">
                        <i class="glyphicon glyphicon-fast-backward fa-lg"></i>
                    </a>
            	    <a id="full-player-backward" title="Previous" href="#">
                        <i class="glyphicon glyphicon-backward fa-lg"></i>
                    </a>
            	    <a id="full-player-play" title="Play" href="#">
                        <i class="fa fa-play-circle fa-3x"></i>
                    </a>
            	    <a id="full-player-forward" title="Forward" href="#">
                        <i class="glyphicon glyphicon-forward fa-lg"></i>
                    </a>
            	    <a id="full-player-next" title="Next" href="#">
                        <i class="glyphicon glyphicon-fast-forward fa-lg"></i>
                    </a>
                </div>
                </div>




			</div>
			<div class="jp-no-solution">
				<span>Update Required</span>
				To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
			</div>
		</div>



          </div>
      </div>
      <!-- footer -->

    </div>
    <!-- content -->

  </div>
  <!-- dialog -->

</div>
<!-- modal -->