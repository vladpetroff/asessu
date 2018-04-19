(function($) {

$.widget("ui.testcatalog", {
	//Функция инициализации
	_init: function() {
		//Сыылка на себя
		var self = this;
		this.workView = this.element;
		//Добавляем навигацию
		this.workView.navigation();
		//Добавим меню к кнопке +
		this.workView.navigation('option','buttonMenu',[
			{name:"Добавить товар",action:"createItem",inactive:false},
			{name:"Создать каталог",action:"createDir",inactive:false}
		]);
		//По-умолцанию радительский id = 0
		this.parent_id = 0;
		this.params = [0];
		// Добавляем рабочую область
		this.workView.tableView({sortable:true});
		this.workView.tableView('option','addClass','catalog');

		//Вызываем функцию создания 
		this.make();
		//Убираем индикатор загрузки
		this.workView.spinner('destroy');
	},

	//Предустановки для окна с редактром
	editWindowPrefs:{
		modal: true,
		width:900,
		minWidth:750,
		height:488,
		minHeight:488,
		maxHeight:488,
		overlay: {backgroundColor: '#000',opacity: 0.5}
	},
	//Предустановки для окна с выбором изображений
	imagesWindowPrefs:{
		modal: true,
		width:940,
		height:550,
		resizable:false,
		overlay: {backgroundColor: '#000',opacity: 0.5}
	},
	//Предустановки для окна с одним редактируемым полем
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
	//Предустановки для Сообщения 
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
	//Обертка для $.post() c типом данных JSON, а также включающая индикатор загрузки
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
	//Функция заполнения рабочей части
	make:function(){
		var self = this;
		// Если ф-ция make() вызывается из _init() - то this.parent_id = 0
		if (this.parent_id == 0){
			// берем из URL правую часть, после хэша(#)
			var url = window.location.href.split("#")[1];
			if (url.split('/').length > 1) {
				// Если в URL есть параметры, то разбиваем строку по(/) и берем последний параметр
				this.parent_id = url.split('/')[url.split('/').length - 1];
			} else {
				// Или падисываем в URL параметр по-умолчанию
				window.location.href+='/0';
			}
			// Заполняем массив this.params
			var tmp = window.location.href.split("#")[1];
			this.params = [];
			tmp = tmp.split('/');
			for (var i=1; i < tmp.length; i++) {
				this.params[i-1] = tmp[i];
			};
			// Очищаем Рабочую область
			this.workView.tableView('clear');
			// Очищаем Навигацию
			this.workView.navigation('clear');
			// Строим навигацию
			this._postJSON('/admin-catalog/getnames.html',{id:self.params},function(data){
				for (var i=0; i < self.params.length; i++) {
					id = self.params[i];
					if (id == 0) {
						// Если id=0 - то это корневой каталог и базе такого нет
						// По-этому пишем вручную имя
						self.workView.navigation('option','push',{name:"Каталог",link:0});
					} else {
						// Или добавляем результат из базы
						self.workView.navigation('option','push',{name:data[i].name,link:id});
					}
				};			
			},false); //false - что бы не выводился идникатор.
			this._postJSON('/admin-catalog/getall.html',{id:self.parent_id},function(data){
				for (var i=0; i < data.object.length; i++) {
					// Добавляем новую строку
					self.workView.tableView('option','newRow',{
						html:data.html[i], //HTML часть строки
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
		}		
	},
	destroy: function() {
		$.widget.prototype.destroy.apply(this, arguments);
	}
});

$.extend($.ui.testcatalog, {
	version: "2.0.0",
	defaults: {

	}
});

})(jQuery);
