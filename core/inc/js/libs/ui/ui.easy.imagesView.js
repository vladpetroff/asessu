(function($) {

$.widget("ui.imagesView", {

	_init: function() {		
		var self = this;
		this.work = this.element;
		if (this.options.add){
			this.work.navigation({addbutton:true});
		} else {
			this.work.navigation({addbutton:false});
		}
		this.table = this.element.tableView();
		this.element.tableView('option','addClass','images');
		this.parent_id = 0;
		this.params = [0];
		if (this.options.multy){
			$('<ul class="dropzone"></ul>').appendTo('.imagesSelect .table-wrap').sortable({
				containment:'.imagesSelect'
			});
				
		}
		//Создаем панель изображений
		this.make();
		//Обрабатываем меню +
		this.work.bind('navContextAction',function(e,action){
			switch(action.split('/')[0]){
				case "createDir":
					//Создаем и добавляем папку
					self._postJSON('/admin-images/create_dir.html',{id:self.params[self.params.length - 1]},function(data){
						//Добавляем элемент
						self.work.tableView('option','newRow',{
							html:data.html, //Содержание строки
							info:{ //Доп. информация, которая вернется нам в прерываниех
								id:data.object.id,
								name:data.object.name,
								type:data.object.type					
							},
							menu:data.menu, //Меню
						});						
					});
				break;
			}
		});
		//Загрузка файла
		this.work.bind('upload',function(e,data){
			//[k]  отключено мультизагрузка в фоксе	
			/*if ($.browser.mozilla && $.browser.version >= '1.9.2') {
				self.make();
			}else {
				//Добавляем новый файл
				self.work.tableView('option','newRow',{
					html:data.html, //Содержание строки
					info:{ //Доп. информация, которая вернется нам в прерываниех
						id:data.object.id,
						name:data.object.name,
						type:data.object.type					
					},
					menu:data.menu, //Меню
				});			
			}*/
			self.make();
		});
		//Обрабатываем даблклики
		this.work.bind('tableDblclick',function(e,obj){
			if (obj.type == 'folder') {
				//Если папка открываем ее
				if (self.options.withURL)
					window.location.href = window.location.href +'/'+obj.id;
				self.params[self.params.length] = obj.id;
				self.parent_id = obj.id;
				self.make();				
			} else {
				//Если картинка показываем лайтбокс
				$('a.lightbox').lightBox();
				$('a.id-'+obj.id).trigger('click');
			}
		});
		//Обрабатываем контекстное меню
		self.work.bind('tableContextAction',function(e,obj){
			var action = obj.action.split('/')[0];
			switch (action){
				case "open":
					//Открываем папку
					if (self.options.withURL)
						window.location.href = window.location.href +'/'+obj.id;
					self.params[self.params.length] = obj.id;
					self.parent_id = obj.id;
					self.make();				
				break;
				case "openimg":
					//Показываем лайтбокс с картинкой
					$('a.lightbox').lightBox();
					$('a.id-'+obj.id).trigger('click');
				break;
				case "rename":
					//Переименовываем
					// Берем название
					var h1 = $('h1',$('li',self.work).eq(obj.index));
					var a = $('a',$('li',self.work).eq(obj.index));
					var placeholder = h1.html();
					//Включаем возможность редактирования
					h1.parent().enableSelection();
					//Прячем название
					h1.hide();
					//Создаем инпут
					$('<input type="text" value="'+placeholder+'" />').insertAfter(h1).focus().select()
						.keypress(function(e){
							//Если нажат Esc
							if (e.keyCode == 27){
								$(this).blur();
							}
							//Если нажат Enter
							if (e.keyCode == 13){
								var str = $(this).val();
								var input = $(this);
								self._postJSON('/admin-images/update.html',{id:obj.id,name:str},function(){
									h1.html(str).show();
									a.attr('title',str);
									input.remove();
								});
							}			
						})
						.blur(function(){
							$(this).remove();
							h1.show();
						});
				break;
				case "remove":
				//Удаление
				if (obj.type == 'file')
					var frame = $('<div><p class="alert">Вы действительно хотите удалить изображение?</p></div>');
				else 
					var frame = $('<div><p class="alert">Вы действительно хотите удалить каталог?</p></div>');
				frame.dialog({
					title: "Удаление",
					width: 350,
					resizable: false,
					buttons: {
						'Удалить': function() {
							$.post('/admin-images/remove.html',{id:obj.id},function(){ 
								self.work.tableView('option','removeRow',obj.index);
							});
							$(this).dialog('close');
						},
						'Отмена': function() {
							$(this).dialog('close');
						}
					},
					close: function() {frame.remove()},
					modal: true,
					overlay: {backgroundColor: '#000',opacity: 0.5}					
				});
				frame.dialog();
				break;
			}
		});
		//Обраатываем клики по панели навигации
		self.work.bind('navigationClick',function(e,link){
			if (self.options.withURL) {
				window.location.href = link;
			}
			self.parent_id = link.split('/')[link.split('/').length -1];
			var tmp = link.split("#")[1];
			self.params = [];
			tmp = tmp.split('/');
			for (var i=1; i < tmp.length; i++) {
				self.params[i-1] = tmp[i];
			};	
			self.make();
		});
		
		this.work.spinner('destroy');
	},
	parent_id: 0,
	params:[0],
	_postJSON:function(url,params,func,spinner){
		var self = this;
		if (typeof spinner == "undefined") {spinner = true};
		if (spinner) this.work.spinner();
		$.post(url,params,function(data){
			if (spinner) self.work.spinner('destroy');
			if (typeof func == "function")
				func(data);
		},'json');
	},
	make:function(){
		
		var self = this;
		if (this.parent_id == 0)
		if (this.options.withURL) {
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
		this.work.tableView('clear');
		this.work.navigation('clear');	

		this.work.navigation('option','buttonMenu',[
			{name:"Загрузить файл",action:"createPage",inactive:false,upload:true,id:this.parent_id},
			{name:"Создать каталог",action:"createDir",inactive:false}
		]);
		this._postJSON('/admin-images/getnames.html',{id:this.params},function(data){
			for (var i=0; i < self.params.length; i++) {
				id = self.params[i];
				if (id == 0) {
					self.work.navigation('option','push',{name:"Изображения",link:0});
				} else {
					self.work.navigation('option','push',{name:data[i].name,link:id});
				}
			};			
		},false);
		this._postJSON('/admin-images/getall.html',{id:this.parent_id},function(data){
			for (var i=0; i < data.object.length; i++) {
				self.work.tableView('option','newRow',{
					html:data.html[i],
					info:{
						id:data.object[i].id,
						name:data.object[i].name,
						type:data.object[i].type				
					},
					menu:data.menu[i],
				});
			}
			if (self.options.multy) {		
				$('.table-view li',self.work).has('.page').draggable({
					opacity:.7,
					helper:'clone',
					containment: $('.imagesSelect').length ? '.imagesSelect' : 'document',
					revert: 'invalid',
					cursor: 'move'
				});
				$('.dropzone',self.work.parent()).droppable('destroy');
				$('.dropzone li',self.work).draggable('destroy');
				$('.dropzone',self.work.parent()).droppable({
						accept: '.table-view.images > li',
						drop: function(ev, ui) {
								ui.draggable.clone().appendTo('.dropzone');
						}
				});

				$('.table-view',self.work.parent()).droppable({
						accept: '.dropzone > li',
						drop: function(ev, ui) {
								ui.draggable.remove();
						}
				});
				for (var i=0; i < self.options.imgs.length; i++) {
					var img = self.options.imgs[i];
					$('<li><img alt="" src="'+img.filename+'" /><input name="id" type="hidden" value="'+img.id+'" /></li>').appendTo('.dropzone',this.work);
				};
				self.options.imgs = 0;
			};
		});
	},
	destroy: function() {
		this.element.tableView('destroy');
		this.work.navigation('destroy');
		$.widget.prototype.destroy.apply(this, arguments);
	}
});

$.extend($.ui.imagesView, {
	version: "2.0.0",
	defaults: {
		withURL:true,
		multy:false,
		add:true,
		cont:'parent',
		imgs:0
	}
});

})(jQuery);
