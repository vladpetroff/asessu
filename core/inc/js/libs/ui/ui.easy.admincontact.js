(function($) {

$.widget("ui.admincontact", {

	_init: function() {
		var self = this;
		this.workView = this.element;
		this.workView.navigation();
		this.parent_id = 0;
		this.params = [0];
		this.workView.navigation('option','buttonMenu',[
			{name:"Создать форму",action:"create/block",inactive:false}
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
					self._postJSON('/admin-contact/create.html',{id:self.params[self.params.length - 1], type:action.split('/')[1] },function(data){
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
					// добавление поле с параметрами
					function addFields(value, key, type, cssClass) {
						var fieldType = ['строка', 'текстовое поле']
						var fieldValue = ['text', 'textarea']
						var fieldOption = ''
						for (var i=0; i<fieldType.length; i++) {
							if (fieldValue[i] == type)
								fieldOption += '<option value="'+fieldValue[i]+'" selected >'+fieldType[i]+ '</option>';
							else
								fieldOption += '<option value="'+fieldValue[i]+'" >'+fieldType[i]+ '</option>';					
						}

						var actionType = ['не обязательное', 'обязательное', 'почта', 'телефон', 'текст']
						var actionTypeValue = ['', 'validate[required]', 'validate[required,custom[email]]', 'validate[required,custom[phone]]', 'validate[required,maxSize[1000]]']
						var option = ''
						for (var i=0; i<actionType.length; i++) {
							if (actionTypeValue[i] != key)
								option += '<option value="'+actionTypeValue[i]+'">'+actionType[i]+ '</option>';
							else
								option += '<option value="'+actionTypeValue[i]+'" selected >'+actionType[i]+ '</option>';					
						}
						var htmlPage = '<li>';
						htmlPage += 'Название: <input type="text" name="key" value="'+value+'"> ';
						htmlPage += ' Тип: <select name="value">'+ option + '</select>';
						htmlPage += ' Тип формы: <select name="field">'+ fieldOption + '</select>';
						htmlPage += ' Css class: <input type="text" name="class" value="'+cssClass+'"> ';
						htmlPage += ' <a href="#" onclick="$(this).parent().remove()">[x]</a>';
						htmlPage += '</li>';
						return htmlPage;						
					}
					// новая форма
					function newFields() {
						var fieldType = ['строка', 'текстовое поле']
						var fieldValue = ['text', 'textarea']
						var fieldOption = ''
						for (var i=0; i<fieldType.length; i++) {
							fieldOption += '<option value="'+fieldValue[i]+'">'+fieldType[i]+ '</option>';
						}

						var actionType = ['не обязательное', 'обязательное', 'почта', 'телефон', 'текст']
						var actionTypeValue = ['', 'validate[required]', 'validate[required,custom[email]]', 'validate[required,custom[phone]]', 'validate[required,maxSize[1000]]']
						var option = ''
						for (var i=0; i<actionType.length; i++) {
							option += '<option value="'+actionTypeValue[i]+'">'+actionType[i]+ '</option>';
						}
						var htmlPage = '<li>';
						htmlPage += 'Название: <input type="text" name="key" value=""> ';
						htmlPage += ' Тип: <select name="value">'+ option + '</select>';
						htmlPage += ' Тип формы: <select name="field">'+ fieldOption + '</select>';
						htmlPage += ' Css class: <input type="text" name="class" value=""> ';
						htmlPage += ' <a href="#" onclick="$(this).parent().remove()">[x]</a>';
						htmlPage += '</li>';
						return htmlPage;						
					}
					self._postJSON('/admin-contact/get_text.html',{id:obj.id},function(data){
						// создаем каркас для полей
						if (data['key'].length == 1 && data['key'][0] == null) {
							var html = $('<form id="contact-form"><ul class="contact-block">'+newFields()+'</ul></form>');
						} else {
							var html = $('<form id="contact-form"><ul class="contact-block"></ul></form>');
						}

						self.frame = $('<div></div>');
						self.editWindowPrefs.title = "Редактирование формы";					
						self.editWindowPrefs.buttons = {
							'Сохранить': function() {
								$('.contact-block li').each(function() {
								    if ($(this).html() == "") {
								    	$(this).remove();
								    }
								});							
								self._postJSON('/admin-contact/update.html',{id:obj.id, content:$('#contact-form').serialize()});
								self.frame.remove();
								$(this).dialog('close');
							},
							'Отмена': function() {
								$(this).dialog('close');
							},
							'Добавить поле': function() {
								$(".contact-block").append('<li>'+newFields()+'</li>');
							}
						};
						self.frame.dialog(self.editWindowPrefs);
						html.appendTo(self.frame);		
						// если есть данные выводим
						if (data['key'].length > 0  && data['key'][0] != null) {
							for(var i=0; i< data['key'].length; i++) {
								$(".contact-block").append('<li>'+addFields(data['key'][i], data['value'][i], data['type'][i], data['class'][i])+'</li>');
							}			
						}
					});
				break;	
				case "remove":
						var frame = $('<div><p class="alert">Вы действительно хотите удалить форму?</p></div>');
						self.alertPrefs.title = "Удаление",
						self.alertPrefs.buttons = {
							'Удалить': function() {
								self._postJSON('/admin-contact/remove.html',{id:obj.id},function(){ 
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
								self._postJSON('/admin-contact/rename.html',{id:obj.id,name:str});
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
		this._postJSON('/admin-contact/getnames.html',{id:self.params},function(data){
			for (var i=0; i < self.params.length; i++) {
				id = self.params[i];
				if (id == 0) {
					self.workView.navigation('option','push',{name:"Формы",link:0});
				} else {
					self.workView.navigation('option','push',{name:data[i].name,link:id});
				}
			};			
		},false);
		this._postJSON('/admin-contact/getall.html',{id:self.parent_id},function(data){
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

$.extend($.ui.admincontact, {
	version: "2.0.0",
	defaults: {

	}
});

})(jQuery);
