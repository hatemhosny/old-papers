
<script type="text/javascript" src="/player/jquery.jplayer.min.js"></script>
<script type="text/javascript">
    var paper_playing = false;
    var paper_playing_id = '';
    var currentTime = 0;
    var playerReady = false;

    // holder for papers data
    var papers = {};

    $.noConflict();
    jQuery(document).ready(function ($)
    {
        $('#papers_player').jPlayer({
            ready: function ()
            {
                $(this).jPlayer("setMedia", {
                    title: "Papers.fm",
                    mp3: ""
                });
                playerReady = true;
            },
            timeupdate: function (event)
            {
                updatePlayerProgress();
            },
            swfPath: '/player',
            solution: 'html, flash',
            supplied: 'mp3',
            preload: 'metadata',
            volume: 0.8,
            muted: false,
            cssSelectorAncestor: '#jp_container_1',
            cssSelector: {
                play: '.jp-play',
                pause: '.jp-pause',
                stop: '.jp-stop',
                seekBar: '.jp-seek-bar',
                playBar: '.jp-play-bar',
                mute: '.jp-mute',
                unmute: '.jp-unmute',
                volumeBar: '.jp-volume-bar',
                volumeBarValue: '.jp-volume-bar-value',
                volumeMax: '.jp-volume-max',
                playbackRateBar: '.jp-playback-rate-bar',
                playbackRateBarValue: '.jp-playback-rate-bar-value',
                currentTime: '.jp-current-time',
                duration: '.jp-duration',
                title: '.jp-title',
                fullScreen: '.jp-full-screen',
                restoreScreen: '.jp-restore-screen',
                repeat: '.jp-repeat',
                repeatOff: '.jp-repeat-off',
                gui: '.jp-gui',
                noSolution: '.jp-no-solution'
            },
            errorAlerts: false,
            warningAlerts: false
        });

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
        progress = (jQuery("#papers_player").data("jPlayer").status.currentTime/jQuery("#papers_player").data("jPlayer").status.duration)*100
        jQuery('#player-bar-progress').css('width', progress + '%');
    }

</script>


<div id="papers_player" class="jp-jplayer"></div>
<div id="player-bar" class="navbar navbar-default navbar-fixed-bottom hidden">
    <div id="player-bar-progress-bar">
        <div id="player-bar-progress"></div>
    </div>
    <div>
      <a href="#" id="player-bar-title" class="pull-left col-xs-10">
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
            	<a id="player-bar-fullscreen" class="pull-right visible-lg" title="Fullscreen" data-toggle="modal" href="#fsModal">
                    <i class="glyphicon glyphicon-fullscreen fa-lg"></i>
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
<div id="fsModal"
     class="modal animated zoomIn"
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
        </h1>
      </div>
      <!-- header -->
      
      <!-- body -->
      <div class="modal-body">
        <h2>1. Modal sub-title</h2>

        <p>Liquor ipsum dolor sit amet bearded lady, grog murphy's bourbon lancer. Kamikaze vodka gimlet; old rip van winkle, lemon drop martell salty dog tom collins smoky martini ben nevis man o'war. Strathmill grand marnier sea breeze b & b mickey slim. Cactus jack aberlour seven and seven, beefeater early times beefeater kalimotxo royal arrival jack rose. Cutty sark scots whisky b & b harper's finlandia agent orange pink lady three wise men gin fizz murphy's. Chartreuse french 75 brandy daisy widow's cork 7 crown ketel one captain morgan fleischmann's, hayride, edradour godfather. Long island iced tea choking hazard black bison, greyhound harvey wallbanger, "gibbon kir royale salty dog tonic and tequila."</p>

        <h2>2. Modal sub-title</h2>

        <p>The last word drumguish irish flag, hurricane, brandy manhattan. Lemon drop, pulteney fleischmann's seven and seven irish flag pisco sour metaxas, hayride, bellini. French 75 wolfram christian brothers, calvert painkiller, horse's neck old bushmill's gin pahit. Monte alban glendullan, edradour redline cherry herring anisette godmother, irish flag polish martini glen spey. Abhainn dearg bloody mary amaretto sour, ti punch black cossack port charlotte tequila slammer? Rum swizzle glen keith j & b sake bomb harrogate nights 7 crown! Hairy virgin tomatin lord calvert godmother wolfschmitt brass monkey aberfeldy caribou lou. Macuá, french 75 three wise men.</p>

        <h2>3. Modal sub-title</h2>

        <p>Pisco sour daiquiri lejon bruichladdich mickey slim sea breeze wolfram kensington court special: pink lady white lady or delilah. Pisco sour glen spey, courvoisier j & b metaxas glenlivet tormore chupacabra, sambuca lorraine knockdhu gin and tonic margarita schenley's." Bumbo glen ord the macallan balvenie lemon split presbyterian old rip van winkle paradise gin sling. Myers black bison metaxa caridan linkwood three wise men blue hawaii wine cooler?" Talisker moonwalk cosmopolitan wolfram zurracapote glen garioch patron saketini brandy alexander, singapore sling polmos krakow golden dream. Glenglassaugh usher's wolfram mojito ramos gin fizz; cactus jack. Mai-tai leite de onça bengal; crown royal absolut allt-á-bhainne jungle juice bacardi benrinnes, bladnoch. Cointreau four horsemen aultmore, "the amarosa cocktail vodka gimlet ardbeg southern comfort salmiakki koskenkorva."</p>

      </div>
      <!-- body -->

      <!-- footer -->
      <div class="modal-footer">
        <button class="btn btn-secondary"
                data-dismiss="modal">
          close
        </button>
        <button class="btn btn-default">
          Default
        </button>
        <button class="btn btn-primary">
          Primary
        </button>
      </div>
      <!-- footer -->

    </div>
    <!-- content -->

  </div>
  <!-- dialog -->

</div>
<!-- modal -->