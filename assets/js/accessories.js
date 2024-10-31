jQuery(function($){
	//checkbox dropoff
	$(document).on('change','.check_acc',function(){
		var acc = $(this).attr('id');
		var value = $(this).is(':checked');
		$.ajax({
			type: 'POST',
			url: ajax.url,
			data: {
				'nonce': ajax.nonce,
				'action':'rpdb_set_accessory',
				'acc': acc,
				'value': value,
			},
			success: function (result) {
				$(document.body).trigger('update_checkout');
			}
		});
	});
});

function rpdb_mondayEasterDate(y) // Takes a given year (y) then returns Date object of Easter Sunday
{ 

	y = Math.floor( y );
	var c = Math.floor( y / 100 );
	var n = y - 19 * Math.floor( y / 19 );
	var k = Math.floor( ( c - 17 ) / 25 );
	var i = c - Math.floor( c / 4 ) - Math.floor( ( c - k ) / 3 ) + 19 * n + 15;
	i = i - 30 * Math.floor( i / 30 );
	i = i - Math.floor( i / 28 ) * ( 1 - Math.floor( i / 28 ) * Math.floor( 29 / ( i + 1 ) ) * Math.floor( ( 21 - n ) / 11 ) );
	var j = y + Math.floor( y / 4 ) + i + 2 - c + Math.floor( c / 4 );
	j = j - 7 * Math.floor( j / 7 );
	var l = i - j;
	var m = 3 + Math.floor( ( l + 40 ) / 44 );
	var d = l + 28 - 31 * Math.floor( m / 4 );
	var z = new Date();
	z.setFullYear( y, m-1, d );
	z.setDate(z.getDate() + 1);
	return z;
}

function rpdb_validateApp(value)
{
	var weekday = (new Date(value)).getDay();
  	var day = (new Date(value)).getDate();
  	var month = (new Date(value)).getMonth();
  	var year = (new Date(value)).getFullYear();
	var easterDateY = rpdb_mondayEasterDate(year);
	var easterDateY_day = easterDateY.getDate();
	var easterDateY_month = easterDateY.getMonth();
	var easterDateY_year = easterDateY.getFullYear();
	
	if (year == easterDateY_year && month == easterDateY_month && easterDateY_day == day) { return ''; }
  	if (weekday==0 || weekday==6) { return ''; } //weekend
	if (day==25 && month == 3) { return ''; } // 25 aprile
	if (day==6 && month == 0) { return ''; } // 6 gennaio
	if (day==1 && (month == 0 || month == 4 || month == 10)) { return ''; } // 1 gennaio,maggio,novembre
	if (day==2 && month == 5) { return ''; } // 2 giugno
	if ((day==8 || day==25 || day==26) && month == 11) { return ''; } // 8,25,26 dicembre
	if (day==15 && month == 7) { return ''; } // 15 agosto
	
  	return value;
}

jQuery(function($){
	$(document).on('change','#ScheduledDelivery_type',function(e){
		var value = rpdb_validateApp($(this).val());
		$(this).val(value);
	});
});