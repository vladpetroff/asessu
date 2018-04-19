(function($) {

$.widget("ui.templates", {

	_init: function() {
		var self = this;
		this.workView = this.element;
		this.workView.navigation();
		this.parent_id = 0;
		this.params = [0];
		this.workView.navigation('option','buttonMenu',[
			{name:"Создать шаблон",action:"create",inactive:false}
		]);
		this.workView.tableView();
		this.workView.tableView('option','addClass','templates');		

		// Обрабатываем кнопку +
		self.workView.bind('navContextAction',function(e,action){
			switch(action.split('/')[0]){
				case "create":
					self._postJSON('/admin-templates/create.html',{},function(data){
						//Добавляем элемент
						console.log(data.html[0]);
						self.workView.tableView('option','newRow',{
							html:data.html[0], //Содержание строки
							info:{ //Доп. информация, которая вернется нам в прерываниех
								id:data.object[0].id,
								name:data.object[0].name
													
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
		self.workView.bind('tableDblclick',function(e,obj){
			window.location.href = window.location.href +'/'+obj.id;
			self.params[self.params.length] = obj.id;
			self.parent_id = obj.id;
			self.make();				
		});
		//Обрабатываем контекстное меню
		self.workView.bind('tableContextAction',function(e,obj){
			var action = obj.action.split('/')[0];
			switch (action){
				case "modules":
					var frame = $('<div class="modulesSelect"></div>');
					//Рисуем рабочую часть
					frame.workView = $('<div class="work-view"></div>').appendTo(frame);
					frame.workView.height(475);
					self.imagesWindowPrefs.title = "Выбор модулей";
					self.imagesWindowPrefs.buttons = {
						'Сохранить': function() {
							var linked =  $('li:has(.fav)',frame.workView);
							var send = [];
							linked.each(function(i){
								send[i] = $('[name=id]',this).val();
							})
							self._postJSON('/admin-templates/modules.html',{id:obj.id,ids:send});
							$(this).dialog('close');
							frame.workView.imagesView('destroy');
							frame.remove();
						},
						'Отмена': function(){
							$(this).dialog('close');
							frame.workView.modules('destroy'); 
							frame.remove();
						}
					};
					self.imagesWindowPrefs.open = function() {
						frame.workView.modules({modal:true, id:obj.id});
					}
					self.imagesWindowPrefs.close = function() { frame.workView.modules('destroy'); frame.remove();};
					frame.dialog(self.imagesWindowPrefs);
				break;
				case "blocks":
					var frame = $('<div class="blocks"></div>');
					self._postJSON('/admin-templates/blocks.html',{id:obj.id},function(data){
						var frame = $('<div class="blocks"></div>');
						var html = $('<fieldset>'+data+'</fieldset>');
						self.blocksWindowPrefs.title = "Выбор модулей";
						self.blocksWindowPrefs.buttons = {
							'Сохранить': function() {
								var post = {};
								$('.blocks select').each(function(){
									post[this.id] = $(this).val();
								})
								self._postJSON('/admin-templates/blocks-save.html',{id:obj.id,blocs:post});
								$(this).dialog('close');
								frame.remove();
							},
							'Отмена': function(){
								$(this).dialog('close');
								frame.remove();
							}
						};
						self.blocksWindowPrefs.close = function() { frame.remove();};
						frame.dialog(self.blocksWindowPrefs);
						html.appendTo(frame);
					});
				break;
				case "edit":
					self._postJSON('/admin-templates/get.html',{id:obj.id},function(data){
						var html = $('<fieldset><label for="path">Путь к файлу:</label><input type="text" id="path" value="'+data.path+'" /><label for="templates_set">Переменные:</label><input type="text" id="templates_set" value="'+data.templates_set+'" /></fieldset>');
						self.frame = $('<div></div>');
						self.twoInputPrefs.title = "Редактирование пути и переменных";					
						self.twoInputPrefs.buttons = {
							'Сохранить': function() {
								self._postJSON('/admin-templates/update.html',{id:obj.id, path:$('#path',html).val(),templates_set:$('#templates_set',html).val()},function(data){
									obj.html = data.html;
									self.workView.tableView('option','editRowHTML',obj);
									obj.info = obj;
									obj.menu = data.menu[0];
									obj.width = 300;
									self.workView.tableView('option','editRowContextMenu',obj);
								});
								self.frame.remove();
								$(this).dialog('close');
							},
							'Отмена': function() {
								self.frame.remove(); $(this).dialog('close');
							}
						};
						self.twoInputPrefs.close = function() {self.frame.remove()}
						self.frame.dialog(self.twoInputPrefs);
						html.appendTo(self.frame);
					});
				break;	
				case "set_group":
					self._postJSON('/admin-templates/update.html',{id:obj.id, shown:obj.action.split('/')[1]},function(data){
						obj.html = data.html;
						self.workView.tableView('option','editRowHTML',obj);
						obj.info = obj;
						obj.menu = data.menu[0];
						obj.width = 300;
						self.workView.tableView('option','editRowContextMenu',obj);
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
								self._postJSON('/admin-templates/remove.html',{id:obj.id},function(){ 
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
								self._postJSON('/admin-templates/update.html',{id:obj.id,name:str});
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
	blocksWindowPrefs:{
		modal: true,
		width:300,
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
	twoInputPrefs:{
		modal: true,
		width:300,
		height:200,
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
		this.workView.tableView('clear');
		this.workView.navigation('clear');	
		// this._postJSON('/admin-templates/getnames.html',{id:self.params},function(data){
		// 	for (var i=0; i < self.params.length; i++) {
		// 		id = self.params[i];
		// 		if (id == 0) {
		// 			self.workView.navigation('option','push',{name:"Блоки",link:0});
		// 		} else {
		// 			self.workView.navigation('option','push',{name:data[i].name,link:id});
		// 		}
		// 	};			
		// },false);
		this._postJSON('/admin-templates/getall.html',{},function(data){
			for (var i=0; i < data.object.length; i++) {
				self.workView.tableView('option','newRow',{
					html:data.html[i],
					info:{
						id:data.object[i].id,
						name:data.object[i].name,				
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

$.extend($.ui.templates, {
	version: "2.0.0",
	defaults: {

	}
});

})(jQuery);
