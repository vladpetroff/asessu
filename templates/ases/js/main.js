$(document).ready(function(){

	$("#feedback").validationEngine();
	
	$(".various").fancybox({
				"frameWidth" : '700px',
				"frameHeight" : '600px',
				'titlePosition'		: 'inside',
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'padding' : '20',
				"imageScale" : 'false', 
				"zoomOpacity" : 'false',
				"zoomSpeedIn" : '1000',
				"zoomSpeedOut" : '1000',
				"zoomSpeedChange" : '1000',
				"overlayShow" : 'true',
				"overlayOpacity" : '0.8',
				"hideOnContentClick" :'false',
				"centerOnScroll" : 'false'

	});


    var cont_height = $('.container_24').height();
    var footer_height = $('#footer').height();
    var wrap_sidebg_height = cont_height - footer_height ;
    $('#wrap_sidebg').height(wrap_sidebg_height);

	 $('a.feedback_open').click(function(){
	 	$('div.feedback').toggle("slow", function() {
	 		$('div.feedback_side').toggle('slow');
	 		$('body').css("overflow","hidden");
	 	});
	 	return false;	
	 })
	 $('a.feedback_close').click(function(){
	 	$('div.feedback').toggle("slow", function() {
	 		$('div.feedback_side').toggle('slow');
	 		$('body').css("overflow","auto");
	 		$('.formError').remove();
	 	});	
	 	return false;
	 })

	url = window.location.href;
	if (url.indexOf('#inline') > 0) {
		url = url.split('#');
		$("[href='#"+url[1]+"']").click();
	}

	$('#product_id').change(function() {
  		$('#form_products').submit();
	});

})

