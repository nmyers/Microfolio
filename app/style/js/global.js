/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var myLayout; // a var is required because this page utilizes: myLayout.allowOverflow() method

$(document).ready(function () {

    myLayout = $('body').layout({
        west__size:			500,
        west__spacing_closed:		20,
        west__togglerLength_closed:	100,
        west__togglerAlign_closed:	"top",
        west__togglerContent_closed:    "M<BR>E<BR>N<BR>U",
        west__togglerTip_closed:	"Open & Pin Menu",
        west__sliderTip:		"Slide Open Menu",
        west__slideTrigger_open:	"mouseover"
    });

});

/**
 *
 * Format
 *
 * 0#Error message
 * 1#Success message
 * 2#Information message
 * 3#Loading message
 */
var MESSAGE_ERROR   = 0;
var MESSAGE_SUCCESS = 1;
var MESSAGE_INFO    = 2;
var MESSAGE_LOADING = 3;

function showMessage(message,style,delay) {

    delay = typeof(delay) != 'number' ? 3000 : delay;
    style = typeof(style) != 'number' ? 2 : style;

    if (message.charAt(1)=='#') {
        style = message.charAt(0);
        message = message.substr(2);
    }
    if (style==3) delay = 10000;

    style = 'style-'+style;
    
    $('#message').remove();
    $('.ui-layout-west').prepend('<div id="message" class="'+style+'" ><div class="in" >'+message+'</div></div>');
    $('#message').show().delay(delay).fadeOut(500,function(){$('#message').remove();});
}

function updateIframe(src) {
    $('#mainFrame').attr('src',src);
}

function makeUrl(uri) {
    return base_url+base_index+uri;
}

$.fn.outer = function(val){
    if(val){
        $(val).insertBefore(this);
        $(this).remove();
    }
    else{return $("<div>").append($(this).clone()).html();}
}