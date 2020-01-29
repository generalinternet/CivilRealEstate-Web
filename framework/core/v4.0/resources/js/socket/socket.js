/*
socket.js v4.0.0
*/
let socketServerURL = '';
if(DEV_MODE){
    socketServerURL = '//' + LOCALHOST_IP + ':8001';
} else if (USE_HTTPS) {
    socketServerURL = 'https://socket2me.businessos.ca:8000';
} else {
    socketServerURL = '//socket2me.businessos.ca:8001';
}

let socketUsers = [];
//let socketUserIds = {};
let mySocketId = null;
let mySocketUserId = null;
let socket;

function getSocketUser(socketUserId){
    let socketUser = socketUsers[socketUserId];
    if(socketUser !== undefined && socketUser !== null){
        return socketUser;
    }
    socketUser = new SocketUser(socketUserId);
    return socketUser;
}

function getSocketNickname(socketUserId){
    let socketUser = getSocketUser(socketUserId);
    if(socketUser !== undefined && socketUser !== null){
        return socketUser.getNickname();
    }
    return socketUserId;
}

class GIDIUPService{
    
    static errMissingCMD(data){
        let cmd = data.cmd;
        console.log('Unknown command code : ' + cmd);
    }
    
    static emit(data, endpoint){
        if(endpoint == undefined){
            endpoint = 'gidiup';
        }
        socket.emit(endpoint, data);
    }
    
    static cmdForceLogout(data){
        window.location.href = 'logout';
    }
    
    static cmdAlert(data){
        let alertData = {};
        if(data.alertMsg != undefined){
            alertData.msg = data.alertMsg;
        }
        if(data.alertColour != undefined){
            alertData.colour = data.alertColour;
        }
        if(data.alertCode != undefined){
            alertData.code = data.alertCode;
        }
        addPageAlert(alertData);
    }
    
    static cmdShareFormData(data){
        let formId = data.formId;
        let fieldName = data.fieldName;
        let fieldVal = data.fieldVal;
        let fieldType = data.fieldType;
        let field = null;
        
        switch(fieldType){
            case 'radio':
            case 'checkbox':
                let fields = $('#' + formId).find('input[name="' + fieldName + '"]');
                for(let f=0; f<fields.length; f++){
                    let field = fields.eq(f);
                    let val = field.val();
                    let prop = false;
                    if(jQuery.inArray(val, fieldVal) !== -1){
                        prop = true;
                    }
                    field.data('gidiup-active', true);
                    field.prop('checked',prop).trigger('change');
                    field.data('gidiup-active', false);
                }
                break;
            case 'select':
                field = $('#' + formId).find('select[name="' + fieldName + '"]');
                field.data('gidiup-active', true);
                field.val(fieldVal).trigger('change').selectric('refresh');
                field.data('gidiup-active', false);
                break;
            case 'textarea':
                field = $('#' + formId).find('textarea[name="' + fieldName + '"]');
                field.data('gidiup-active', true);
                field.val(fieldVal).trigger('change');
                field.data('gidiup-active', false);
                break;
            case 'autocomp_search':
                //@todo maybe search?
                break;
            case 'autocomp':
                field = $('#' + formId).find('input[name="' + fieldName + '"]');
                let acField = field.closest('.form_element').find('.gi_field_autocomplete');
                acField.trigger('autocompleteFill',{value : fieldVal});
                break;
            default:
                field = $('#' + formId).find('input[name="' + fieldName + '"]');
                field.data('gidiup-active', true);
                field.val(fieldVal).trigger('change');
                field.data('gidiup-active', false);
                break;
        }
    }
    
    static cmdShareFormFocus(data){
        //@todo this is temporariy as proof of concept, will need to get specific as to WHO is focused on the field
        $('.form_element.other_focus').removeClass('other_focus');
        let blur = data.blur;
        if(blur != undefined && blur){
            return;
        }
        let formId = data.formId;
        let fieldName = data.fieldName;
        let fieldType = data.fieldType;
        let field = null;
        
        switch(fieldType){
            case 'radio':
            case 'checkbox':
                let fields = $('#' + formId).find('input[name="' + fieldName + '"]');
                break;
            case 'select':
                field = $('#' + formId).find('select[name="' + fieldName + '"]').eq(0);
                break;
            case 'textarea':
                field = $('#' + formId).find('textarea[name="' + fieldName + '"]');
                break;
            default:
                field = $('#' + formId).find('input[name="' + fieldName + '"]');
                break;
        }
        
        let formElm = field.closest('.form_element');
        formElm.addClass('other_focus');
    }
    
}

