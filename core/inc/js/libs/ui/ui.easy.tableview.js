(function($) {

$.widget("ui.tableView", {

	_init: function() {
		var self = this,
			odd	 = false;
		this.rowArr = [];
		this.i = 0;
		this.table = $('<ul class="table-view"></ul>').appendTo(this.element);
		this.table.wrap('<div class="table-wrap"></div>');
		this.tableWrap = $('.table-wrap',this.element);
		if (this.options.sortable) {
			this.table.sortable({
				axis:'y',
				items:'li',
				containment: this.wrap,
				update: function(){
					self.element.trigger('tableSortStop');			
				}
				});
		};
		
		if ($('.navigation-bar',this.element).length) {
			this.tableWrap.css({top:22});
			this.tableWrap.height(this.element.height() - 22);
			self.element.bind('resize',function(){
				self.tableWrap.height(self.element.height() - 22);
			});
		};

	},
	
	destroy: function() {
		this.table.remove();
		this.tableWrap.remove();
		$.widget.prototype.destroy.apply(this, arguments);
	},
	
	select: function(el){
		$('.selected',this.table).removeClass('selected');
		el.addClass('selected');
		$('input',this.table).blur();
		this.element.trigger('rowSelected',el);
	},
	
	clear:function(){
		this.i = 0;
		this.rowArr = [];
		$('li',this.table).unbind().remove();
		this.odd = false;
	},
	
	removeRow: function(index){
		// this.i--;
		// var arr = [],
		// 	y = 0;
		// for (var i=0; i < this.rowArr.length; i++) {
		// 	if (i != index) {
		// 		arr[y] = this.rowArr[i];
		// 		y++;
		// 	}
		// }
		// this.rowArr = arr;
		$('li',this.table).eq(index).hide();
	},
	
	addClass: function(clas){
		this.table.addClass(clas);
	},
	
	editContextMenu: function(obj){
		var self = this;

		if ($.browser.opera) {
			$('.opera-cmenu',this.rowArr[obj.info.index]).unbind('click');
			$('.opera-cmenu',this.rowArr[obj.info.index]).bind('click',{info:obj.info,menu:obj.menu,width:obj.width},function(e){
					//Контекстное меню
					var evt = e;
					$(this).contextmenu({
						top:evt.pageY -10, 
						left:evt.pageX -10,
						menu:evt.data.menu,
						width:evt.data.width
					});
					$(this).one('contextAction',function(e,action){
						evt.data.info.action = action;

						self.element.trigger('tableContextAction',evt.data.info);
					});
					return false;
				});			
		} else {
			this.rowArr[obj.info.index].unbind('mousedown')
			.bind('mousedown',{info:obj.info,menu:obj.menu,width:obj.width},function(e){
				//Контекстное меню
				var evt = e;
				$(this).data('data',evt.data);
				self.select($(this));
				$(this).mouseup( function() {
					yebaldata = $(this).data('data');
					$(this).unbind('mouseup');
					$(this).unbind('contextAction');
					if( evt.button == 2 ) {
						$(this).contextmenu({
							top:evt.pageY, 
							left:evt.pageX,
							menu:yebaldata.menu,
							width:yebaldata.width
						});
						$(this).one('contextAction',function(e,action){
							yebaldata.info.action = action;

							self.element.trigger('tableContextAction',yebaldata.info);
						});
						return false;
					} else {
						return true;
					}
				})[0].oncontextmenu = function() {
					return false;
				}
			});		
		}
		
	},
	
	editHTML:function(obj){
		var self = this;
		$('*',this.rowArr[obj.index]).not('.opera-cmenu').remove();
		$(obj.html).appendTo(this.rowArr[obj.index]);
		$('a',this.rowArr[obj.index]).bind('click',obj,function(e){
			e.data.element = this;
			self.element.trigger('tableAClick',e.data);
			return false;
		});
	},
	
	addRow: function(obj){
		var clas = (this.odd)?'odd':'';
		var self = this;
		obj.info.index = this.i;
		if ($.browser.opera) {
			var row = this.rowArr[this.i] = $('<li class="'+clas+' opera">'+obj.html+'<div class="opera-cmenu"></div></li>')
				.appendTo(this.table)
				.disableSelection()
				.bind('dblclick',obj.info,function(e){
					self.element.trigger('tableDblclick',e.data);
				}).bind('mousedown',{info:obj.info,menu:obj.menu,width:obj.width},function(e){
						self.select($(this));
				});
			$('.opera-cmenu',row).bind('click',{info:obj.info,menu:obj.menu,width:obj.width},function(e){
					//Контекстное меню
					var evt = e;
					$(this).contextmenu({
						top:evt.pageY -10, 
						left:evt.pageX -10,
						menu:evt.data.menu,
						width:evt.data.width
					});
					$(this).one('contextAction',function(e,action){
						evt.data.info.action = action;

						self.element.trigger('tableContextAction',evt.data.info);
					});
					return false;
				});			
		} else {
			var row = this.rowArr[this.i] = $('<li class="'+clas+'">'+obj.html+'</li>')
				.appendTo(this.table)
				.disableSelection()
				.bind('dblclick',obj.info,function(e){
					self.element.trigger('tableDblclick',e.data);
				}).bind('mousedown',{info:obj.info,menu:obj.menu,width:obj.width},function(e){
					//Контекстное меню
					var evt = e;
					$(this).data('data',evt.data);
					self.select($(this));
					$(this).mouseup( function() {
						yebaldata = $(this).data('data');
						$(this).unbind('mouseup');
						$(this).unbind('contextAction');
						//[k] если правая кнопка показать контекстное меню
						if( evt.button == 2 ) {
							$(this).contextmenu({
								top:evt.pageY -10, 
								left:evt.pageX -10,
								menu:yebaldata.menu,
								width:yebaldata.width
							});
							$(this).one('contextAction',function(e,action){
								yebaldata.info.action = action;

								self.element.trigger('tableContextAction',yebaldata.info);
							});
							return false;
						} else {
							return true;
						}
					})[0].oncontextmenu = function() {
										return false;
									}
				});			
		}

		$('a',row).bind('click',obj.info,function(e){
			e.data.element = this;
			self.element.trigger('tableAClick',e.data);
			return false;
		});
		this.odd = (this.odd)?false:true;
		this.i++;
		
	},
	
	_makeBinds: function(){
		
	},

	_setData: function(key, value){
		switch (key) {
			case 'newRow':
				this.addRow(value);	
			break;
			case 'editRowContextMenu':
				this.editContextMenu(value);	
			break;
			case 'editRowHTML':
				this.editHTML(value);
			break;
			case 'addClass':
				this.addClass(value);
			break;
			case "removeRow":
				this.removeRow(value);
			break;
				
		}

		$.widget.prototype._setData.apply(this, arguments);
	}
});

$.extend($.ui.tableView, {
	version: "2.0.0",
	defaults: {
		sortable: false
	}
});

})(jQuery);
