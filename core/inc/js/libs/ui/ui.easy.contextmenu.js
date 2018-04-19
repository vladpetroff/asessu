(function($) {

$.widget("ui.contextmenu", {

	_init: function() {
		var self = this
		this.panel = $('<div class="context-menu"></div>').appendTo(document.body)
		this.panel.height($(window).height());
		this.panel.width($(window).width());
		if (typeof this.options.menu.length)
			this.makeMenu(this.options.menu);		
		
		$(window).resize(function(){
			self.destroy();
		});		
		this.panel.click(function(e){
			if ( 
				e.pageX < parseInt( self.cxmenu.css('left') ) || 
				e.pageX > (parseInt( self.cxmenu.css('left') )+self.cxmenu.width()) || 
				e.pageY < parseInt( self.cxmenu.css('top') ) || 
				e.pageY > (parseInt( self.cxmenu.css('top') )+self.cxmenu.height())
				)
				self.destroy();
		});
		this.panel.rightClick(function(){
			self.destroy();
		});	
	},
	
	makeMenu: function(menu){
		var self = this;
		var upload = 0;
		var id = 0;
		var html = '<ul>';
		for (var i=0; i < menu.length; i++) {
			var item = menu[i];
			if (item =="separator") {
				html+='<li class="separator"></li>';
			} else if(typeof item.sub != "undefined") {
				var subWidth = (typeof item.width != "undefined")?item.width:200;
				html+='<li class="sub"><a class="sub" href="#'+item.action+'">'+item.name+'<span class="more"></span></a><ul style="width:'+subWidth+'px">';
					for (var y=0; y < item.sub.length; y++) {
						var subitem = item.sub[y];
						if (subitem =="separator") {
							html+='<li class="separator"></li>';
						} else if(subitem.selected) {
							html+='<li><a class="selected" href="#'+item.action+'/'+subitem.action+'">'+subitem.name+'</a></li>';
						} else {
							html+='<li><a href="#'+item.action+'/'+subitem.action+'">'+subitem.name+'</a></li>';
						}
						
					};
				html+='</ul></li>';
			} else if(item.inactive) {
				html+='<li><span>'+item.name+'</span></li>';
			} else {
				if (typeof item.set != "undefined")
					if (item.set)
						html+='<li><a href="#'+item.action+'"><span class="set"></span>'+item.name+'</a></li>';
					else
						html+='<li><a href="#'+item.action+'">'+item.name+'</a></li>';
				else
					html+='<li><a href="#'+item.action+'">'+item.name+'</a></li>';		
				if (item.upload){
					upload = '#'+item.action;
					id = item.id;
				}		
			}
		}
		html+='</ul>';
		self.cxmenu = $(html).appendTo(this.panel);
		var css = {width:this.options.width};
		if ((this.options.left + this.options.width) > $(window).width()) {
			css.left = this.options.left - this.options.width;
		} else {
			css.left = this.options.left;
		}
		if ((this.options.top + self.cxmenu.height()) > $(window).height()) {
			css.top = this.options.top - (self.cxmenu.height()+10);
		} else {
			css.top = this.options.top;
		}
		self.cxmenu.css(css);
		$('li.sub',this.panel).hover(
		function(){
			$('ul',this).show().css({
				left:self.options.width-2,
				top:-6
			});
			if (($('ul',this).offset().top + $('ul',this).height()) > $(window).height()) {
				$('ul',this).css({
					top:$(window).height() - ($('ul',this).offset().top + $('ul',this).height()+20)
				});
			};
			if (($('ul',this).offset().left + $('ul',this).width()) > $(window).width()) {
				$('ul',this).css({
					left:1 - ($('ul',this).width()+2)
				});
			};
		},	
		function(){
			$('ul',this).hide();
		}
		);
		$('a',this.panel).click(function(){
			var action = this.href.split('#');
			self.element.trigger('contextAction',action[1]);
			self.destroy();
			return false;
		});
		$('span',this.panel).click(function(){
			return false;
		});
		if (upload != 0) {
			//[k] для мозиллы с мультизагрузкой
			/*if($.browser.mozilla && $.browser.version >= '1.9.2'){
				var multy = true;
				$('[href='+upload+']').unbind('click').css({overflow:"hidden",position:"relative"});
				$('<input type="file" id="inputField" multiple="true"/>')
					.appendTo($('[href='+upload+']'))
					.css({
						position:"absolute",
						top:0,
						right:0,
						fontSize:"100px",
						opacity:0
					});
				$('[href='+upload+']').attr('href',window.location.href);	
				var inputElement = document.getElementById("inputField");  
				function handleFiles() {  
					self.destroy();
					$('.table-view.images').parent().spinner();
					var files = this.files;	
					for (var i = 0; i < files.length; i++) {  
					    var file = files[i];  
					    //[k] АЛЯРМА ниже в строке если раскомментируешь блок мультизагрузки, не забудь склеить звезду и слеш!!	
					    var imageType = /image.* /;  
					    if (!file.type.match(imageType)) {  
					      continue;  
					    }
						var xhr = new XMLHttpRequest();     
						xhr.onreadystatechange = function (aEvt) {  
							if (xhr.readyState == 4) {  
							   if(xhr.status == 200) {
								if (multy) {
									$('.table-view.images').parent().spinner('destroy');
								    self.element.trigger('upload');
									multy = false;
								};
							
							} else  
							    log("Error loading page.");  
							}  
						};
						var url = window.location.href.split("#")[1];
						var id = url.split('/')[url.split('/').length - 1];
						xhr.open("POST", "admin-images/upload-bin/"+id+".html",true);  
						xhr.setRequestHeader("If-Modified-Since", "Mon, 26 Jul 1997 05:00:00 GMT");
										        xhr.setRequestHeader("Cache-Control", "no-cache");
										        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
										        xhr.setRequestHeader("X-File-Name", file.name);
										        xhr.setRequestHeader("X-File-Size", file.size);
										        xhr.setRequestHeader("Content-Type", "multipart/form-data");
						xhr.sendAsBinary(file.getAsBinary());
					}
				}	
				inputElement.addEventListener("change", handleFiles, false); 
			} 
			//[k] для браузеров без мультизагрузки
			else {*/
				$('[href='+upload+']').unbind('click').click(function(){return false;});
				var myUpload = $('[href='+upload+']').upload({
				        name: 'file',
				        action: '/admin-images/upload.html',
				        enctype: 'multipart/form-data',
				        params: {id:id},
				        autoSubmit: false,
				});
				myUpload.onSelect = function() {
					msg('process','Загрузка началась. Подождите..');
					myUpload.autoSubmit = false;
					var re = new RegExp("(jpg|jpeg|gif|png)$", "i"); 
					if (!re.test(myUpload.filename())) {
						var frame = $('<div><p class="alert">Загружать можно только следующие форматы изображений:<br/>JPEG, JPG, GIF, PNG</p></div>');
						frame.dialog({
							title: "Ошибка загрузки",
							width: 350,
							resizable: false,
							buttons: {
								'OK': function() {
									$(this).dialog('close');
								}
							},
							close: function() {frame.remove()},
							modal: true,
							overlay: {backgroundColor: '#000',opacity: 0.5}					
						});					
						self.destroy();
					} else {
						self.panel.hide()
						myUpload.submit();
					}
				}
				myUpload.onComplete = function(data){
					data = $.parseJSON(data);
					if (data.error) {
						var frame = $('<div><p class="alert">Ошибка при загрузке. Размер файла привышает лимит.</p></div>');
						frame.dialog({
							title: "Ошибка загрузки",
							width: 350,
							resizable: false,
							buttons: {
								'OK': function() {
									$(this).dialog('close');
								}
							},
							close: function() {frame.remove()},
							modal: true,
							overlay: {backgroundColor: '#000',opacity: 0.5}					
						});					
						self.destroy();					
					} else {
						msg('ok','Загрузка завершена.');				
						var html = '<div class="page"><img alt="'+data.object.name+'" src="'+data.object.prev+'"/></div>';
						html+= '<h1>'+data.object.name+'</h1>';
						html+= '<a class="lightbox id-'+data.object.id+'" style="display:none" title="'+data.object.name+'" href="'+data.object.big+'">Увеличить</a>';
						data.html = html;
						self.element.trigger('upload',data);
						self.destroy();					
					}
				/*}*/
			}
			
		} else {
			//Do nothing  :)
		}
	},
	
	destroy: function() {
		this.panel.remove();
		$.widget.prototype.destroy.apply(this, arguments);
	}
});

$.extend($.ui.contextmenu, {
	version: "2.0.0",
	defaults: {
		top:0,
		left:0,
		width:200,
		menu:[]
	}
});

})(jQuery);
