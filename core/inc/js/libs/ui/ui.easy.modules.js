(function($) {

$.widget("ui.modules", {

	_init: function() {
		var self = this;
		this.workView = this.element;
		this.workView.navigation();
		this.parent_id = 0;
		this.params = [0];
		this.workView.navigation('option','buttonMenu',[
			{name:"Создать модуль",action:"create",inactive:false}
		]);
		
		this.workView.tableView({sortable:this.options.modal});
		this.workView.tableView('option','addClass','modules');
			

		// Обрабатываем кнопку +
		self.workView.bind('navContextAction',function(e,action){
			switch(action.split('/')[0]){
				case "create":
					self._postJSON('/admin-modules/create.html',{},function(data){
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
				case "link":
					$('<div class="fav"></div>').appendTo($('li',self.workView).eq(obj.index));
				break;
				case "unlink":
					$('.fav',$('li',self.workView).eq(obj.index)).remove();
				break;
				case "edit":
					self._postJSON('/admin-modules/get.html',{id:obj.id},function(data){
						var html = $('<fieldset><label for="name">Путь к файлу:</label><input type="text" id="name" value="'+data.name+'" /></fieldset>');
						self.frame = $('<div></div>');
						self.oneInputPrefs.title = "Редактирование пути";					
						self.oneInputPrefs.buttons = {
							'Сохранить': function() {
								self._postJSON('/admin-modules/update.html',{id:obj.id, name:$('#name',html).val()},function(data){
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
						self.oneInputPrefs.close = function() {self.frame.remove()}
						self.frame.dialog(self.oneInputPrefs);
						html.appendTo(self.frame);
					});
				break;	
				case "remove":
						var frame = $('<div><p class="alert">Вы действительно хотите удалить модуль?</p></div>');
						self.alertPrefs.title = "Удаление",
						self.alertPrefs.buttons = {
							'Удалить': function() {
								self._postJSON('/admin-modules/remove.html',{id:obj.id},function(){ 
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
								self._postJSON('/admin-modules/update.html',{id:obj.id,description:str});
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
		this.workView.tableView('clear');
		if (this.options.modal)
		this._postJSON('/admin-modules/getallm.html',{id:this.options.id},function(data){
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
		else
		this._postJSON('/admin-modules/getall.html',{},function(data){
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

$.extend($.ui.modules, {
	version: "2.0.0",
	defaults: {
		modal:false,
		id:0
	}
});

})(jQuery);
