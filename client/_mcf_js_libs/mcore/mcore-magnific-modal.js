function _show_mw(html){
	var max=999999;
	var min=100000;
	var modal_id='modal-'+Math.floor(Math.random()*(max-min+1)+min);	
	var _w=$.magnificPopup.open({
		items: {
			src: html,
			type: 'inline'
		},
		key: modal_id,
		callbacks: {
			beforeOpen: function() {return false;},
			close: function() {
				delete $.magnificPopup.instance.popupsCache[this.st.key];
				return false;
			},
		},
	});
	$('.mfp-content').attr('id',modal_id);
	return modal_id;
}
 
function _mmcall(_obj,_fn,_args){
	var _co=$(_obj).closest('div._modal');
	var _idm=_co.prop('id'); //modal id
	if(!_idm){
		var max=999999;
		var min=100000;
		var _idm='modal-'+Math.floor(Math.random()*(max-min+1)+min);
		_co.attr('id',_idm);
	}
	if(typeof _fn === "function"){
		_fn(_args,_obj,_idm);
	}else if(typeof window[_fn] === "function"){
		window[_fn](_args,_obj,_idm);
	}
	return false;
}
function ModalClose(){
	$.magnificPopup.close();
	return false;
}