/*
socket.js v4.0.0
*/

if (USE_HTTPS) {
    var socketServerURL = 'socket2me.businessos.ca:8000';
    var socket = io.connect('https://'+socketServerURL, { 
    'reconnect': true,
    'reconnectionDelay': 1000,
    'reconnectionAttempts': 10
});
} else {
    var socketServerURL = 'socket2me.businessos.ca:8001';
    var socket = io.connect('//'+socketServerURL, {
    'reconnect': true,
    'reconnectionDelay': 1000,
    'reconnectionAttempts': 10
});
}

socket.on('socket_id', function (data) {
    var socketId = encodeURIComponent(data.socket_id.trim());
    jQuery.post('index.php?controller=notification&action=setSocketId&socketId=' + socketId + '&ajax=1', function (data) {
        //connected
    });
});

socket.on('qb_connected', function(data){
    if(qbConnectWindow){
        qbConnectWindow.close();
    }
    jQuery.post('index.php?controller=accounting&action=getQBButton&ajax=1', function (data) {
        if(data.qbBtn != undefined){
            var newBtn = $(data.qbBtn);
            $('.qb_connect_btn').replaceWith(newBtn);
            $('.qb_bar').not('.connected').addClass('connected');
            $('.qb_related_content').not('.connected').addClass('connected');
        }
    });
    
    jQuery.post('index.php?controller=accounting&action=updateQBCustomerBalances&ajax=1', function (data) {
        if (data.result != undefined) {
            
        }
    });
});
