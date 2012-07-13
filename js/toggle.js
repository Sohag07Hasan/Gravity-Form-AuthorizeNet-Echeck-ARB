jQuery(document).ready(function($){
	$('.arb-toggle-command').change(function(){
		var arb = $(this).val();
		if(arb > 0){
			$('.arb-interval-dependent').show();
		}
		else{
			$('.arb-interval-dependent').hide();
		}
		return false;
	});
});