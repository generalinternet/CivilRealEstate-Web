//Youtube video player
//Since Chrome doesn't support autoplay, use this.
//v 0.1

var playerArray = new Array();

//This function creates an <iframe> (and YouTube player)
//after the API code downloads.
function onYouTubeIframeAPIReady() {
    //onYouTubeIframeAPIReady is called only once after downloading (https://www.youtube.com/iframe_api)
    var playerIdx = 1;
    $('.youtube-player').each(function(){
        var videoId = $(this).data('id');
        var playerId = 'player_'+playerIdx;
        
        var videoHeight = '390';
        if ($(this).data('height') !== undefined) {
            videoHeight = $(this).data('height');
        }
        var videoWidth = '640';
        if ($(this).data('width') !== undefined) {
            videoWidth = $(this).data('width');
        }
        $(this).prepend('<div id="'+playerId+'" style="display:none;"></div>');
        var player = new YT.Player(playerId, {
            height: videoHeight,
            width: videoWidth,
            host: 'https://www.youtube.com',
            videoId: videoId,
            playerVars: {rel: 0},
            events: {
                'onReady': setPayerReady,
            }
        });
        playerArray[playerId] = {player:player} ;
        playerIdx++;
    });
}
function setPayerReady(event) {
    var playerIFrameEl = event.target.getIframe();
    var playerWrapEl = $(playerIFrameEl).closest('.youtube-player');
    $(playerWrapEl).removeClass('loading').addClass('loaded');
    $(playerWrapEl).data('player-id', $(playerIFrameEl).attr('id'));
}

//Just in order to avoid loading unlimitedly, set try limit.
var tryCnt = 0;
var tryLimit = 10;
//Play YouTube Video
function playYTVideo(playerWrapEl) {
    if ($(playerWrapEl).hasClass('loaded')){
        var playerIFrameEl = $(playerWrapEl).find('iframe');
        var playerId = $(playerIFrameEl).attr('id');
        var playerData = playerArray[playerId];
        if (playerData !== undefined) {
            player = playerData['player'];
            if (player !== undefined) {
                player.playVideo();
                $(playerWrapEl).find('.place_holder').hide();
                $(playerWrapEl).find('iframe').show();
                tryCnt = 0;
            }
        }
    } else {
        if (tryCnt < tryLimit) {
            //If player is not ready, try it again after some time
            $(playerWrapEl).addClass('loading');
            setTimeout(function(){ 
                playYTVideo(playerWrapEl);
            }, 500);
            tryCnt++;
        }
    }
}

var ytJSTag;
$(document).ready(function(){
    // To reduce initial loading time, set Youtube players when one of place holder images is clicked.
    $(document).on('click', '.youtube-player', function (e) {
        if (ytJSTag === undefined) { //Load only once
            // This code loads the IFrame Player API code asynchronously.
            ytJSTag = document.createElement('script');

            ytJSTag.src = "https://www.youtube.com/iframe_api";
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(ytJSTag, firstScriptTag);
        }
        //Play video
        playYTVideo(this);
    });
});