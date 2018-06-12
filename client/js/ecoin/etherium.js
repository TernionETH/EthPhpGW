var wallet='0x0';

function ch_trigger(oShow,oHide){
	$(oShow).show();
	$(oHide).hide();
	return false;
}

$(document).ready(function() {
	$('#wallet_number_upload').hide();
	$('#wallet_file_number').show();
	$('#buy_coins_section').hide();
	
	$('#have_wallet_number').on('click',function(){
		if($(this).is(':checked')){
			ch_trigger($('#wallet_number_field'),$('#wallet_number_upload'));
			$('#have_wallet_file').prop('checked',false);
		}else{
			ch_trigger($('#wallet_number_upload'),$('#wallet_number_field'));
			$('#have_wallet_file').prop('checked',true);
		}
		return true;
	});

	$('#have_wallet_file').on('click',function(){
		if($(this).is(':checked')){
			ch_trigger($('#wallet_number_upload'),$('#wallet_number_field'));
			$('#have_wallet_number').prop('checked',false);
		}else{
			ch_trigger($('#wallet_number_field'),$('#wallet_number_upload'));
			$('#have_wallet_number').prop('checked',true);
		}
		return true;
	});

	
	
	$('.btn-submit').mcf('bind','submit_element',{
		'_':{
			'closest':'form',
			'setup':'get_mandatory_fields',
			'setup_args':{'_mf':'_mf'},
			'callbefore':'',
			'callback':'processing_callback_result'
			}
		}
	);
	
	
	
	
	
});


function move_to_wallet_info(_args,_obj,_idm){
	var newURL = window.location.protocol + "//" + window.location.host + "/wallet_info.html";
	window.location.href = newURL;
	window.navigate(newURL);
	return false;
}

function move_to_buy_coins(_args,_obj,_idm){
	var newURL = window.location.protocol + "//" + window.location.host + "/buy_coins.html";
	window.location.href = newURL;
	window.navigate(newURL);
	return false;
}


/* Collect data for send step 1*/
function get_mandatory_fields(args,_obj){
	$('._err').each(function(){$(this).removeClass('_err');});
	var _mf=[];
	if(typeof args['_mf'] != "undefined"){
		$('.'+args['_mf'],_obj).each(function(){
			var _n=$(this).prop('name');
			_mf.push(_n);
		});
	}
	return {'_mf':_mf};
}

/* work with return data*/
function processing_callback_result(_r){
	if(typeof _r._err != "undefined" && typeof _r._err._fields != "undefined"){
		var _c=_r._err._fields.length;
		for(var i=0;i<_c;i++){
			$("[name='"+_r._err._fields[i]+"']").each(function(){$(this).addClass('_err');});
		}
		$('._err').on('blur',function(){if($(this).val()!=''){$(this).removeClass('_err');}});
	}
	
	if(typeof _r.html != "undefined"){
		mcore_materialize_modal_show(_r.html);
	}
	
	if(typeof _r._data != "undefined"){
		if(typeof _r._data.balance != "undefined"){
			$('#current_balance').html(_r._data.balance);
		}
		
		
		if(typeof _r._data.wallet != "undefined"){
			$('#wallet_number').val(_r._data.wallet);
			wallet=_r._data.wallet;
			$('#wallet_to_by_coins').val(_r._data.wallet);
			$('#wallet_to_by_coins_text').html(_r._data.wallet);
			$('#buy_coins_section').show();
		}
	}
	
	return false;
}