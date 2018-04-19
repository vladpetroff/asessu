(function($) {

$.widget("ui.navigation", {

	_init: function() {
		var self = this;
		this.i = 0;
		this.navArr = [];
		this.panel = $('<div class="navigation-bar"></div>').appendTo(this.element);
		this.element.addClass('navigation');
		this.navPanel = $('<div class="nav-panel"></div>').appendTo(this.panel);
		if (this.options.addbutton) {
			this.addButton = $('<a href="#" class="addButton">+</a>').appendTo(this.panel);
			this.addButton.click(function(e){
				self.panel.contextmenu({
					top:self.panel.offset().top+18, 
					left:self.panel.offset().left+35,
					menu:self.buttonMenu,
					width:250
				});
				return false;
			});			
		};

		this.panel.bind('contextAction',function(e, data){
			self.element.trigger('navContextAction',data);
		});
	},
	
	makeNav:function(){
		var nav = [];
		var link = [];
		var self = this;
		var url = window.location.href.split('#')[1].split('/')[0];
		if (this.options.addarr) {
			for (var i=0; i < this.navArr.length; i++) {	
				var item = this.navArr[i];
				link[i] = item.link;
				var y = i+1;
				if (y == this.navArr.length) {
					nav[i] = '<span>'+item.name+'</span>';
				} else {
					nav[i] = '<a href="#'+url+'/'+link.join('/')+'">'+item.name+'</a>';
				}
			};
			this.navPanel.html(nav.join('<span class="arr"></span>'));
		}
		else {
			for (var i=0; i < this.navArr.length; i++) {	
				var item = this.navArr[i];
				if (item.cur) {
					nav[i] = '<a class="cur" href="#'+url+'/'+item.link+'">'+item.name+'</a>';
				} else {
					nav[i] = '<a href="#'+url+'/'+item.link+'">'+item.name+'</a>';
				}
			};
			this.navPanel.html('<span>Страницы:&nbsp;</span>'+nav.join('<span>&nbsp;|&nbsp;</span>'));			
		}
		$('a',this.navPanel).bind('click',function(){
			self.element.trigger('navigationClick',this.href);
			return false;
		});
	},
	
	clear: function(){
		this.navPanel.html('');
		this.navArr = [];

		this.i = 0;
	},
	
	_setData: function(key, value){
		switch (key) {
			case 'buttonMenu':
				this.buttonMenu = value;	
				break;
			case 'push':
				this.navArr[this.i] = value;
				this.makeNav();
				this.i++;
				break;
		}

		$.widget.prototype._setData.apply(this, arguments);
	},
	
	destroy: function() {
		this.panel.contextmenu('destroy');
		this.panel.remove();
		this.element.removeClass('navigation');
		$.widget.prototype.destroy.apply(this, arguments);
	}
});

$.extend($.ui.navigation, {
	version: "2.0.1",
	defaults: {
		addbutton:true,
		addarr:true
	}
});

})(jQuery);
