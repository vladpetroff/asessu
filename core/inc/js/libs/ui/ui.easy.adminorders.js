(function($) {

$.widget("ui.adminorders", {

	_init: function() {
		var self = this;
		this.workView = this.element;
		this.workView.tableView();
		this.workView.tableView('option','addClass','orders');	
		// обрабатываем даблклик по элементу дерева
		self.workView.bind('tableDblclick',function(e,obj){
			self._postJSON('/admin-orders/get_text.html',{id:obj.id},function(html){
				self.frame = $('<div class="order"></div>');
				self.editWindowPrefs.title = "Просмотр заказа.";					
				self.editWindowPrefs.buttons = {
					'Закрыть': function() {
						self.frame.remove(); $(this).dialog('close');
					}
				};
				self.editWindowPrefs.close = function() {self.frame.remove()}
				self.frame.dialog(self.editWindowPrefs);
				$(html).appendTo(self.frame);
			});
		});
		//Обрабатываем контекстное меню
		self.workView.bind('tableContextAction',function(e,obj){
			var action = obj.action.split('/')[0];
			switch (action){
				case "open":
					self._postJSON('/admin-orders/get_text.html',{id:obj.id},function(html){
						self.frame = $('<div class="order"></div>');
						self.editWindowPrefs.title = "Просмотр заказа.";					
						self.editWindowPrefs.buttons = {
							'Закрыть': function() {
								self.frame.remove(); $(this).dialog('close');
							}
						};
						self.editWindowPrefs.close = function() {self.frame.remove()}
						self.frame.dialog(self.editWindowPrefs);
						$(html).appendTo(self.frame);
					});
				break;
				case "remove":
						var frame = $('<div><p class="alert">Вы действительно хотите удалить заказ из истории?</p></div>');
						self.alertPrefs.title = "Удаление",
						self.alertPrefs.buttons = {
							'Удалить': function() {
								self._postJSON('/admin-orders/remove.html',{id:obj.id},function(){ 
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
		width:600,
		minWidth:750,
		height:488,
		minHeight:488,
		maxHeight:488,
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
		this._postJSON('/admin-orders/getall.html',function(data){
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
		$.widget.prototype.destroy.apply(this, arguments);
	}
});

$.extend($.ui.adminorders, {
	version: "2.0.0",
	defaults: {

	}
});

})(jQuery);
