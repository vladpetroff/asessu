//[k] буфер обмена
var buffer = {};
buffer.full = false;

(function($) {

$.widget("ui.dashboard", {

	_init: function() {
		var self = this;
		//Определяем центр по горизонтали
		this.center = ($(window).width() / 2);

		//Рисуем Логотип и оформление окна
		var left = self.center - 470;
		this.easyView = $('<div class="easy-view" style="left:'+left+'px;"><div class="logo"></div><a class="doc" target="_blank" href="/help.html">Документация</a><a class="out" target="_blank" href="/">Просмотр сайта</a><a class="logout" href="/login/logout.html">Выход</a></div>').appendTo(this.element);
		this.easyView.height($(window).height());				
		//Рисуем рабочую часть окна
		var height = $(window).height() - 120;

		this.mainView = $('<div class="main-view" style="height:'+height+'px;"></div>').appendTo(this.easyView);
		//Рисум боковую панель.
		this.sidebar = $('<div class="side-bar"></div>').appendTo(this.mainView);
		//Рисуем рабочую часть
		this.workView = $('<div class="work-view"></div>').appendTo(this.mainView);
		this.workView.easy = this.utils;
		//Запускаем наш контроллер, управляющий загрузкой нужных модулей
		this._controller();
		//На основе URL делаем активным соответствующий пункт меню
		this.menu.menu('select','[href=#'+this.module+']');

		//Следим за изменениями размеров окна и применяем изменения к нашим елементам
		$(window).resize(function(){
			self.easyView.height(self.element.height());
			self.center = $(window).width() / 2;
			self.easyView.css({left:self.center - (self.easyView.width() / 2)});
			self.easyView.height($(window).height());
			self.mainView.height($(window).height() - 120);
			self.workView.trigger('resize');
		});
		
	},
	
	utils: function() {
		var self = this;
		

		// parseURL: function (){
		// 	
		// },
		// start: function(options){
		// 	console.log(this);
		// 	self.workView.spinner('destroy');
		// }
	},
	
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
		if (spinner) this._spinner();
		$.post(url,params,function(data){
			if (spinner) self._spinner();
			if (typeof func == "function")
				func(data);
		},'json');
	},
	
	
	_controller: function(page){
		var self = this;
		this.workView.navigation('destroy');
		this.workView.tableView('destroy');
		this.workView.imagesView('destroy');
		this.menu = this.sidebar.menu('destroy');
		for (var i=0; i < this.loaded.length; i++) {
			this.workView[this.loaded[i]]('destroy');
		};
		
		$('*',this.element).unbind();

		//В боковой панели создаем меню
		this.menu = this.sidebar.menu({menu:admin_menu});

		//Обработчик события изменения страницы при выборе пункта меню(не исполбзуем click т.к. можно будет прикрутить например навигацию по клавиатуре)
		this.menu.bind('changepage',function(e, page){
			self._controller(page);
		});
		this._parseURL(page);
		this.menu.menu('select','[href=#'+this.module+']');
		this.load(this.module);
	},
	
	_parseURL: function(url){
		if (typeof url == "undefined"){
			url = window.location.href;
		} else {
			window.location.href = url;
		}
		var params = url.split('#');
		this.link = params[0]
		if (params.length == 1){
			this.module = $('a',this.menu).eq(0).attr('href').split('#')[1];
			window.location.href = this.link+"#"+$('a',this.menu).eq(0).attr('href').split('#')[1];
		} else {
			params = params[1].split('/');
			this.module = params[0];
			this.params = [];
			for (var i=1; i < params.length; i++) {
				this.params[i-1] = params[i];
			};
			if (typeof this[this.module] == "undefined") {
				this.module = this.options.module;
				window.location.href = this.link+"#"+this.options.module;
			}
		}
		this.url = url;
	},
	loaded:[],
	_try: function(name){
		var self = this;
		if (typeof this.workView[name] == "undefined"){
			$.getScript('/core/inc/js/libs/ui/ui.easy.'+name+'.js',function(){
				self.workView[name]();
				self.loaded[self.loaded.length] = name;					
			})
		} else {
			self.workView[name]();
		}
	},
	_spinner: function(){
		if ($('.easy-spinner',this.workView).length) {
			this.workView.spinner('destroy');
		} else {
			this.workView.spinner();
		}		
	},
	
	load:function(module){
		this._spinner();
		this[this.module]();
	},

	"admin-images": function(){
		this.workView.imagesView();
	},
	
	"admin-orders": function(){
		this._try('adminorders');
	},
	
	"admin-settings": function(){
		this._try('adminsettings');
	},

	"admin-users": function(){
		this._try('adminusers');
	},
	
	"admin-blocks": function(){
		this._try('blocks');
	},
	
	"admin-templates": function(){
		this._try('templates');
	},
	
	"admin-modules": function(){
		this._try('modules');
	},
	
	"admin-faq": function(){
		this._try('faq');
	},
	
	articles: function(){
		this.workView.navigation();
		this.workView.navigation('option','buttonMenu',[
			{name:"Добавить статью",inactive:true},
			{name:"Создать категорию",action:"createDir",inactive:false}
		]);
		this._spinner();
	},
	
	"admin-news": function(){
		this._try('adminnews');
	},

	"admin-contact": function(){
		this._try('admincontact');
	},

	"admin-catalog": function(){
		this._try('admincatalog');
	},
	"admin-blocks": function(){
		this._try('blocks');
	},
	//Структура Сайта
	"admin-tree": function(){
		var self = this;
		//Создаем навигационное меню.
		this.workView.navigation();
		$.post('/admin-tree/get_templates.html',function(data){
			var submenu = [];
			for (var y=0; y < data.templates.length; y++) {
				var template = data.templates[y];
				submenu[y] = {name:template.name,action:template.id, selected:false}
			};			
			self.workView.navigation('option','buttonMenu',[
				{name:"Создать страницу на основе",action:"createPage", inactive:false, sub:submenu, width:300},
				{name:"Создать каталог",action:"createDir",inactive:false}
			]);			
		},'json');

		// Создаем таблицу для вывода данных
		this.workView.tableView({sortable:true});
		this.workView.tableView('option','addClass','web');
		// обрабатываем даблклик по элементу дерева
		self.workView.bind('tableDblclick',function(e,obj){
			//Если каталог - открываем его.
			if (obj.type == 'folder') {
				window.location.href = window.location.href +'/'+obj.id;
				self.params[self.params.length] = obj.id;
				self._spinner();
				getTree();				
			};
		});
		self.workView.bind('tableSortStop',function(){
			var arr = [];
			$('li',self.workView).each(function(i){
				arr[i] = $('[name=id]',this).val();
			});
			self._postJSON('/admin-tree/save_pos',{id:self.parent_id,pos:arr},function(){
				self._spinner();
				getTree();
			});
		});
		//Обрабатываем контекстное меню
		self.workView.bind('tableContextAction',function(e,obj){
			var action = obj.action.split('/')[0];
			switch (action){
				case "open":
					window.location.href +='/'+obj.id;
					self._parseURL();
					self._spinner();
					getTree();
				break;
				case "edit_text":
					self._spinner();
					$.post('/admin-tree/get_text.html',{id:obj.id},function(data){
						var html = $('<textarea id="text">'+data.html+'</textarea>');
						var frame = $('<div></div>');
						self.editWindowPrefs.title = "Редактирование текста";
						self.editWindowPrefs.width = 600;
						self.editWindowPrefs.height = 485;
						self.editWindowPrefs.minHeight = 485;
						self.editWindowPrefs.resizable = true;						
						self.editWindowPrefs.buttons = {
							'Сохранить': function() {
								self._spinner();
								$.post('/admin-tree/set_text.html',{id:obj.id, html:CKEDITOR.instances.text.getData()},function(){ self._spinner(); });
								if (CKEDITOR.instances.text) CKEDITOR.remove(CKEDITOR.instances.text);
								frame.remove();
								$(this).dialog('close');
							},
							'Отмена': function() {
								if (CKEDITOR.instances.text) CKEDITOR.remove(CKEDITOR.instances.text); frame.remove(); $(this).dialog('close');
							}
						};
						self.editWindowPrefs.close = function() {if (CKEDITOR.instances.text) CKEDITOR.remove(CKEDITOR.instances.text); frame.remove()}
						frame.dialog(self.editWindowPrefs);
						html.appendTo(frame);
						if (!CKEDITOR.instances.edithtml) CKEDITOR.replace('text', {toolbar : 'Editbar',
								filebrowserBrowseUrl : '/core/inc/ckfinder/ckfinder.html',
								filebrowserImageBrowseUrl : '/core/inc/ckfinder/ckfinder.html?Type=Images',
								filebrowserFlashBrowseUrl : '/core/inc/ckfinder/ckfinder.html?Type=Flash',
								filebrowserUploadUrl : '/core/inc/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
								filebrowserImageUploadUrl : '/core/inc/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
								filebrowserFlashUploadUrl : '/core/inc/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash'
							});						
						self._spinner();
					},'json');
				break;
				case "edit_meta":
					self._spinner();
					$.post('/admin-tree/get_meta.html',{id:obj.id},function(data){
						var html = '<fieldset>';
						html+= '<label for="title">Название:</label><input type="text" id="title" value="'+data.title+'"/>';
						html+= '<label for="link">Ссылка:</label><input type="text" id="link" value="'+data.link+'"/>';
						html+= '<label for="className">Класс:</label><input type="text" id="classname" value="'+data.classname+'"/>';
						html+= '<label for="description">Описание:</label><textarea id="description">'+data.description+'</textarea>';
						html+= '<label for="keywords">Ключевые слова:</label><input type="text" id="keywords" value="'+data.keywords+'"/>';
						html+= '<label for="h1">Заголовок первого уровня:</label><input type="text" id="h1" value="'+data.h1+'"/>';
						html+= '</fielset>';
						var frame = $('<div></div>');
						self.editWindowPrefs.title = "Meta-информация";
						self.editWindowPrefs.width = 600;
						self.editWindowPrefs.height = 485;
						self.editWindowPrefs.minHeight = 485;
						self.editWindowPrefs.resizable = true;
						self.editWindowPrefs.buttons = {
							'Сохранить': function() {
								self._spinner();
								$.post('/admin-tree/set_meta.html',{
									id:obj.id,
									title:$('#title').val(),
									link:$('#link').val(),
									classname:$('#classname').val(),
									description:$('#description').val(),
									keywords:$('#keywords').val(),
									h1:$('#h1').val()
								},function(){ self._spinner(); });
								$(this).dialog('close');
							},
							'Отмена': function() {
								$(this).dialog('close');
							}
						};
						self.editWindowPrefs.close = function() {frame.remove()}
						frame.dialog(self.editWindowPrefs);
						$(html).appendTo(frame);
						self._spinner();
					},'json');
				break;
				case "edit_link":
					self._spinner();
					$.post('/admin-tree/get_meta.html',{id:obj.id},function(data){	
						var frame = $('<div><fieldset><label for="link">Хотите изменить сcылку страницы на:</label><input type="text" id="link" value="'+data.link+'" /></fieldset></div>');
						self.editWindowPrefs.title = "Измение ссылки",
						self.editWindowPrefs.width = 350;
						self.editWindowPrefs.height = 145;
						self.editWindowPrefs.minHeight = 145;
						self.editWindowPrefs.resizable = false;
						self.editWindowPrefs.buttons = {
							'Изменить': function() {
								self._spinner();
								$('input[value='+obj.id+']').prev().prev().html($('#link').val()+'.html');
								$.post('/admin-tree/set_meta.html',{id:obj.id,link:$('#link').val()},function(){ 
									self._spinner();
								});
								$(this).dialog('close');
							},
							'Отмена': function() {
								$(this).dialog('close');
							}
						};
						self.editWindowPrefs.close = function() {frame.remove()}
						frame.dialog(self.editWindowPrefs);											
						self._spinner();
					},'json');
				break;
				case "edit_header":
					self._spinner();
					$.post('/admin-tree/get_meta.html',{id:obj.id},function(data){	
						var frame = $('<div><fieldset><label for="pheader">Введите заголовок:</label><input type="text" id="pheader" value="'+data.header+'" /></fieldset></div>');
						self.editWindowPrefs.title = "Измение заголовка",
						self.editWindowPrefs.width = 350;
						self.editWindowPrefs.height = 145;
						self.editWindowPrefs.minHeight = 145;
						self.editWindowPrefs.resizable = false;
						self.editWindowPrefs.buttons = {
							'Сохранить': function() {
								self._spinner();
								$.post('/admin-tree/set_meta.html',{id:obj.id,header:$('#pheader').val()},function(){ 
									self._spinner();
								});
								$(this).dialog('close');
							},
							'Отмена': function() {
								$(this).dialog('close');
							}
						};
						self.editWindowPrefs.close = function() {frame.remove()}
						frame.dialog(self.editWindowPrefs);											
						self._spinner();
					},'json');
				break;
				case "set_group":
					if(obj.action.split('/').length > 1){
						self._spinner();

						$.post('/admin-tree/set_group.html',{id:obj.id,group_id:obj.action.split('/')[1]},function(data){
							var menu = genMenu(data.page,data.groups,data.templates);
							self.workView.tableView('option','editRowContextMenu',{
								info:{ //Доп. информация, которая вернется нам в прерываниех
									index:obj.index,
									id:data.page.id,
									name:data.page.name,
									catalog:(data.page.c == 0)?false:true						
								},
								menu:menu,
								width:300
							});	
							self._spinner();				
						},'json');						
					}
		
				break;
				case "set_template":
					
					if(obj.action.split('/').length > 1){
						self._spinner();
						$.post('/admin-tree/set_template.html',{id:obj.id, template:obj.action.split('/')[1]},function(data){
							var menu = genMenu(data.page,data.groups,data.templates);
							self.workView.tableView('option','editRowContextMenu',{
								info:{ //Доп. информация, которая вернется нам в прерываниех
									index:obj.index,
									id:data.page.id,
									name:data.page.name,
									catalog:(data.page.c == 0)?false:true						
								},
								menu:menu,
								width:300
							});	
							self._spinner();				
						},'json');						
					}
	
				break;
				case "mark_def":
					self._spinner();
					$.post('/admin-tree/mark_def.html',{id:obj.id,def:"y"},function(data){
							var html = '<div class="page"></div>';
							html+= '<h1>'+data.page.name+'</h1>';
							html+='<p>'+data.page.link+'.html</p>';
							html+='<p>'+data.page.title+'</p>';
							html+='<input type="hidden" name="id" value="'+obj.id+'" />';
							html+='<div class="fav"></div>';
						$('.fav',self.workView.tableView.element).remove();			
						self.workView.tableView('option','editRowHTML',{
							index:obj.index,
							html:html
						});	
						self._spinner();				
					},'json');					
				break;
				case "buffer_cut" : //[k] вырезать в буфер обмена
					buffer.full = true;
					buffer.obj = obj;
					getTree();
					self._spinner();					
					break;
					
				case "buffer_insert" : //[k] вставить из буфера
					self._spinner();
					$.post('/admin-tree/move.html',{id:buffer.obj.id,pid:obj.id},function(){ 
						buffer.full = false;
						buffer.obj = null;
						getTree();
					});					
					break;				
				case "remove":
						if (obj.type == 'page')
							var frame = $('<div><p class="alert">Вы действительно хотите удалить страницу?</p></div>');
						else 
							var frame = $('<div><p class="alert">Вы действительно хотите удалить каталог?</p></div>');
						self.editWindowPrefs.title = "Удаление",
						self.editWindowPrefs.width = 350;
						self.editWindowPrefs.height = 135;
						self.editWindowPrefs.minHeight = 135;
						self.editWindowPrefs.resizable = false;
						self.editWindowPrefs.buttons = {
							'Удалить': function() {
								self._spinner();
								$.post('/admin-tree/remove.html',{id:obj.id},function(){ 
									self.workView.tableView('option','removeRow',obj.index);
									self._spinner();
								});
								$(this).dialog('close');
							},
							'Отмена': function() {
								$(this).dialog('close');
							}
						};
						self.editWindowPrefs.close = function() {frame.remove()}
						frame.dialog(self.editWindowPrefs);					
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
								self._spinner();
								var str = $(this).val();
								var input = $(this);
								//Переводим
								google.language.translate(str, "ru", "en", function(result) {
							        if (!result.error) {
										$.post('/admin-tree/rename.html',{id:obj.id,name:str,link:result.translation},function(data){
											self._spinner();
											// if (obj.type == "folder") {
											// 	h1.html(str).show();
											// 	input.remove();												
											// 	h1.parent().disableSelection();
											// } else {
												h1.html(str).show();
												input.remove();
												var frame = $('<div><fieldset><label for="link">Хотите изменить сcылку страницы на:</label><input type="text" id="link" value="'+data+'" /></fieldset></div>');
												self.editWindowPrefs.title = "Изменение ссылки",
												self.editWindowPrefs.width = 350;
												self.editWindowPrefs.height = 145;
												self.editWindowPrefs.minHeight = 145;
												self.editWindowPrefs.resizable = false;
												self.editWindowPrefs.buttons = {
													'Изменить': function() {
														self._spinner();
														h1.next().html($('#link').val()+'.html');
														h1.parent().disableSelection();
														$.post('/admin-tree/set_meta.html',{id:obj.id,link:$('#link').val()},function(){ 
															self._spinner();
														});
														$(this).dialog('close');
													},
													'Отмена': function() {
														$(this).dialog('close');
													}
												};
												self.editWindowPrefs.close = function() {frame.remove()}
												frame.dialog(self.editWindowPrefs);											
											// }						
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
				
			}
		});
		// Обрабатываем нажатия навигационного меню
		self.workView.bind('navigationClick',function(e,link){
			window.location.href = link;
			self._parseURL();
			self._spinner();
			getTree();
		});
		// Обрабавываем сортировку
		self.workView.bind('tableSortStop',function(){
			var arr = [];
			$('li',self.workView).each(function(i){
				arr[i] = $('[name=id]',this).val();
			});
			$.post('/admin-tree/save_pos',{pos:arr});
		});
		// Обрабатываем кнопку +
		self.workView.bind('navContextAction',function(e,action){
			switch(action.split('/')[0]){
				case "createDir":
					self._spinner();
					$.post('/admin-tree/create_tree.html',{parent:self.params[self.params.length - 1]},function(data){
							var page = data.pages[0];
							//Вывод элемента
							if (page.type == 'folder'){
								var html = '<div class="folder"></div>';
							} else {
								var html = '<div class="page"></div>';
							}
							html+= '<h1>'+page.name+'</h1>';
							if (page.type == 'folder'){
								html+='<p>Елементов:'+page.c+'</p>';
							} else {
								html+='<p>'+page.link+'.html</p>';
								html+='<p>'+page.title+'</p>';
								html+='<input type="hidden" name="id" value="'+page.id+'" />';
							}
							if (page.def == 'y') {
								html+='<div class="fav"></div>';
							}					
							var menu = genMenu(page,data.groups,data.templates);	
							//Добавляем элемент
							self.workView.tableView('option','newRow',{
								html:html, //Содержание строки
								info:{ //Доп. информация, которая вернется нам в прерываниех
									id:page.id,
									name:page.name,
									type:page.type					
								},
								menu:menu, //Меню
								width:300 //Ширина меню
							});
						self._spinner();						
					},'json');
				break;
				case "createPage":
				if (action.split('/').length == 1) {return false}
				self._spinner();
				$.post('/admin-tree/create_page.html',{parent:self.params[self.params.length - 1], template: action.split('/')[1]},function(data){
						var page = data.pages[0];
						//Вывод элемента
						if (page.type == 'folder'){
							var html = '<div class="folder"></div>';
						} else {
							var html = '<div class="page"></div>';
						}
						html+= '<h1>'+page.name+'</h1>';
						if (page.type == 'folder'){
							html+='<p>Елементов:'+page.c+'</p>';
						} else {
							html+='<p>'+page.link+'.html</p>';
							html+='<p>'+page.title+'</p>';
							html+='<input type="hidden" name="id" value="'+page.id+'" />';
						}
						if (page.def == 'y') {
							html+='<div class="fav"></div>';
						}					
						var menu = genMenu(page,data.groups,data.templates);	
						//Добавляем элемент
						self.workView.tableView('option','newRow',{
							html:html, //Содержание строки
							info:{ //Доп. информация, которая вернется нам в прерываниех
								id:page.id,
								name:page.name,
								type:page.type					
							},
							menu:menu, //Меню
							width:300 //Ширина меню
						});
					self._spinner();						
				},'json');					
				break;
			}
		})
		//Устанавливаем каталог по-умолчанию
		if (this.params.length == 0) {
			this.params[0] = 0;
			window.location.href+='/0';
		}
		//Запускаем генерацию дерева
		getTree();		
		//Генерация дерева
		function getTree(){
			//Очищаем навигацию и вывод
			self.workView.tableView('clear');
			self.workView.navigation('clear');			
			var id = 0;
			//Получаем имена рдительских каталогов и помещаем их в навигацию
			$.post('/admin-tree/getnames/'+self.params.join('/'),function(data){
				for (var i=0; i < self.params.length; i++) {
					id = self.params[i];
					if (id == 0) {
						self.workView.navigation('option','push',{name:"Структура сайта",link:0});
					} else {
						self.workView.navigation('option','push',{name:data[i].name,link:id});
					}
				};			
			},'json');
			//Получаем список элементов каталога
			$.post('/admin-tree/get/'+self.params[self.params.length - 1],function(data){
				if (data.pages != null)
				for (var i=0; i < data.pages.length; i++) {
					var page = data.pages[i];
					//Вывод элемента
					if (page.type == 'folder'){
						var html = '<div class="folder"></div>';
					} else {
						var html = '<div class="page"></div>';
					}
					html+= '<h1>'+page.name+'</h1>';
					if (page.type == 'folder'){
						html+='<p>'+page.link+'.html</p>';
						html+='<p>Элементов:'+page.c+'</p>';
						html+='<input type="hidden" name="id" value="'+page.id+'" />';
					} else {
						html+='<p>'+page.link+'.html</p>';
						html+='<p>'+page.title+'</p>';
						html+='<input type="hidden" name="id" value="'+page.id+'" />';
					}
					if (page.def == 'y') {
						html+='<div class="fav"></div>';
					}					
					var menu = genMenu(page,data.groups,data.templates);	
					//Добавляем элемент
					self.workView.tableView('option','newRow',{
						html:html, //Содержание строки
						info:{ //Доп. информация, которая вернется нам в прерываниех
							id:page.id,
							name:page.name,
							type:page.type					
						},
						menu:menu, //Меню
						width:300 //Ширина меню
					});
				}
				self._spinner();
			},'json');
		}
		function genMenu(page,groups,templates){
			//Контекстное меню для элемента	
			var menu = [];
			if (page.type == 'folder') {
				menu[menu.length] = {name:"Открыть",action:"open",inactive:false};
				menu[menu.length] = {name:"Редактировать ссылку",action:"edit_link",inactive:false};
				var submenu = [];
				for (var y=0; y < groups.length; y++) {
					var group = groups[y];
					submenu[y] = {name:group.name,action:group.id, selected:(group.id == page.group_id)?true:false}
				};				
				menu[menu.length] = {name:"Права доступа",action:"set_group",inactive:false, sub:submenu};
				var submenu2 = [];
				for (var y=0; y < templates.length; y++) {
					var template = templates[y];
					submenu2[y] = {name:template.name,action:template.id, selected:(template.id == page.template)?true:false}
				};			
				menu[menu.length] = {name:"Шаблон",action:"set_template",inactive:false, width:400,sub:submenu2};
				menu[menu.length] = "separator";
				menu[menu.length] = {name:"Вырезать",action:"buffer_cut",inactive:false};
				if (buffer && (buffer.full == true)) {
					menu[menu.length] = {name:"Вставить",action:"buffer_insert",inactive:false};
				}				
			} else {	
				menu[menu.length] = {name:"Редактировать заголовок",action:"edit_header",inactive:false};
				menu[menu.length] = {name:"Редактировать текст",action:"edit_text",inactive:false};
				menu[menu.length] = "separator";
				menu[menu.length] = {name:"Редактировать meta-информацию",action:"edit_meta",inactive:false};
				var submenu = [];
				for (var y=0; y < groups.length; y++) {
					var group = groups[y];
					submenu[y] = {name:group.name,action:group.id, selected:(group.id == page.group_id)?true:false}
				};				
				menu[menu.length] = {name:"Права доступа",action:"set_group",inactive:false, sub:submenu};
				var submenu2 = [];
				for (var y=0; y < templates.length; y++) {
					var template = templates[y];
					submenu2[y] = {name:template.name,action:template.id, selected:(template.id == page.template)?true:false}
				};			
				menu[menu.length] = {name:"Шаблон",action:"set_template",inactive:false, width:400,sub:submenu2};
				menu[menu.length] = "separator";
				menu[menu.length] = {name:"Сделать главной",action:"mark_def",inactive:false};				
				menu[menu.length] = {name:"Вырезать",action:"buffer_cut",inactive:false};
			}
			menu[menu.length] = {name:"Переименовать",action:"rename",inactive:false},
			menu[menu.length] = {name:"Удалить",action:"remove",inactive:(page.c == 0)?false:true}					
			return menu;
		}
	},
	
	destroy: function() {
		this.easyView.remove();
		$.widget.prototype.destroy.apply(this, arguments);
	}
});

$.extend($.ui.dashboard, {
	version: "2.0.1",
	defaults: {
		module: 'admin-tree' //Загружаемый по-умолчанию, модуль.
	}
});

})(jQuery);
