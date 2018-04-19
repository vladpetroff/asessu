(function($) {

$.widget("ui.blank", {
	//Функция инициализации
	_init: function() {
		//Сыылка на себя
		var self = this;
		this.workView = this.element;
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

	},
	destroy: function() {
		$.widget.prototype.destroy.apply(this, arguments);
	}
});

$.extend($.ui.blank, {
	version: "2.0.0",
	defaults: {

	}
});

})(jQuery);
