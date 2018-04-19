(function($) {

$.widget("ui.menu", {

	_init: function() {
		var self = this;
		this.menu = this.create().appendTo(this.element);
		$('a',this.menu).click(function(){
			self.select(this);
			self.element.trigger('changepage',this.href);
			return false;
		});
	},
	
	select: function(el){
		$('li.selected',this.menu).removeClass('selected');
		$(el).parent().addClass('selected');

		//[k] подсказки в левом нижнем углу - минихелп
		if ($('#addt').length == 0) {
			$('.side-bar').append('<div id="addt"></div>');
		}
		var addtext = '';
		switch (($(el).attr('class'))) {
			case 'menu-catalog':
			case 'menu-images':
				addtext = '';
			break;
			default: ;
		}
		$('#addt').html(addtext);				
	},
	
	create: function(){
		var html = '<ul class="menu">';
		for (var i=0; i < this.options.menu.length; i++) {
			if ( this.options.menu[i].items.length > 0) {
				html+='<li><span class="menu-header">'+this.options.menu[i].name+'</span><ul class="submenu">';
				for (var y=0; y < this.options.menu[i].items.length; y++) {
					var item = this.options.menu[i].items[y];
					html+='<li><a href="#'+item.link+'" class="'+item.className+'">'+item.name+'</a></li>';
				};
				html+='</ul></li>';				
			};
		};
		html+='</ul>';
		return $(html);
	},

	destroy: function() {
		this.menu.remove();
		$.widget.prototype.destroy.apply(this, arguments);
	}
});

$.extend($.ui.menu, {
	version: "2.0.0",
	defaults: {
		menu:[
		]
	}
});

})(jQuery);
