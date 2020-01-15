var resourceDir = 'resources/external/js/boxy/';
var boxyScriptList = 'classic';
var boxyName = 'Boxy';
var boxySysName = 'BOS';
var boxyScript = null;
var boxySaid = [];
function boxyFloat() {
    $('#boxy').animate({
        'top' : '-2%'
    }, 600,function(){
        $('#boxy').animate({
            'top' : '0%'
        }, 600);
    });
    $('#boxy_shadow').animate({
        'top' : '-1%',
        'right' : '-1%'
    }, 600,function(){
        $('#boxy_shadow').animate({
            'top' : '0%',
            'right' : '0%'
        }, 600,function(){
            boxyFloat();
        });
    });
}
function boxyEyes() {
    setTimeout(function(){
        var eyeType = Math.floor(Math.random() * 4) + 1;
        var waitTime = Math.floor(Math.random() * 2) + 1;
        switch (eyeType){
            case 1:
                $('#boxy_eyes').addClass('surprised');
                setTimeout(function(){
                    $('#boxy_eyes').removeClass('surprised');
                    boxyEyes();
                },waitTime+'000');            
                break;
            case 2:
                $('#boxy_eyes').addClass('left');
                setTimeout(function(){
                    $('#boxy_eyes').removeClass('left');
                    boxyEyes();
                },waitTime+'000'); 
                break;
            case 3:
                $('#boxy_eyes').addClass('right');
                setTimeout(function(){
                    $('#boxy_eyes').removeClass('right');
                    boxyEyes();
                },waitTime+'000'); 
                break;
            case 4:
                $('#boxy_eyes').addClass('closed');
                setTimeout(function(){
                    $('#boxy_eyes').removeClass('closed');
                    boxyEyes();
                },100); 
                break;
        }
    },1000);    
}
$(document).on('click','.close_boxy_tip, .thank_boxy',function(){
    $('#boxy_tip_box').slideUp('fast');
});
function newBoxyTip(tip, playAudio) {
    $('#boxy_tip').html(tip);
    $('#boxy_tip_box').slideDown('fast');
    if (playAudio && $('#boxy').is(':visible')) {
        $('#boxy_tick')[0].play();
    }
}
$(document).on('click','.close_boxy',function(){
    var name = $('#boxy').data('name');
    newBoxyTip('<p>Are you sure you want to get rid of me, ' + name + '?</p><p class="centered"><span class="boxy_btn kill_boxy">Yes</span> <span class="boxy_btn keep_boxy">No</span></p>', true);
});
$(document).on('click','.kill_boxy',function(){
    newBoxyTip('<p>Okay, bye bye :(</p>');
    setBoxyCookie('hide_boxy',1,1);
    setTimeout(function(){
        $('#boxy_tip_box').slideUp('fast',function(){
            $('#boxy_woosh')[0].play();
            $('#boxy_wrap').slideUp('fast',function(){
                $('#bring_back_boxy').show();
            });
        });
    },1000);
});
$(document).on('click','#bring_back_boxy',function(){
    setBoxyCookie('hide_boxy',0,1);
    $('#bring_back_boxy').hide();
    $('#boxy_blop')[0].play();
    $('#boxy_wrap').slideDown('fast',function(){
        newBoxyTip('<p>Hi there again! Thanks for bringing me back.</p>');
    });
});
$(document).on('click','.keep_boxy',function(){
    var thanksType = Math.floor(Math.random() * 4) + 1;
    var name = $('#boxy').data('name');
    switch (thanksType){
        case 1:
            var thanks = 'Oh good! Thanks for keeping ' + name + ' around.';
            break;
        case 2:
            var thanks = ':D';
            break;
        case 3:
            var thanks = 'That\'s swell, I appreciate you not getting rid of me!';
            break;
        case 4:
            var thanks = 'Great :) I\'m glad you\'re letting me stick around a bit longer.';
            break;
    }
    newBoxyTip('<p>'+thanks+'</p>');
});

$(document).on('click','#toggle_fullscreen',function(){
    var msg = '';
    if(!$(this).find('.maximize').length){
        msg = '<p>Woah, we’ve gone fullscreen!</p><p>If you ever feel like you need a bit more space, hit the expand button to go fullscreen.</p><p class="centered"><span class="boxy_btn thank_boxy">Thanks</span></p>';
    } else {
        msg = '<p>We’ve gone small again.</p><p>Guess you didn\'t need all that extra space after all.</p><p class="centered"><span class="boxy_btn thank_boxy">Thanks</span></p>';
    }
    newBoxyTip(msg, true);
});

$(document).on('click','.boxy_april_fools',function(){
    newBoxyTip('<p>Also... April Fools!</p><p>If you want to get rid of me, just click the "&times;" to the right of me.</p><ul><li>Click on me!</li><li>Drag me around!</li></ul><p class="centered"><span class="boxy_btn thank_boxy">Thanks</span></p>');
});

