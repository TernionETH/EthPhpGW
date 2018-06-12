/*
$('form').mcf('bind','submit_form',{
	'a':'b',
	'_':{
		'callbefore':'check_login_invalid_fields',
		'callback':'show_auth_result',
		'setup':'call_test_function',
		'setup_args':{'_obj':1111}
	}
});
*/

if(typeof _mCoreV === undefined){var _mCoreV ={};}
var _mcore_rpc_options={
	"_lp":"",	//lang prefix  Not send to rpc
	"_rl":"en", //lang require
	"_rp":"",	//ini section name
	"_un":"",	//namespace /mcore
	"_ln":"",	//lang prefix + namespace /ru/mcore
	"_rpckey":"",
	"_callbefore":"", //rpc before send call. Not send to rpc
	"_callback":""	//rpc callback function Not send to rpc
};

(function($){
	window['_mCoreV']=$.extend(window['_mcore_rpc_options'],window['_mCoreV']);
	var defered_rpc_call={};
	var methods = {
		online : function() {
			if(typeof $(this).context == "undefined" || !isDomElem($(this))){
				var w_uuid=localStorage.getItem("_mcf_wuuid");
				if(w_uuid==null){w_uuid=mcore_generate_uuid();localStorage.setItem('_mcf_wuuid',w_uuid);}
				var _d=$.extend({'_rpckey':'#update','_rp':'online','w_uuid':w_uuid},$(this)[0]);
				return methods['_rpc_call'](_d,null);
			}
			return false
		},
		bind: function(evt,data){
			var _sel=$(this)['selector'];
			$(_sel).each(function(){
				jQuery.removeData($(this),'_mcf_init');
				if(data){$(this).data('_mcf_init',data);}
				switch(evt){
					case 'submit_element':	$(this).bind("click",data,methods['submit_element']);
											break;
					case 'submit_form':		$(this).bind("submit",data,methods['submit_element']);
											break;
				}				
			});
			return false;
		},
		fetch:function(_obj,data){
			if(!_obj || !isDomElem(_obj)){_obj=$(this);}
			var _hd=methods['_harvest']($(_obj),data);
			return _hd.data;
		},
	/*	
		submit_delay:function(_obj){
			if(!_obj || !isDomElem(_obj)){_obj=$(this);}
			var _hd=methods['_harvest']($(_obj),false);
			delayed_spool['ttt']=_hd;
			console.log(delayed_spool);
			return true;
		},
	*/
		submit_element:function(_obj){
			if(!_obj || !isDomElem(_obj)){_obj=$(this);}
			var _hd=methods['_harvest']($(_obj),false);
			return methods['_rpc_call'](_hd.data,_hd.id);
		},
		send_delayed:function(defer_key){
			if(typeof defered_rpc_call[defer_key] == "undefined"){return false;}
			return methods['_rpc_call'](false,false,defer_key);
		},
		send: function(data){
			if(typeof $(this).context == "undefined" || !isDomElem($(this))){return methods['_rpc_call']($(this)[0],null);}
			_obj=$(this);
			var _hd=methods['_harvest']($(_obj),data);
			var _xx=_hd.data;
			if(data && typeof data=="object"){_xx=$.extend({},_xx,data);}
			return methods['_rpc_call'](_xx,_hd.id);
		},	
		_harvest:function(_obj,data){
			var _fd={};
			if(data){
				jQuery.removeData($(_obj),'_mcf_init');
				$(this).data('_mcf_init',data);
			}
			var _a=methods['_get_dom_element_data']($(_obj));
			var _d=$.extend({},_a['_mcf_init']);
			delete (_a['_mcf_init']);
			
			if (!$(_obj).attr('id')){$(_obj).attr('id','_mcf_dom-'+Math.floor(Math.random()*900000+100000));}
			var _id= $(_obj).attr('id');			
			
			if(typeof _d['_'] !=='undefined' && typeof _d['_']['closest'] !=='undefined'){
				_obj=$(_obj).closest(_d['_']['closest']);
				_fd=methods['_get_dom_element_data'](_obj);
			}

			var _d=methods['_get_callable'](_d,_obj,_id);
			if(typeof _d !== 'object'){return _d;}
			var _s=$.extend(_fd,_a,_d);			
			return {'id':_id,'data':_s}
		},
		_get_dom_element_data: function(_obj){
			if(typeof _obj != 'object'){return {};}
			var _fd={};
			var _fe={}
			_fe=$(_obj).data();
			switch($(_obj).prop("tagName")){
				case 'form':
				case 'FORM':
							_fd=$(_obj).serializeArray().reduce(function(obj,item) {
								obj[item.name] = item.value;
								return obj;
							},{});
							break;
			}
			return $.extend({},_fe,_fd);
		},
		_get_callable: function(_d,_obj,_id){
			if(typeof _d['_'] === 'undefined'){return {'_mcf':{'_int':{'defer':false,'defer_key':false}}};}
			var _xx={'_mcf':{'_int':{'defer':false,'defer_key':false}}};
			
			if(typeof _d['_']['defer'] != "undefined"){
				var max=999999;
				var min=100000;
				_xx['_mcf']['_int']['defer']=_d['_']['defer'];
				_xx['_mcf']['_int']['defer_key']='defer_'+Math.floor(Math.random()*(max-min+1)+min);
			}
			
			if(typeof _d['_']['callbefore'] !== 'undefined'){
				_xx['_mcf']['_callbefore']=_d['_']['callbefore'];
			}
			if(typeof _d['_']['callback'] !== 'undefined'){
				if(typeof _xx['_mcf'] === 'undefined'){_xx['_mcf']={};}
				_xx['_mcf']['_callback']=_d['_']['callback'];
			}

			if(typeof _d['_']['setup'] != 'undefined'){
				var _fn=_d['_']['setup'];
				
				var args=(typeof _d['_']['setup_args'] != 'undefined')?_d['_']['setup_args']:{};
				switch(true){
					case (typeof _fn === "function") :
							_cb=_fn(args,_obj,_id);
							break;
					case (typeof window[_fn] === "function") :
							_cb= window[_fn](args,_obj,_id);
							break;
				}
				if(typeof _cb !== 'undefined'){
					if(_cb && typeof _cb === 'object'){
						if(_cb!={}){
							if(typeof _cb['_mcf'] !== 'undefined'){
								_xx=$.extend(_xx,_cb);
							}else{
								if(typeof _xx['_usr'] === undefined){_xx['_usr']={};}
								_xx._usr=$.extend(_xx._usr,_cb);
							}
						}
					}else{
						return _cb;
					}
				}
			}
			_xx=$.extend({},_xx,_d);
			delete _xx['_'];
			return _xx;
		},
		
		_repack_data: function(_obj){
			var _rk=Object.keys(_mcore_rpc_options);
			var _z={'_usr':{},'_mcf':$.extend({},window['_mCoreV'])};

			if(typeof _obj._mcf != "undefined" ){
				_z._mcf=$.extend({},_z._mcf,_obj._mcf);
				delete _obj._mcf;
			}
			if(typeof _obj._usr !== 'undefined' ){
				_z._usr=_obj._usr;
				delete _obj._usr;
			}
			
			var _k=Object.keys(_obj);
			var _c=_k.length
			for(var _i=0;_i<_c;_i++){
				var _n=_k[_i];
				if(_rk.indexOf(_n)==-1){
					_z._usr[_n]=_obj[_n];
				}else{
					_z._mcf[_n]=_obj[_n];
				}
			}
			
			var _c=_rk.length;
			for(var _i=0;_i<_c;_i++){
				var _n=_rk[_i];
				switch(_n){
					case '_un':
					case '_lp':
					case '_ln':		delete _z._mcf[_n];
									break;
					
					case '_callbefore':
					case '_callback': 	if(_z._mcf[_n]==''){delete _z._mcf[_n];}
										break;
									
					case '_rp': 	if(_z._mcf[_n]==''){
										delete _z._mcf[_n];
										_z._mcf['_rf']=window.location.pathname;
									}
									break;
					default:		
				}
			}
			return _z;
		},
		
		_rpc_call: function(_rpc_data,_id,defer_key){
			var _mrpc={};
			var _callback=false;
			if(defer_key){
				if(typeof defered_rpc_call[defer_key]){
					_mrpc=defered_rpc_call[defer_key]['data'];
					_callback=defered_rpc_call[defer_key]['_callback']
					delete(defered_rpc_call[defer_key]);
				}else{
					return false;
				}
			}else{
				_mrpc=methods['_repack_data'](_rpc_data);
				var _cb = null;
				var _fn = null;

				var max=999999;
				var min=100000;
				var defer_key='defer_'+Math.floor(Math.random()*(max-min+1)+min);
				
				if(typeof _mrpc['_mcf']['_callbefore'] !== undefined){
					var _fn=_mrpc['_mcf']['_callbefore'];
					switch(true){
						case (typeof _fn === "function") :
								_cb=_fn(_mrpc['_usr'],defer_key);
								if(_cb===false){return false;}
								break;
						case (typeof window[_fn] === "function") :
								_cb= window[_fn](_mrpc['_usr'],defer_key);
								if(_cb===false){return false;}
								break;
					}

					if(typeof _cb == 'object' && _cb != null){
						if(typeof _cb['_mcf'] != "undefined"){
							_mrpc['_mcf']=$.extend({},_mrpc._mcf,_cb._mcf);
						}
						
						if(typeof _cb['_usr'] != "undefined"){
							_mrpc['_usr']=$.extend({},_mrpc._usr,_cb['_usr']);
						}
					}
					delete (_mrpc['_mcf']['_callbefore']);
				}
				if(typeof _mrpc['_mcf']['_callback'] != "undefined"){
					var _fn=_mrpc['_mcf']['_callback'];
					if(typeof _fn === "function"){
						_callback=_fn;
					}else if(typeof window[_fn] === "function"){
						_callback=window[_fn];
					}
					delete (_mrpc['_mcf']['_callback']);
				}
				if(typeof _mrpc['_mcf']['_int'] != "undefined"){
					var _df=_mrpc['_mcf']['_int'];
					if(typeof _df['defer'] != "undefined" && _df['defer']){
						defered_rpc_call[defer_key]={'data':_mrpc,'_callback':_callback};
						return defer_key;
					}
				}
			}
			delete(_mrpc['_mcf']['_int']);
			var _mCoreURI=(typeof _mCoreV['_un'] !== undefined)?_mCoreV['_un']+'/rpc/':'/rpc/';	
			$.ajax({
				url: _mCoreURI,
				data: _mrpc,
				type: 'POST',
				dataType: 'json',
				headers: {"Accept": "application/json"},
				cache: false,
				statusCode: {
					404: function() {console.log( "page not found" );return false;},
					500: function() {console.log( "internal error" );return false;},
				},
				success: function (r, t, rs) {
					if(typeof r['_mcf'] === undefined){
						console.log( "network problem" );
						return false;
					}
					if(_callback){return _callback(r['_mcf'],_id);}
					return false;
				},
				error: function (e) {
					//console.log(e);
					return false;
				}
			});
			return false;
		},
  };

  $.fn.mcf = function(method) {
	  switch(true){
		  case (typeof methods[method] !== undefined):	return methods[ method ].apply(this,Array.prototype.slice.call(arguments,1));
		  case (typeof method === 'object' || !method):	return methods.init.apply(this,arguments);
		  default:	$.error( 'Метод с именем ' +  method + ' не существует' );
	  }
  };
})( jQuery );

