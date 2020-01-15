/* Video Dynamic */


//this is for replacing the video with an image and only load on click
    $('.video-promo .fa-cog').hide();
    $('.video-btn').click( function() {
            var filepath = 'https://www.youtube.com/embed/nkxea_Og_0Q?autoplay=1';//youtube embed code with autoplay
            $('#dynamic-video').attr('src', filepath);//add the src to the iframe with this id
            $('.video-promo .video-btn').hide(); //hide the play button
            $('.video-promo img').hide();//hide the video thumb
            $('.video-promo .fa-spin').hide(); //hide the spinning element?
            $('.video-promo h3').hide(); //hide the title
            $('.video-promo .fa-cog').show(); //show the cog (supposed to be the spinning element)
            $('.html5-main-video').play(); //attempting to force the html 5 youtube video to play
    });

