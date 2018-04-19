(function($) {

$.widget("ui.adminsettings", {

	_init: function() {
		var self = this;
		this.workView = this.element;

		this.workView.navigation();
		this.workView.navigation('option','buttonMenu',[
			{name:"Добавить опцию",action:"add-option",inactive:false}
		]);

		this.workView.tableView();
		this.workView.tableView('option','addClass','settings');	


		self.workView.bind('navContextAction',function(e,action){
			switch(action.split('/')[0]){
				case "add-option":
					self._postJSON('/admin-settings/add-option.html',{id:0},function(data){
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
				self._postJSON('/admin-settings/get_text.html',{id:obj.id},function(data){
					var html = $('<fieldset><label for="description">Описание:</label><input type="text" id="description" value="'+data.description+'" /><label for="name">Название:</label><input type="text" id="name" value="'+data.name+'" /><label for="text">Значение:</label><textarea id="text">'+data.html+'</textarea></fieldset>');
					self.frame = $('<div></div>');
					self.editWindowPrefs.title = "Редактирование настройки";					
					self.editWindowPrefs.buttons = {
						'Сохранить': function() {
							self._postJSON('/admin-settings/update.html',{id:obj.id, name:$('#name',html).val(), description:$('#description',html).val(),value:$('#text',html).val()},function(data){
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
					self.editWindowPrefs.close = function() {self.frame.remove()}
					self.frame.dialog(self.editWindowPrefs);
					html.appendTo(self.frame);
				});
				break;			
				case "remove":
						var frame = $('<div><p class="alert">Вы действительно хотите удалить опцию?</p></div>');
						self.alertPrefs.title = "Удаление",
						self.alertPrefs.buttons = {
							'Удалить': function() {
								self._postJSON('/admin-settings/remove.html',{id:obj.id},function(){ 
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
			}
		});		
		this.make();
		this.workView.spinner('destroy');
	},
	editWindowPrefs:{
		modal: true,
		width:900,
		minWidth:750,
		height:488,
		minHeight: 488,
		maxHeight: 488,
		overlay: {backgroundColor: '#000',opacity: 0.5}
	},
	editShortWindowPrefs:{
		modal: true,
		width:450,
		height:150,
		resizable: false,
		overlay: {backgroundColor: '#000',opacity: 0.5}
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
		this._postJSON('/admin-settings/getall.html',function(data){
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
		});
	},
	destroy: function() {
		this.element.tableView('destroy');
		this.workView.navigation('destroy');
		$.widget.prototype.destroy.apply(this, arguments);
	}
});

$.extend($.ui.adminsettings, {
	version: "2.0.0",
	defaults: {

	}
});

})(jQuery);
