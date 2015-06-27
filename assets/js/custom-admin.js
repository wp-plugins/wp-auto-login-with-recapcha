(function(neil){
	neil(document).ready(function(){
		var recapElem = neil('#recapcha'),
			sitekeyElem = neil('tr.recapcha-text'),
			autoLoginElem = neil('#auto-login'),
			redirectElem = neil('tr.redirect-url');

		if(recapElem.length > 0)
		{
			recapElem.change(function(){
				var status = recapElem.find(":selected").val();
				status == 'Y' ? sitekeyElem.fadeIn(1000) : sitekeyElem.fadeOut(1000);
			});
		}

		if(autoLoginElem.length > 0)
		{
			autoLoginElem.change(function(){
				var status = autoLoginElem.find(":selected").val();
				status == 'Y' ? redirectElem.fadeIn(1000) : redirectElem.fadeOut(1000);
			});

		}
		redirect-url
	});
	
})(jQuery);