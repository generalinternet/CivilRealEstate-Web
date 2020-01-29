/*
socket.notify.js v3.0.0
*/
// request permission on page load
if(USE_HTTPS){
    document.addEventListener('DOMContentLoaded', function () {
      if (Notification.permission !== "granted")
        Notification.requestPermission();
    });
}

function notify(title, body, url) {
  if (Notification.permission !== 'granted')
    Notification.requestPermission();
  else {
        var options = {
            body: body,
            sticky: true,
            icon: "http://generalinternet.ca/css/images/bosLogo.png"
        }
        var notification = new Notification(title, options);
        setTimeout(notification.close.bind(notification), 7000);

        notification.onclick = function () {
            notification.close();
            window.open(url, '_blank');
            window.focus();
        };

    }
}

function reportSeen(modelId) {
     jQuery.post('index.php?controller=notification&action=markNotificationSeen&modelId=' + modelId + '&ajax=1', function (data) {
         //do nothing
     });
}

function notifyBounce(){
    $('.notify_count').animate({
        marginTop: '-0.5em'
    },function(){
        $('.notify_count').animate({
            marginTop: '0'
        },700,'easeOutBounce',function(){
            notifyBounce();
        });
    });
}

$(function(){
    socket.on('notified', function (data) {
        //Get notifications from BOS and add new ones to notification bar
        jQuery.post('index.php?controller=notification&action=getUnseenNotificationData&ajax=1', function (data) {
            //var parsedData = JSON.parse(data);
            var count = data.count;
            var totalCount = data.totalCount;
            var bData = data.b_data;
            for (i=0;i<count;i++) {
                var title = bData[i].title;
                var sbj = bData[i].sbj;
                var url = bData[i].url;
                notify(title, sbj, url);
                var modelId = bData[i].model_id;
                reportSeen(modelId);
            }
            $('.notify_count').show();
            $('.notify_count').html(totalCount);
            notifyBounce();      
            $('#notify_sound')[0].play();
        });
    });
    
    $('<audio id="notify_sound"><source src="resources/media/sounds/open-ended.ogg" type="audio/ogg"><source src="resources/media/sounds/open-ended.mp3" type="audio/mpeg"><source src="resources/media/sounds/open-ended.wav" type="audio/wav"></audio>').appendTo('body');
});