class SocketUser {
    constructor(socketUserId, socketData){
        this.socketUserId = socketUserId;
        this.appRef = null;
        this.nickname = null;
        this.userId = null;
        socketUsers[socketUserId] = this;
    }
    setSocketData(socketData){
        let socketUser = this;
        Object.keys(socketData).forEach(function(key, i) {
            if(key === 'nickname'){
                socketUser.setNickname(socketData[key]);
            } else {
                socketUser[key] = socketData[key];
            }
            $('.sync_socket_user_data[data-socket-user-id="' + socketUser.socketUserId + '"][data-property="' + key + '"]').html(socketData[key]);
        });
    }
    setNickname(nickname){
        this.nickname = nickname;
        $('.update_socket_nickname[data-socket-user-id="' + this.socketUserId + '"]').html(nickname);
        $('.get_socket_nickname[data-socket-user-id="' + this.socketUserId + '"]').html(nickname).removeClass('get_socket_nickname').addClass('update_socket_nickname');
        this.updateSocketConvoTitle();
    }
    getNickname(){
        return this.nickname;
    }
    updateStyle(){
        if(this.socketUserId !== mySocketUserId){
            return;
        }
        let styleSheet = $('style#socket_user_' + this.socketUserId);
        if(!styleSheet.length){
            styleSheet = $('<style type="text/css" id="#socket_user_' + this.socketUserId + '">');
            styleSheet.appendTo('head');
        }
        let newStyle = '';
        let colour = this.colour;
        if(colour !== undefined && colour !== null && colour !== ''){
            newStyle += '.gi_chat_user_list li.me{background:#' + colour + '}';
            newStyle += '.gi_chat_msg_list li.me .bubble{background:#' + colour + '}';
            newStyle += '.gi_chat_msg_list li.me .bubble:after {border-left-color:#' + colour + '}';
            newStyle += '.gi_chat_box li.me .bubble .file_thumb .corner:before{background: #' + colour + '}';
        }
        styleSheet.html(newStyle);
    }
    updateSocketConvoTitle(){
        let convoId = this.getSocketConvoId();
        if(convoId !== null){
            let socketConvo = getSocketConvo(convoId);
            if(socketConvo){
                socketConvo.setTitle(this.nickname);
            }
        }
    }
    getSocketConvoId(){
        if(this.socketConvoId !== undefined && this.socketConvoId !== null){
            return this.socketConvoId;
        }
        return this.socketUserId;
    }
}

function clearChatCookies(){
//    setCookie('myChatInfo', '', 1);
    setCookie('myChatInfoStep', '', 1);
    setCookie('chatOpen', '', 1);
    setCookie('openChatConvoIds', '', 1);
    setCookie('chatDoNotDisturb', '', 1);
}

