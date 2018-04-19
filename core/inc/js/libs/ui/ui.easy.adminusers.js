(function($) {

$.widget("ui.adminusers", {

	//инициализация
	_init: function() {
		var self = this;
		var grarr = {};
		$.post('/admin-users/get_groups',{id:0},function(data){grarr = data;},'json');

		this.workView = this.element;
		this.workView.navigation();
		this.workView.tableView({sortable:false});
		this.params = [0];
		this.workView.navigation('option','buttonMenu',[
			{name:"Добавить пользователя", action:"createItem", inactive:false}
		]);
		this.workView.tableView('option','addClass','catalog');

		// Обрабатываем кнопку +
		self.workView.bind('navContextAction',function(e,action){
			var act = action.split('/')[0];
			if (act == 'createItem'){
				self._postJSON('/admin-users/create_item.html',{
						id:self.params[self.params.length - 1]
					},
					function(data){
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
					}
				);					
			}
		});

		// обрабатываем клик по ссылкам в элементе
		
		self.workView.bind('tableAClick',function(e,obj){
			var placeholder = $(obj.element).html();
			if (obj.element.className == 'ulogin'){
				self.frame = $('<div><fieldset><label>Введите логин:</label><input name="ulogin" type="text" value="'+placeholder+'" /></fieldset></div>');
				self.oneInputPrefs.title = "Редактирование логина";
				self.oneInputPrefs.open = function(event, ui) {$('[name=ulogin]',self.frame).focus().select()}
				self.oneInputPrefs.buttons['Сохранить'] = function() {
					self._postJSON('/admin-users/update.html',{id:obj.id,login:$('[name=ulogin]',self.frame).val()},function(data){
						obj.html = data.html;
						self.workView.tableView('option','editRowHTML',obj);
					});
					$(this).dialog('close');
				}
				self.oneInputPrefs.close = function(){self.frame.remove()}
			} else {
				var groupselect = '<select name="ugroup">';
				for (var gr in grarr){
					selected =  (grarr[gr] == placeholder)? ' selected="selected" ':'';
					groupselect += '<option value="'+gr+'" '+selected+'>'+grarr[gr]+'</option>';
				}
				groupselect += '</select>';
				self.frame = $('<div><fieldset><label>Выберите группу:</label>'+groupselect+'</fieldset></div>');
				self.oneInputPrefs.title = "Выбор группы для пользователя";
				self.oneInputPrefs.open = function(event, ui) { 
					$('[name=ugroup]',self.frame).focus().select();				
				}
				self.oneInputPrefs.buttons['Сохранить'] = function() {
					self._postJSON('/admin-users/update.html',{id:obj.id,group_id:parseInt($('[name=ugroup]',self.frame).val())},function(data){
						obj.html = data.html;
						self.workView.tableView('option','editRowHTML',obj);
					});
					$(this).dialog('close');
				}
				self.oneInputPrefs.close = function(){self.frame.remove()}
			}
			self.frame.dialog(self.oneInputPrefs);
		});

		//Обрабатываем контекстное меню
		self.workView.bind('tableContextAction',function(e,obj){
			var action = obj.action.split('/')[0];

			switch (action){
				case "remove":
						self.alertPrefs.title = "Удаление пользователя";
						var frame = $('<div><p class="alert">Вы действительно хотите удалить пользователя?</p></div>');
						self.alertPrefs.buttons = {
							'Удалить': function() {
								self._postJSON('/admin-users/remove.html',{id:obj.id});
								self.workView.tableView('option','removeRow',obj.index);
								$(this).dialog('close');
							},
							'Отмена': function() {
								$(this).dialog('close');
							}
						};
						self.alertPrefs.close = function() {frame.remove()}
						frame.dialog(self.alertPrefs);					
				break;

				case "editname":
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
								self.workView.spinner();
								var str = $(this).val();
								var input = $(this);
								$.post('/admin-users/update.html',{id:obj.id,nickname:str},function(data){
									self.workView.spinner('destroy');
									h1.html(str).show();
									input.remove();
								});
							}
								
						})
						.blur(function(){
							$(this).remove();
							h1.show();
						});
				break;

				case "editpass":
					self.frame = $('<div><fieldset><label>Введите новый пароль:</label><input name="upass" type="password"/></fieldset></div>');
					self.oneInputPrefs.title = "Изменение пароля";
					self.oneInputPrefs.open = function(event, ui) { 
						$('[name=upass]',self.frame).focus().select();				
					}
					self.oneInputPrefs.buttons['Сохранить'] = function() {
						self._postJSON('/admin-users/updatepass.html',{id:obj.id,pass:$('[name=upass]',self.frame).val()},function(data){
							obj.html = data.html;
							self.workView.tableView('option','editRowHTML',obj);
						});
						$(this).dialog('close');
					}
					self.oneInputPrefs.close = function(){self.frame.remove()}
					self.frame.dialog(self.oneInputPrefs);
				break;
			}
		});		
		this.make();
		this.workView.spinner('destroy');
	},

	params:[0],

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
		}
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
		}
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
		this._postJSON('/admin-users/getall.html',{id:0},function(data){
			for (var i=0; i < data.object.length; i++) {
				self.workView.tableView('option','newRow',{
					html:data.html[i],
					info:{
						id:data.object[i].id,
						name:data.object[i].login,
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

$.extend($.ui.adminusers, {
	version: "1.0",
	defaults: {}
});

})(jQuery);