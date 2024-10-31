// POPUP
function rpdb_createMap(centerLat,centerLng,vectorLayer){
	const container = document.getElementById('popup');
	const content = document.getElementById('popup-content');
	const closer = document.getElementById('popup-closer');
	const overlay = new ol.Overlay({
		element: container,
	});
	closer.onclick = function() {
		overlay.setPosition(undefined);
		closer.blur();
		return false;
	};

	// CENTER
	var center = ol.proj.fromLonLat([centerLat,centerLng]);
	var view = new ol.View({
		center: center,
		zoom: 12
	});

	// MAP
	var map = new ol.Map({
		target: 'map',
		view: view,
		layers: [
			new ol.layer.Tile({
				preload: 3,
				source: new ol.source.OSM(),
			}),
			vectorLayer,
		],
		overlays: [overlay],
		loadTilesWhileAnimating: true,
	});

	map.on('click', function (evt) {
		const feature = map.forEachFeatureAtPixel(evt.pixel, function (feature) {
			return feature;
		});
		if(feature){
			const coordinates = feature.getGeometry().getCoordinates();
			content.innerHTML =
				'<p class="titolo"><strong>'+feature.get('title')+'</strong></p><p class="indirizzo">'+feature.get('addressName')+' '+feature.get('city')+' '+feature.get('zip')+' ('+feature.get('province')+')</p><p class="phone">'+feature.get('phone')+'</p><a href="#" data-code="'+feature.get('code')+'" data-title="'+feature.get('title')+'" data-address="'+feature.get('addressName')+'" data-city="'+feature.get('city')+'" data-zip="'+feature.get('zip')+'" data-province="'+feature.get('province')+'" class="select_dropoff_point button alt">Seleziona</a>'
				overlay.setPosition(coordinates);
		}
	});
	
	map.once('postrender',function(event) {
		const btn_dropoff_popup = document.getElementById('btn_dropoff_popup');
		btn_dropoff_popup.classList.add('enabled');
	});
}


jQuery(function($){
	//checkbox dropoff
	$(document).on('change','[name="dropoff"]',function(){
		var value = $('[name="dropoff"]').is(':checked');
		$.ajax({
			type: 'POST',
			url: ajax.url,
			data: {
				'nonce': ajax.nonce,
				'action':'rpdb_set_dropoff',
				'value': value,
			},
			success: function (result) {
				$(document.body).trigger('update_checkout');
				if(value){
					ajaxDropoffMap();
				}else{
					$('.dropoff_popup').html('<div id="map"></div><div id="popup" class="ol-popup"><a href="#" id="popup-closer" class="ol-popup-closer"></a><div id="popup-content"></div></div>');
				}
			}
		});
	});
	
	/* POPUP */
	function closeDropoffPopup(){
		$('body').removeClass('dropoff_popup_active');
		$('.dropoff_popup').removeClass('active');
	}
	$(document).on('click','#btn_dropoff_popup',function(e){
		e.preventDefault();
		if($(this).hasClass('enabled')){
			$('body').addClass('dropoff_popup_active');
			$('.dropoff_popup').addClass('active');
		}
	});
	$(document).click(function(event){ 
  		var $target = $(event.target);
		if(!$target.closest('#btn_dropoff_popup').length && !$target.closest('#dropoff_popup').length && $('#dropoff_popup').is(":visible")){
			closeDropoffPopup();
		}
	});
	
	function ajaxDropoffMap(){
		$('.dropoff_popup').html('<div id="map"></div><div id="popup" class="ol-popup"><a href="#" id="popup-closer" class="ol-popup-closer"></a><div id="popup-content"></div></div>');
		$.ajax({
			type: 'POST',
			url: ajax.url,
			data: {
				'nonce': ajax.nonce,
				'action':'rpdb_refresh_dropoff_map',
			},
			dataType: 'json',
    		cache: false,
			success: function(data){
				if(data === false){
					
				}else{
					var vectorSource = new ol.source.Vector({});
					var places = [];
					var dropoff_point = data; //response
					dropoff_point.forEach((point) => {
  						places.push([point['code'],point['latitude'],point['longitude'],point['marker'],point['description'],point['addressName'],point['city'],point['province'],point['zip'],point['phone']]);
					});
					
					var features = [];
					for(var i = 0; i < places.length; i++){
 						var iconFeature = new ol.Feature({
							geometry: new ol.geom.Point(ol.proj.transform([places[i][1], places[i][2]], 'EPSG:4326', 'EPSG:3857')),
							code: places[i][0],
							title: places[i][4],
							addressName: places[i][5],
							city: places[i][6],
							province: places[i][7],
							zip: places[i][8],
							phone: places[i][9],
						});

						var iconStyle = new ol.style.Style({
							image: new ol.style.Icon({
								anchor: [0.5, 0.5],
								anchorXUnits: 'fraction',
								anchorYUnits: 'fraction',
								src: places[i][3],
								width: 40,
								height: 50,
								crossOrigin: 'anonymous',
							})
						});
						iconFeature.setStyle(iconStyle);
						vectorSource.addFeature(iconFeature);
					}

					var vectorLayer = new ol.layer.Vector({
						source: vectorSource,
						updateWhileAnimating: true,
						updateWhileInteracting: true,
					});
					
					rpdb_createMap(places[0][1],places[0][2],vectorLayer);
				}
			}
		});
	}
	
	$(document.body).on('updated_checkout',function(){
		if($('[name="dropoff"]').length && $('[name="dropoff"]').is(':checked')){
			setTimeout(function(){
				ajaxDropoffMap();
			},1000);
		}
	});
	
	
	// Select dropoff point
	$(document).on('click','.select_dropoff_point',function(e){
		e.preventDefault();
		var code = $(this).attr('data-code');
		var title = $(this).attr('data-title');
		var address = $(this).attr('data-address');
		var city = $(this).attr('data-city');
		var zip = $(this).attr('data-zip');
		var province = $(this).attr('data-province');
		$.ajax({
			type: 'POST',
			url: ajax.url,
			data: {
				'nonce': ajax.nonce,
				'action':'rpdb_set_dropoff_point',
				'code': code,
				'title': title,
				'address': address,
				'city': city,
				'zip': zip,
				'province': province,
			},
			success: function (result) {
				closeDropoffPopup();
				window.location.href = window.location.href;
				//jQuery(document.body).trigger('update_checkout');
			},
		});
	});
});
