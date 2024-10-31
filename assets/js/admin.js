jQuery(function($){
	
	$(document).on('change','input,select',function(){
		$('#check_input').val(1);
	});
	
	var submitted = false;
	$(window).bind('beforeunload', function(){
		var check_input = $('#check_input').val();
		if(check_input == 1 && !submitted){
			return 'Are you sure you want to leave?';
		}
	});
	$("form").submit(function() {
		submitted = true;
	});
	
	//API KEY
	$('#form_apikey th').css('display','none');
	$('#form_apikey td').attr('colspan',2);
	$('#reshark_pdb_apikey').css('width',300);
	$('#reshark_pdb_apikey').attr('placeholder','Inserisci qui la tua API KEY Reshark');
	$('#reshark_pdb_apikey').addClass($('#check_api_key').val());
	
	function show_hide_options(input_checkbox,is_checked){
		var id_checkbox = input_checkbox.attr('id');
		var header_div = $('.ui-accordion-header[for="'+id_checkbox+'"]');
		var content_div = $('.ui-accordion-content[for="'+id_checkbox+'"]');
		if(is_checked){
			content_div.find('tr:not(:first-child)').show();
			header_div.find('.dashicons').addClass('dashicons-yes-alt').removeClass('dashicons-dismiss');
		}else{
			content_div.find('tr:not(:first-child)').hide();
			header_div.find('.dashicons').removeClass('dashicons-yes-alt').addClass('dashicons-dismiss');
		}
	}
	
	//DROPOFF
	$(document).on('change','#reshark_pdb_dropoff',function(){
		var is_checked = $(this).is(':checked');
		show_hide_options($(this),is_checked);
	});
	$(document).ready(function(){
		var is_checked = $('#reshark_pdb_dropoff').is(':checked');
		show_hide_options($('#reshark_pdb_dropoff'),is_checked);
	});
	
	//ACCESSORY
	$(document).ready(function(){
		$('#reshark_accessories input[type="checkbox"]').each(function(){
			var is_checked = $(this).is(':checked');
			show_hide_options($(this),is_checked);
		});
	});
	$(document).on('change','#reshark_accessories input[type="checkbox"]',function(){
		var is_checked = $(this).is(':checked');
		show_hide_options($(this),is_checked);
	});
	
	//INSURES
	$(document).ready(function(){
		$('#reshark_insures input[type="checkbox"]').each(function(){
			var is_checked = $(this).is(':checked');
			show_hide_options($(this),is_checked);
		});
	});
	$(document).on('change','#reshark_insures input[type="checkbox"]',function(){
		var is_checked = $(this).is(':checked');
		show_hide_options($(this),is_checked);
	});
	
	//COST TYPE
	$(document).on('change','#form_dropoff select',function(){
		var val = $(this).val();
		var parent = $(this).parent();
		
		parent.find('small').hide();
		parent.find('small[for="'+val+'"]').show();
	});
	$(document).on('change','#reshark_accessories select',function(){
		var val = $(this).val();
		var parent = $(this).parent();
		
		parent.find('small').hide();
		parent.find('small[for="'+val+'"]').show();
	});
	$(document).on('change','#reshark_insures select',function(){
		var val = $(this).val();
		var parent = $(this).parent();
		
		parent.find('small').hide();
		parent.find('small[for="'+val+'"]').show();
	});
});