String.prototype.replaceAll = function(obj) {
    var retStr = this;
    for (var x in obj) {
        retStr = retStr.replace(new RegExp(x, 'g'), obj[x]);
    }
    return retStr;
};

function replaceBoxySaysPlaceholders(boxySays){
    var firstName = $('body').data('user-first-name');
    var lastName = $('body').data('user-last-name');
    var dow = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"][(new Date()).getDay()];
    
    boxyReallySays = boxySays.replaceAll({
        '#boxy_name#' : $('#boxy').data('name'),
        '#first_name#' : firstName,
        '#last_name#' : lastName,
        '#dow#' : dow
    });
    return boxyReallySays;
}

$(document).on('click','#boxy',function(){
    var boxySays = '<p>I don’t know what to say.</p>';
    if(boxyScript != null && boxyScript != undefined){
        var scriptIndex = Math.floor(Math.random() * boxyScript.length);
        var boxySaysRand = boxyScript[scriptIndex];
        boxyScript.splice(scriptIndex,1);
        if(boxyScript == undefined || boxyScript.length == 0){
            boxyScript = boxySaid;
            boxySaid = [];
        }
        boxySaid.push(boxySaysRand);
        boxySays = replaceBoxySaysPlaceholders(boxySaysRand);
    }
    var boxyText = boxySays + '<p class="centered"><span class="boxy_btn boxy_help">Need Help?</span></p>';
    newBoxyTip(boxyText, true);
});
$(document).on('click','.boxy_help', function(){
    newBoxyTip('<p>April Fools!</p><p>If you want to get rid of me, just click the "&times;" on my right side.</p><ul><li>Click on me <ul><li><i>I say stuff</i></li></ul></li><li>Drag me around <ul><li><i>So I\'m out of your way</i></li></ul></li></ul><p class="centered"><span class="boxy_btn thank_boxy">Thanks</span></p>', true);
});
function setBoxyCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
} 
function getBoxyCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}
function makeBoxy(name){
    if(name == undefined || name == ''){
        name = 'Boxy';
    }
    $('body').append('<div id="boxy_wrap" class="hide_on_load"><span class="close_boxy" title="Close ' + name + '"></span><div id="boxy_tip_box"><span class="close_boxy_tip" title="Close Tip"></span><div id="boxy_tip_arrow"></div><div id="boxy_tip"><p>Hey! I\'m ' + name + ', your ' + boxySysName + ' assistant.</p><p>As you navigate your way around the ' + boxySysName + ', I\'ll try to help you in any way I can.</p><p class="centered"><span class="boxy_btn boxy_april_fools">Next &raquo;</span></p></div></div><div id="boxy" data-name="' + name + '"><div id="boxy_eyes"></div></div><div id="boxy_shadow"></div><div id="boxy_bg"></div></div>');
    $('#boxy_wrap').css({
        left: getBoxyCookie('boxy_left')+'px',
        top: getBoxyCookie('boxy_top')+'px'
    });
    $('body').append('<div id="bring_back_boxy" title="Bring Back ' + name + '!"></div>');
    $( "#boxy_wrap" ).draggable({
        stop: function(e, ui){
            setBoxyCookie('boxy_left',ui.position.left,1);
            setBoxyCookie('boxy_top',ui.position.top,1);
        },
        containment: 'parent'
    });
    boxyFloat();
    boxyEyes();
    var hideBoxy = getBoxyCookie('hide_boxy');
    if(hideBoxy == undefined || hideBoxy == 0){
        $('#boxy_wrap').slideDown('fast');
        $('#bring_back_boxy').hide();
        $('#boxy_blop')[0].play();
    }
}
$(window).bind('load', function() {
    $('<audio id="boxy_woosh"><source src="' + resourceDir + 'sounds/boxy_woosh.ogg" type="audio/ogg"><source src="' + resourceDir + 'sounds/boxy_woosh.mp3" type="audio/mpeg"><source src="' + resourceDir + 'sounds/boxy_woosh.wav" type="audio/wav"></audio>').appendTo('body');
    $('<audio id="boxy_tick"><source src="' + resourceDir + 'sounds/boxy_tick.ogg" type="audio/ogg"><source src="' + resourceDir + 'sounds/boxy_tick.mp3" type="audio/mpeg"><source src="' + resourceDir + 'sounds/boxy_tick.wav" type="audio/wav"></audio>').appendTo('body');
    $('<audio id="boxy_blop"><source src="' + resourceDir + 'sounds/boxy_blop.ogg" type="audio/ogg"><source src="' + resourceDir + 'sounds/boxy_blop.mp3" type="audio/mpeg"><source src="' + resourceDir + 'sounds/boxy_blop.wav" type="audio/wav"></audio>').appendTo('body');
    $.getJSON(resourceDir + '/script/' + boxyScriptList + '.json', function(json) {
        boxyScript = json.script;
    });
    makeBoxy(boxyName);
});