function isDomElem(obj) {
      if(obj instanceof HTMLCollection && obj.length) {
          for(var a = 0, len = obj.length; a < len; a++) {
              if(!checkInstance(obj[a])) {
                  console.log(a);
                  return false;   
              }
          }     
          return true;                
      } else {
          return checkInstance(obj);  
      }

      function checkInstance(elem) {
          if((elem instanceof jQuery && elem.length) || elem instanceof HTMLElement) {
              return true;  
          }
          return false;        
      }
}

if (!Array.isArray) {
  Array.isArray = function(arg) {
    return Object.prototype.toString.call(arg) === '[object Array]';
  };
}

if (!isObject) {
	function isObject(obj){
		 return (typeof(obj)!='object')?false:true;
		 return (typeof(obj)=='object');
	}
}

if(typeof window['_online'] !="undefined" && typeof window['_online'].init !="undefined"){
	
	//$({'_rp':'#online','_rpckey':'update','ua_id':_ua_id}).mcf('send');
	//setInterval(function(){$({'_rp':'#online','_rpckey':'update','ua_id':_ua_id}).mcf('send');},300000);	
	$(window['_online'].init).mcf('online');
	if(typeof window['_online'].interval == "undefined"){
		setInterval(function(){$(window['_online'].init).mcf('online');},300000);
	}else{
		setInterval(function(){$(window['_online'].init).mcf('online');},window['_online'].interval);
	}
}

function mcore_generate_uuid(){
	function chr4(){return Math.random().toString(16).slice(-4);}
	return chr4() + '-' + chr4() + '-' + chr4() + '-' + chr4() + '-' + chr4() + '-' + chr4();
}