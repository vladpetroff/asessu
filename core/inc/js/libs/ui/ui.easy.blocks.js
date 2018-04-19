(function($) {

$.widget("ui.blocks", {

	_init: function() {
		var self = this;
		this.workView = this.element;
		this.workView.navigation();
		this.parent_id = 0;
		this.params = [0];
		this.workView.navigation('option','buttonMenu',[
//			{name:"Добавить папку",action:"create/folder",inactive:false},
//			{name:"Добавить группу блоков",action:"create/group",inactive:false},
//			{name:"Добавить группу ротации",action:"create/rotate",inactive:false},
			{name:"Создать блок",action:"create/block",inactive:false}
		]);
		this.workView.tableView();
		this.workView.tableView('option','addClass','blocks');		
		//Устанавливаем каталог по-умолчанию
		if (this.params.length == 0) {
			this.params[0] = 0;
			window.location.href+='/0';
		}
		// Обрабатываем кнопку +
		self.workView.bind('navContextAction',function(e,action){
			switch(action.split('/')[0]){
				case "create":
					self._postJSON('/admin-blocks/create.html',{id:self.params[self.params.length - 1], type:action.split('/')[1] },function(data){
						//Добавляем элемент
						self.workView.tableView('option','newRow',{
							html:data.html[0], //Содержание строки
							info:{ //Доп. информация, которая вернется нам в прерываниех
								id:data.object[0].id,
								name:data.object[0].name,
								type:data.object[0].type					
							},
							menu:data.menu[0], //Меню
							width:300 //Ширина меню
						});						
					});
				break;
			}
		})
		// Обрабатываем нажатия навигационного меню
		self.workView.bind('navigationClick',function(e,link){
			window.location.href = link;
			self.parent_id = link.split('/')[link.split('/').length -1];
			var tmp = link.split("#")[1];
			self.params = [];
			tmp = tmp.split('/');
			for (var i=1; i < tmp.length; i++) {
				self.params[i-1] = tmp[i];
			};	
			self.make();
		});
		// обрабатываем даблклик по элементу дерева
		/*self.workView.bind('tableDblclick',function(e,obj){
			window.location.href = window.location.href +'/'+obj.id;
			self.params[self.params.length] = obj.id;
			self.parent_id = obj.id;
			self.make();				
		});*/
		//Обрабатываем контекстное меню
		self.workView.bind('tableContextAction',function(e,obj){
			var action = obj.action.split('/')[0];
			switch (action){
				case "open":
					window.location.href = window.location.href +'/'+obj.id;
					self.params[self.params.length] = obj.id;
					self.parent_id = obj.id;
					self.make();
				break;
				case "edit_text":
					self._postJSON('/admin-blocks/get_text.html',{id:obj.id},function(data){
						var html = $('<textarea id="text">'+data.html+'</textarea>');
						self.frame = $('<div></div>');
						self.editWindowPrefs.title = "Редактирование текста";					
						self.editWindowPrefs.buttons = {
							'Сохранить': function() {
								self._postJSON('/admin-blocks/update.html',{id:obj.id, content:CKEDITOR.instances.text.getData()});
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
				break;
				case "images":
					var frame = $('<div class="imagesSelect"></div>');
					var imgs = [];
					//Рисуем рабочую часть
					frame.workView = $('<div class="work-view"></div>').appendTo(frame);
					self.imagesWindowPrefs.title = "Выбор изображений";
					self.imagesWindowPrefs.buttons = {
						'Выбрать': function() {
							var images = [];
							$('.dropzone li',frame).each(function(i){
								images[i] = $('[name=id]',this).val();
							})
							$(this).dialog('close');
							frame.workView.imagesView('destroy');
							frame.remove();
							
							self._postJSON('/admin-blocks/setimgs.html',{id:obj.id,imgs:images},function(){},false);
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
					self._postJSON('/admin-blocks/getimgs.html',{id:obj.id},function(data){
						imgs = data;
						frame.dialog(self.imagesWindowPrefs);
					});
					
				break;
				case "remove":
						if (obj.type == 'block')
							var frame = $('<div><p class="alert">Вы действительно хотите удалить блок?</p></div>');
						else 
							var frame = $('<div><p class="alert">Вы действительно хотите удалить каталог?</p></div>');
						self.alertPrefs.title = "Удаление",
						self.alertPrefs.buttons = {
							'Удалить': function() {
								self._postJSON('/admin-blocks/remove.html',{id:obj.id},function(){ 
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
								h1.html(str).show();
								input.remove();
								self._postJSON('/admin-blocks/rename.html',{id:obj.id,name:str});
							}
								
						})
						.blur(function(){
							$(this).remove();
							h1.show();
						});
				break;
			}
		});		
		this.make();
		this.workView.spinner('destroy');
	},
	parent_id: 0,
	params:[0],
	editWindowPrefs:{
		modal: true,
		width:900,
		minWidth:750,
		height:488,
		minHeight:488,
		maxHeight:488,
		overlay: {backgroundColor: '#000',opacity: 0.5}
	},
	imagesWindowPrefs:{
		modal: true,
		width:940,
		height:550,
		resizable:false,
		overlay: {backgroundColor: '#000',opacity: 0.5}
	},
	oneInputPrefs:{
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
	alertPrefs:{
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
		var self = this;
		if (this.parent_id == 0){
			var url = window.location.href.split("#")[1];
			if (url.split('/').length > 1) {
				this.parent_id = url.split('/')[url.split('/').length - 1];
			} else {
				window.location.href+='/0';
			}
			var tmp = window.location.href.split("#")[1];
			this.params = [];
			tmp = tmp.split('/');
			for (var i=1; i < tmp.length; i++) {
				this.params[i-1] = tmp[i];
			};
		}
		this.workView.tableView('clear');
		this.workView.navigation('clear');	
		this._postJSON('/admin-blocks/getnames.html',{id:self.params},function(data){
			for (var i=0; i < self.params.length; i++) {
				id = self.params[i];
				if (id == 0) {
					self.workView.navigation('option','push',{name:"Блоки",link:0});
				} else {
					self.workView.navigation('option','push',{name:data[i].name,link:id});
				}
			};			
		},false);
		this._postJSON('/admin-blocks/getall.html',{id:self.parent_id},function(data){
			for (var i=0; i < data.object.length; i++) {
				self.workView.tableView('option','newRow',{
					html:data.html[i],
					info:{
						id:data.object[i].id,
						name:data.object[i].name,
						type:data.object[i].type				
					},
					menu:data.menu[i], //Меню
					width:300 //Ширина меню
				});
			}		
		});
	},
	destroy: function() {
		this.element.tableView('destroy');
		this.workView.navigation('destroy');
		$.widget.prototype.destroy.apply(this, arguments);
	}
});

$.extend($.ui.blocks, {
	version: "2.0.0",
	defaults: {

	}
});

})(jQuery);
