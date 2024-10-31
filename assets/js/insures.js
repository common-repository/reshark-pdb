jQuery(function($){
	//checkbox dropoff
	$(document).on('change','.check_ins',function(){
		var ins = $(this).attr('id');
		var value = $(this).is(':checked');
		$('.check_ins').prop('checked',false);
		$(this).prop('checked',value);
		$.ajax({
			type: 'POST',
			url: ajax.url,
			data: {
				'nonce': ajax.nonce,
				'action':'rpdb_set_insure',
				'ins': ins,
				'value': value,
			},
			success: function (result) {
				$(document.body).trigger('update_checkout');
			}
		});
	});
});