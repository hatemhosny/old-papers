
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



    });



    function playPaper(paper_id)
    {
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
            paper_playing = false;

        }
        else    // else play
        {
            if (playerReady == true)
            {
                jQuery('#papers_player').jPlayer("setMedia", {
                    title: "Bubble",
                    mp3: "https://s3.amazonaws.com/cdn01.papers.fm/" + papers[paper_id].journalISSN + "/" + papers[paper_id].pmid + ".mp3"
                });

                // if a new paper, reset the timer and the button
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

                jQuery('#papers_player').jPlayer('play', currentTime);

                jQuery('.article-play-button-' + paper_id)
                    .html('<span class="fa fa-pause-circle fa-lg"></span> Pause');

                jQuery('.article-play-icon-' + paper_id)
                    .html('<i class="fa fa-pause-circle fa-2x"></i>')
                    .attr('title', 'Pause')
                    .addClass("playing");

                paper_playing = true;
                paper_playing_id = paper_id;

            }
        }

        return false;
    }

</script>


<div id="papers_player" class="jp-jplayer"></div>
<div id="player-bar" class="navbar navbar-default navbar-fixed-bottom">
    <div class="container">
      <p class="navbar-text pull-left">The Player Controls to be placed here</p>


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
