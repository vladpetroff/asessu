/*
 * jQuery Growl plugin
 * Version 1.0.2 (8/1/2009)
 * @requires jQuery v1.3.2 or later
 *
 * Examples at: http://fragmentedcode.com/jquery-growl
 * Copyright (c) 2008-2009 David Higgins
 * 
 * Special thanks to Daniel Mota for inspiration:
 * http://icebeat.bitacoras.com/mootools/growl/
 */

/*
USAGE:

	$.growl(title, msg);
	$.growl(title, msg, image);
	$.growl(title, msg, image, priority);

THEME/SKIN:

You can override the default look and feel by updating these objects:
$.growl.settings.displayTimeout = 4000;
$.growl.settings.noticeTemplate = ''
  + '<div>'
  + '<div style="float: right; background-image: url(my.growlTheme/normalTop.png); position: relative; width: 259px; height: 16px; margin: 0pt;"></div>'
  + '<div style="float: right; background-image: url(my.growlTheme/normalBackground.png); position: relative; display: block; color: #ffffff; font-family: Arial; font-size: 12px; line-height: 14px; width: 259px; margin: 0pt;">' 
  + '  <img style="margin: 14px; margin-top: 0px; float: left;" src="%image%" />'
  + '  <h3 style="margin: 0pt; margin-left: 77px; padding-bottom: 10px; font-size: 13px;">%title%</h3>'
  + '  <p style="margin: 0pt 14px; margin-left: 77px; font-size: 12px;">%message%</p>'
  + '</div>'
  + '<div style="float: right; background-image: url(my.growlTheme/normalBottom.png); position: relative; width: 259px; height: 16px; margin-bottom: 10px;"></div>'
  + '</div>';
$.growl.settings.noticeCss = {
  position: 'relative'
};

To change the 'dock' look, and position: 

$.growl.settings.dockTemplate = '<div></div>';
$.growl.settings.dockCss = {
    position: 'absolute',
    top: '10px',
    right: '10px',
    width: '300px'
  };
  
The dockCss will allow you to 'dock' the notifications to a specific area
on the page, such as TopRight (the default) or TopLeft, perhaps even in a
smaller area with "overflow: scroll" enabled?
*/

(function($) {

$.growl = function(title,message,image,priority) { notify(title,message,image,priority); }
$.growl.version = "1.0.2";

function create(rebuild) {
	var instance = document.getElementById('growlDock');
	if(!instance || rebuild) {
	  instance = $(jQuery.growl.settings.dockTemplate).attr('id', 'growlDock').addClass('growl');
	  if(jQuery.growl.settings.defaultStylesheet) {
	    $('head').append('<link rel="stylesheet" type="text/css" href="' + jQuery.growl.settings.defaultStylesheet + '" />');
	  }
	  
	} else {
	  instance = $(instance);
	}
	$('body').append(instance.css(jQuery.growl.settings.dockCss));
	return instance;
};
  
function r(text, expr, val) {
	while(expr.test(text)) {
	text = text.replace(expr, val);
	}
	return text;
};
  
function notify(title,message,image,priority) {
	var instance = create();
	var html = jQuery.growl.settings.noticeTemplate;
	if(typeof(html) == 'object') html = $(html).html();
	html = r(html, /%message%/, (message?message:''));
	html = r(html, /%title%/, (title?title:''));
	html = r(html, /%image%/, (image?image:jQuery.growl.settings.defaultImage));
	html = r(html, /%priority%/, (priority?priority:'normal'));


		var notice = $(html)
			.hide()
			.css(jQuery.growl.settings.noticeCss);
	
	$.growl.settings.noticeDisplay(notice);
	instance.append(notice);
	$('a[rel="close"]', notice).click(function() {
		notice.remove();
	});
	if ($.growl.settings.displayTimeout > 0) {
		setTimeout(function(){
			jQuery.growl.settings.noticeRemove(notice, function(){
				notice.remove();
			});
		}, jQuery.growl.settings.displayTimeout);
	}
};

  
// default settings
$.growl.settings = {
	dockTemplate: '<div></div>',
	dockCss: {
		position: 'fixed',
		bottom: '10px',
		right: '10px',
		width: '300px',
		zIndex: 50000
	},
	noticeTemplate: 
		'<div class="notice">' +
		' <h3 style="margin-top: 15px"><a rel="close">%title%</a></h3>' +
		' <p>%message%</p>' +
		'</div>',
	noticeCss: {
		opacity: .75,
		backgroundColor: '#333333',
		color: '#ffffff'
	},
	noticeDisplay: function(notice) {
		if ($.browser.msie) {
			notice.show();
		}else{
		//какой нахуй прозрачность 0?? оно и не видно нигде кроме ие notice.css({'opacity':'0'}).fadeIn(jQuery.growl.settings.noticeFadeTimeout);
		notice.fadeIn(jQuery.growl.settings.noticeFadeTimeout);
		}
	},
	noticeRemove: function(notice, callback) {
		if ($.browser.msie) {
			notice.css({'opacity':'0'}).animate({height: '0px'}, {duration:jQuery.growl.settings.noticeFadeTimeout, complete: callback});			
		}
		else {
			notice.animate({opacity: '0'},function(){
				notice.animate({height: '0px'}, {duration:jQuery.growl.settings.noticeFadeTimeout, complete: callback});			
			})			
		}

	},
	noticeFadeTimeout: 'fast',
	displayTimeout: 3500,
	defaultImage: 'growl.jpg',
	defaultStylesheet: null,
	noticeElement: function(el) {
		$.growl.settings.noticeTemplate = $(el);
	}
};
})(jQuery);

var	msg = function(type, text){
			$.growl.settings.displayTimeout = 3000;
			$.growl.settings.dockCss = {
				position: 'fixed',
				bottom: '10px',
				right: '10px',
				width: '330px',
				zIndex: 50000
			};				
			$.growl.settings.noticeCss = {
			};				
			if (type == 'error'){
				$.growl.settings.noticeTemplate = ''
				  + '<div>'
				  + '<div class="noticeTop"><div class="noticeBottom"><div class="noticeCenter">'
				  + '<p class="error">%message%</p>'				
				  + '</div></div></div>'
				  + '<div>';				
			} else if (type == 'ok'){
				$.growl.settings.noticeTemplate = ''
				  + '<div>'
				  + '<div class="noticeTop"><div class="noticeBottom"><div class="noticeCenter">'
				  + '<p class="ok">%message%</p>'				
				  + '</div></div></div>'
				  + '<div>';				
			} else {
				$.growl.settings.noticeTemplate = ''
				  + '<div>'
				  + '<div class="noticeTop"><div class="noticeBottom"><div class="noticeCenter">'
				  + '<p class="info">%message%</p>'				
				  + '</div></div></div>'
				  + '<div>';				
			}
			$.growl(type, text);
}
