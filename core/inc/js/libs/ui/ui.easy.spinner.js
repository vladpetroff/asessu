(function($) {

$.widget("ui.spinner", {

	_init: function() {
		this.spinner = $('<div class="easy-spinner"></div>').appendTo(this.element);
	},
	destroy: function() {
		this.spinner.remove();
		$.widget.prototype.destroy.apply(this, arguments);
	}
});

$.extend($.ui.spinner, {
	version: "2.0.0",
	defaults: {

	}
});

})(jQuery);
