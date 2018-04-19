(function($) {

$.widget("ui.adminnews", {

	_init: function() {
		var self = this;
		this.workView = this.element;
		this.workView.navigation({addarr:false});
		this.workView.navigation('option','buttonMenu',[
			{name:"Добавить новость",action:"createNew",inactive:false}
		]);

		this.workView.tableView();
		this.workView.tableView('option','addClass','orders');	

		// Обрабатываем нажатия навигационного меню
		self.workView.bind('navigationClick',function(e,link){
			window.location.href = '#'+link.split('#')[1];
			$('.nav-panel a').removeClass('cur');
			getPage();
		});

				
		//Генерация страницы и навигации
		function getPage(){
			//Очищаем навигацию
			self.page = ((window.location.href).split('#')[1].split('/')[1])?(window.location.href).split('#')[1].split('/')[1]: 1;

			//Заполняем навигацию
			$.post('/admin-news/getpages/',function(data){
				self.workView.navigation('clear');
				for (var i=1; i <= data.count; i++) {
					if (i == self.page) {
						self.workView.navigation('option','push',{name:i,link:i,cur:true});
					} else {
						self.workView.navigation('option','push',{name:i,link:i,cur:false});
					}
				};			
			},'json');

			//[k] очищаем рабочее поле	

			self._postJSON('/admin-news/getpage/'+self.page,function(data){
				self.workView.tableView('clear');
				for (var i=0; i < data.object.length; i++) {
					self.workView.tableView('option','newRow',{
						html:data.html[i],
						info:{
							id:data.object[i].id,
							name:data.object[i].name,
							type:data.object[i].type				
						},
						menu:data.menu[i], //Меню
						width:150 //Ширина меню
					});
				}
				self.workView.spinner('destroy');
			});
		}

		//Запускаем генерацию навигации
		getPage();


		self.workView.bind('navContextAction',function(e,action){
			switch(action.split('/')[0]){
				case "createNew":
					self._postJSON('/admin-news/create_new.html',{id:0},function(data){
						//Добавляем элемент
						self.workView.tableView('option','newRow',{
							html:data.html, //Содержание строки
							info:{ //Доп. информация, которая вернется нам в прерываниех
								id:data.object.id,
								name:data.object.header,
								type:data.object.type					
							},
							menu:data.menu, //Меню
							width:150 //Ширина меню
						});						
					});					
				break;
				case "createAct":
					self._postJSON('/admin-news/create_act.html',{id:0},function(data){
						//Добавляем элемент
						self.workView.tableView('option','newRow',{
							html:data.html, //Содержание строки
							info:{ //Доп. информация, которая вернется нам в прерываниех
								id:data.object.id,
								name:data.object.header,
								type:data.object.type					
							},
							menu:data.menu, //Меню
							width:150 //Ширина меню
						});					
					});					
				break;
			}
		});

		//Обрабатываем контекстное меню
		self.workView.bind('tableContextAction',function(e,obj){
			var action = obj.action.split('/')[0];
			switch (action){
				case "open":				
				self._postJSON('/admin-news/get_text.html',{id:obj.id},function(data){
					var html = $('<textarea id="text">'+data.html+'</textarea>');
					self.frame = $('<div></div>');
					self.editWindowPrefs.title = "Редактирование новости";					
					self.editWindowPrefs.buttons = {
						'Сохранить': function() {
							self._postJSON('/admin-news/update-text.html',{id:obj.id, text:CKEDITOR.instances.text.getData()},function(data){
								var html = $('<div>'+data+'</div>');
								var html = $('<textarea style="width:444px; height:60px" id="text"></textarea>');
								html.appendTo(self.frame);
							});
							if (CKEDITOR.instances.text) CKEDITOR.remove(CKEDITOR.instances.text);
							self.frame.remove();
							$(this).dialog('close');
						},
						'Отмена': function() {
							if (CKEDITOR.instances.text) CKEDITOR.remove(CKEDITOR.instances.text); self.frame.remove(); $(this).dialog('close');
						}
					};
					self.editWindowPrefs.close = function() {if (CKEDITOR.instances.text) CKEDITOR.remove(CKEDITOR.instances.text); self.frame.remove()}
					self.frame.dialog(self.editWindowPrefs);
					html.appendTo(self.frame);
					// $('[name=date]').datepicker({ dateFormat: 'yy-mm-dd', showButtonPanel: true });
					if (!CKEDITOR.instances.edithtml) CKEDITOR.replace('text', {toolbar : 'Editbar',
							filebrowserBrowseUrl : '/core/inc/ckfinder/ckfinder.html',
							filebrowserImageBrowseUrl : '/core/inc/ckfinder/ckfinder.html?Type=Images',
							filebrowserFlashBrowseUrl : '/core/inc/ckfinder/ckfinder.html?Type=Flash',
							filebrowserUploadUrl : '/core/inc/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
							filebrowserImageUploadUrl : '/core/inc/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
							filebrowserFlashUploadUrl : '/core/inc/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash'
						});						
				});
				break;
				case "edit_short":
				self._postJSON('/admin-news/get_short.html',{id:obj.id},function(data){
					var html = $('<textarea style="width:444px; height:60px" id="text">'+data.html+'</textarea>');
					self.frame = $('<div></div>');
					self.editShortWindowPrefs.title = "Редактирование анонса";					
					self.editShortWindowPrefs.buttons = {
						'Сохранить': function() {
							self._postJSON('/admin-news/update.html',{id:obj.id, title:$('#text').val()},function(data){
								obj.html = data.html;
								self.workView.tableView('option','editRowHTML',obj);
							});
							self.frame.remove();
							$(this).dialog('close');
						},
						'Отмена': function() {
							self.frame.remove(); $(this).dialog('close');
						}
					};
					self.editShortWindowPrefs.close = function() {self.frame.remove()}
					self.frame.dialog(self.editShortWindowPrefs);
					html.appendTo(self.frame);
				});
				break;				
				case "remove":
						var frame = $('<div><p class="alert">Вы действительно хотите удалить новость?</p></div>');
						self.alertPrefs.title = "Удаление",
						self.alertPrefs.buttons = {
							'Удалить': function() {
								self._postJSON('/admin-news/remove.html',{id:obj.id},function(){ 
									self.workView.tableView('option','removeRow',obj.index);
								});
								$(this).dialog('close');
							},
							'Отмена': function() {
								$(this).dialog('close');
							}
						};
						self.alertPrefs.close = function() {frame.remove()}
						frame.dialog(self.alertPrefs);					
				break;
				case "rename":
					var h1 = $('h1',$('li',self.workView).eq(obj.index));
					var placeholder = h1.html();
					h1.parent().enableSelection();
					h1.hide();
					$('<input type="text" value="'+placeholder+'" />').insertAfter(h1).focus().select()
						.keypress(function(e){
							if (e.keyCode == 27){
								$(this).blur();
							}
							if (e.keyCode == 13){
								var str = $(this).val();
								var input = $(this);
								//Переводим
								google.language.translate(str, "ru", "en", function(result) {
							        if (!result.error) {
										self._postJSON('/admin-news/rename.html',{id:obj.id,name:str,link:result.translation},function(data){
											h1.html(str).show();
											input.remove();
											var frame = $('<div><fieldset><label for="link">Хотите изменить ссылку страницы на:</label><input type="text" id="link" value="'+data+'" /></fieldset></div>');
											self.alertPrefs.title = "Измение ссылки",
											self.alertPrefs.buttons = {
												'Изменить': function() {
													h1.parent().disableSelection();
													self._postJSON('/admin-news/update.html',{id:obj.id,link:$('#link').val()});
													$(this).dialog('close');
												},
												'Отмена': function() {
													$(this).dialog('close');
												}
											}
											self.alertPrefs.close = function() {frame.remove()}
											frame.dialog(self.alertPrefs);													
										});
							        }
								});
							}
								
						})
						.blur(function(){
							$(this).remove();
							h1.show();
						});
				break;
				case "mark_hot":
				self._postJSON('/admin-news/update.html',{id:obj.id,type:obj.action.split('/')[1]},function(data){
					obj.html = data.html;
					self.workView.tableView('option','editRowHTML',obj);
					obj.info = obj;
					obj.menu = data.menu;
					self.workView.tableView('option','editRowContextMenu',obj);
				});		
				break;
				case "images":
					var frame = $('<div class="imagesSelect"></div>');
					var imgs = [];
					//Рисуем рабочую часть
					frame.workView = $('<div class="work-view"></div>').appendTo(frame);
					self.imagesWindowPrefs.title = "Выбор изображения (перетащите на серое поле справа и нажмите сохранить)";
					self.imagesWindowPrefs.buttons = {
						'Выбрать': function() {
							var images = [];
							$('.dropzone li',frame).each(function(i){
								images[i] = $('[name=id]',this).val();
							})
							$(this).dialog('close');
							frame.workView.imagesView('destroy');
							frame.remove();
							
							self._postJSON('/admin-news/setimgs.html',{id:obj.id,imgs:images},function(){},false);
						},
						'Отмена': function() {
							$(this).dialog('close');
							frame.workView.imagesView('destroy');
							frame.remove();
							
						}
					};
					self.imagesWindowPrefs.open = function() {
						frame.workView.imagesView({withURL:false, multy:true, cont:frame, imgs:imgs});
					}
					self.imagesWindowPrefs.close = function() { frame.workView.imagesView('destroy'); frame.remove();};
					self._postJSON('/admin-news/getimgs.html',{id:obj.id},function(data){
						imgs = data;
						frame.dialog(self.imagesWindowPrefs);
					});
					
				break;
				case "date":	
					$('<div>')
						.datepicker('dialog', obj.action.split('/')[1], function(date) {
							self._postJSON('/admin-news/update.html',{id:obj.id,date:date},function(data){
							obj.html = data.html;
							self.workView.tableView('option','editRowHTML',obj);
							obj.info = obj;
							obj.menu = data.menu;
							self.workView.tableView('option','editRowContextMenu',obj);
						});
						}, {dateFormat: 'yy-mm-dd'}, [$(window).width()/2-110,$(window).height()/2-75])
				break;
			}
		});		
		this.make();
		this.workView.spinner('destroy');
	},
	page:1,
	editWindowPrefs:{
		modal: true,
		width:900,
		minWidth:750,
		height:488,
		minHeight:488,
		maxHeight:488,
		overlay: {backgroundColor: '#000',opacity: 0.5}
	},
	editShortWindowPrefs:{
		modal: true,
		width:450,
		height:150,
		resizable: false,
		overlay: {backgroundColor: '#000',opacity: 0.5}
	},
	imagesWindowPrefs:{
		modal: true,
		add:true,
		multy:true,
		width:940,
		height:550,
		resizable:false,
		overlay: {backgroundColor: '#000',opacity: 0.5}
	},	alertPrefs:{
		modal: true,
		width:300,
		height:150,
		resizable: false,
		overlay: {backgroundColor: '#000',opacity: 0.5},
		buttons: {
			'Сохранить': function() {
				$(this).dialog('close');
			},
			'Отмена': function() {
				$(this).dialog('close');
			}
		},
	},
	_postJSON:function(url,params,func,spinner){
		var self = this;
		if (typeof spinner == "undefined") {spinner = true};
		if (spinner) this.workView.spinner();
		$.post(url,params,function(data){
			if (spinner) self.workView.spinner('destroy');
			if (typeof func == "function")
				func(data);
		},'json');
	},
	make:function(){
//		var self = this;
//		this.workView.tableView('clear');
	},
	destroy: function() {
		this.element.tableView('destroy');
		this.workView.navigation('destroy');
		$.widget.prototype.destroy.apply(this, arguments);
	}
});

$.extend($.ui.adminnews, {
	version: "2.0.0",
	defaults: {

	}
});

})(jQuery);