$(function(){
    let queryString = SOCKET_QUERY_STRING;
    mySocketUserId = getCookie('socketUserId');
    if(mySocketUserId != undefined && mySocketUserId != null && mySocketUserId != ''){
        queryString += '&socketUserId=' + mySocketUserId;
    }
    socket = io.connect(socketServerURL, {
        'reconnect': true,
        'reconnectionDelay': 1000,
        'reconnectionAttempts': 10,
        'query' : queryString
    });
    
    socket.on('socket_id', function (data) {
        mySocketId = encodeURIComponent(data.socketId.trim());
        if(data.socketUserId != undefined){
            mySocketUserId = data.socketUserId;
        }
        setCookie('socketUserId', mySocketUserId, 30);
        //use a cookie to store if the user is "logged in"
        let socketLoggedInRaw = getCookie('socketLoggedIn');
        let socketLoggedIn = 0;
        if(socketLoggedInRaw != undefined && socketLoggedInRaw != ''){
            socketLoggedIn = JSON.parse(socketLoggedInRaw);
        }
        jQuery.post('index.php?controller=notification&action=setSocketId&socketId=' + mySocketId + '&socketUserId=' + mySocketUserId + '&ajax=1&socketLoggedIn=' + socketLoggedIn, function (data) {
            //connected
            let emitData = {};
            if(data.userId != undefined){
                if(!socketLoggedIn){
                    clearChatCookies();
                }
                emitData.userId = data.userId;
                socket.emit('join', emitData);
                setCookie('socketLoggedIn', 1, 30);
            } else {
                if(socketLoggedIn){
                    clearChatCookies();
                }
                socket.emit('join', emitData);
                setCookie('socketLoggedIn', 0, 30);
            }
            
            socket.emit('socket_ready', true);
        });
    });

    socket.on('qb_connected', function(data){
        if(qbConnectWindow){
            qbConnectWindow.close();
        }
        jQuery.post('index.php?controller=accounting&action=getQBButton&ajax=1', function (data) {
            if(data.qbBtn != undefined){
                let newBtn = $(data.qbBtn);
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
    
    socket.on('gidiup', function(data){
        let cmd = data.cmd;
        if(cmd == undefined || cmd == 0){
            console.log(data);
        } else {
            let method = 'cmd' + cmd;
            if (typeof GIDIUPService[method] === 'function'){
                GIDIUPService[method](data);
            } else {
                GIDIUPService.errMissingCMD(data);
            }
        }
    });
    
    let chatDoNotDisturbRaw = getCookie('chatDoNotDisturb');
    let chatDoNotDisturb = 0;
    if(chatDoNotDisturbRaw !== undefined && chatDoNotDisturbRaw !== ''){
        chatDoNotDisturb = JSON.parse(chatDoNotDisturbRaw);
    }
    if(chatDoNotDisturb){
        setDoNotDisturbOn();
    } else {
        setDoNotDisturbOff();
    }
});

let shareInputTimeout = 3000;

function shareFormData(field){
    let active = field.data('gidiup-active');
    if(active != undefined && active){
        return;
    }
    let formId = field.closest('form').attr('id');
    let fieldName = field.attr('name');
    let fieldType = getShareFieldType(field);
    if(fieldType=='password'){
        //do not send password
        return;
    }
    let fieldVal = field.val();
    switch (fieldType) {
        case 'radio':
        case 'checkbox':
            fieldVal = $('#' + formId).find('input[name="' + fieldName + '"]:checked').map(function(){
                return $(this).val();
            }).get();
            break;
    }
    GIDIUPService.emit({
        cmd : 'ShareFormData',
        formId : formId,
        fieldName : fieldName,
        fieldType : fieldType,
        fieldVal : fieldVal,
        sendToMe : false
    });
}

function shareFormFocus(field){
    let formId = field.closest('form').attr('id');
    let fieldName = field.attr('name');
    let fieldType = getShareFieldType(field);
    let blurStatus = false;
    if(!field.is(':focus')){
        blurStatus = true;
    }
    if(fieldType=='select'){
        blurStatus = true;
        let selectricWrap = field.closest('.selectric-wrapper');
        if(selectricWrap.is('.selectric-focus')){
            blurStatus = false;
        }
    }
    GIDIUPService.emit({
        cmd : 'ShareFormFocus',
        formId : formId,
        fieldName : fieldName,
        fieldType : fieldType,
        blur : blurStatus,
        sendToMe : false
    });
}

function getShareFieldType(field){
    let fieldType = field.attr('type');
    if(field.is('textarea')){
        fieldType = 'textarea';
    }
    if(field.is('select')){
        fieldType = 'select';
    }
    if(field.is('.gi_field_autocomplete')){
        fieldType = 'autocomp_search';
    }
    if(field.is('.autocomp')){
        fieldType = 'autocomp';
    }
    return fieldType;
}

function setDoNotDisturbOn(){
    setCookie('chatDoNotDisturb', true, 30);
    $('#gi_chat_bar').addClass('do_not_disturb_mode');
    socket.emit('do_not_disturb', {
        doNotDisturb : 1
    });
}

function setDoNotDisturbOff(){
    setCookie('chatDoNotDisturb', false, 30);
    $('#gi_chat_bar').removeClass('do_not_disturb_mode');
    socket.emit('do_not_disturb', {
        doNotDisturb : 0
    });
}

$(document).on('keyup', 'form.gidiup input, form.gidiup textarea', function(e){
    let field = $(this);
    let active = field.data('gidiup-active');
    if(active != undefined && active){
        return;
    }
    field.data('gidiup-timeout', setTimeout(function(){
        shareFormData(field);
    },shareInputTimeout));
});

$(document).on('change', 'form.gidiup input, form.gidiup textarea, form.gidiup select', function(e){
    let field = $(this);
    let active = field.data('gidiup-active');
    if(active != undefined && active){
        return;
    }
    let timeout = field.data('gidiup-timeout');
    clearTimeout(timeout);
    shareFormData(field);
});

$(document).on('autocompleteSelected autocompleteRemoved autocompleteRemFull', 'form.gidiup .gi_field_autocomplete', function(e, ui){
    let formElement = $(this).closest('.form_element');
    let field = formElement.find('.autocomp');
    shareFormData(field);
});

$(document).on('focus blur', 'form.gidiup input, form.gidiup textarea', function(e){
    let field = $(this);
    shareFormFocus(field);
});

$(document).on('selectric-open selectric-close', 'form.gidiup select', function(e){
    let field = $(this);
    shareFormFocus(field);
});

$(document).on('click', '.do_not_disturb', function(e){
    e.preventDefault();
    if($('#gi_chat_bar').is('.do_not_disturb_mode')){
        setDoNotDisturbOff();
    } else {
        setDoNotDisturbOn();
    }
});

$(document).on('bindActionsToNewContent',function(){
    $('.sync_socket_user_data').each(function(){
        let socketUserId = $(this).data('socket-user-id');
        let socketUser = getSocketUser(socketUserId);
        let socketProperty = $(this).data('property');
        $(this).html(socketUser[socketProperty]);
    });
});
