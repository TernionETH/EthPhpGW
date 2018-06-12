if(typeof _mmcall == "undefined"){
	function _mmcall(){
		console.log('func _mmcall undefined');
		return false;
	}
}

class mcf_modal_render {
	constructor(settings) {this.init(settings);}
	init(settings){this.settings=(typeof settings =="object")?settings:{};return this;}

	render(html,envs,key){
		if(typeof this.settings != "object" || this.settings=={}){return html;}
		if(key && typeof this.settings['key'] == "undefined"){return html;}
		var _b={};
		if(key){
			_b[key+'.text']=this.build_by_key(key);
		}else{
			var _ak=Object.keys(this.settings);
			var i=0;
			for(;i<_ak.length;i++){var key=_ak[i];_b[key+'.text']=this.build_by_key(key);}
		}
		var _h=(html.length>0)?this.render_environments(html,_b):'';
		if(typeof envs!="object" || !Object.keys(envs).length){return _h;}		
		return this.render_environments(_h,this.object_to_keys(envs));
	}
	render_environments(_html,_envs){
		if(typeof _envs!="object" || !Object.keys(_envs).length){return _html;}
		var _x=this.clone({'h':_html});
		var _h=_x.h;
		var _rk=Object.keys(_envs);
		for(var i=0;i<_rk.length;i++){
			var key=_rk[i];
			_h =_h.replace(new RegExp("\\{"+key+"\\}", "gi"),_envs[key]);
		}
		return _h;
	}
	
	build_by_key(key){
		if(this.settings=={} || typeof this.settings[key] == "undefined"){return '';}
		var _elc = document.createElement('div');
		
		var _n=this.settings[key];
		var _def=(typeof _n['_def'] =="undefined")?{}:_n['_def'];
		var _set=(typeof _n['_'] =="undefined")?{}:_n['_'];
		var _elc=this.create_elements_recursive(_elc,_set,_def);
		
		return _elc.innerHTML;
	}
	create_elements_recursive(_mo,_set,_def){
		var _ak=Object.keys(_set);
		var y=0;
		var _el=false;
		for(;y<_ak.length;y++){
			var _k=_ak[y];
			_el=this.create_element(_set[_k],_def);
			if(_el && typeof _set[_k]['items'] !="undefined"){
				_el=this.create_elements_recursive(_el,_set[_k]['items'],_def);
			}
			if(_el){_mo.appendChild(_el);}
		}
		return _mo;
	}
	create_element(_st,_def){
		var _ws=Object.assign({},{'class':'','attr':'','text':'','tag':''},_st);
		if(_ws.tag=='' && _ws.text!=''){
			var _el =document.createTextNode(_ws.text);
			return _el;
		}
		
		var _el=document.createElement(_ws.tag);
		var _els={'class':[],'attr':{}};
		if(typeof _def[_ws.tag]!="undefined"){
			if(typeof _def[_ws.tag]['class'] != "undefined" && _def[_ws.tag]['class']){_els['class']=_def[_ws.tag]['class'].split(' ');}
			if(typeof _def[_ws.tag]['attr'] != "undefined" && _def[_ws.tag]['attr']){_els['attr']=Object.assign({},_def[_ws.tag]['attr']);}
		}
		
		if(_ws['class']!=''){_els['class']=_els['class'].concat(_ws['class'].split(' '));}
		if(_ws['attr']!={}){_els['attr']=Object.assign({},_ws['attr']);}

		for(var i=0;i<_els['class'].length;i++){if(_els['class'][i]!=''){_el.classList.add(_els['class'][i]);}}
		var _ak=Object.keys(_els['attr']);
		for(var f=0;f<_ak.length;f++){_el.setAttribute(_ak[f],_els['attr'][_ak[f]]);}


		if(typeof _ws.call!="undefined" && typeof _mmcall == "function"){
			if(typeof _ws.call.func!="undefined"){
				if(typeof _ws.call.args == "undefined"){
					_el.setAttribute('onclick','_mmcall(this,'+_ws.call.func+')');
				}else if(typeof _ws.call.args == "object"){
					_el.setAttribute('onclick','_mmcall(this,\''+_ws.call.func+'\','+JSON.stringify(_ws.call.args)+')');
				}else{
					_el.setAttribute('onclick','_mmcall(this,\''+_ws.call.func+'\',\''+escape(_ws.call.args)+'\')');
				}
			}
		}
		
		if(typeof _ws.data!="undefined"){
			var _atks=Object.keys(_ws.data);
			for(var f=0;f<_atks.length;f++){_el.setAttribute('data-'+_atks[f],_ws.data[_atks[f]]);}
		}
		
		if(_ws.text!=''){
			var _elt =document.createTextNode(_ws.text);
			_el.appendChild(_elt);
		}
		return _el;
	}
	
	isDomElem(_el){
		if(_el instanceof HTMLCollection && _el.length) {
			for(var a = 0, len = _el.length; a < len; a++) {if(!(_el[a] instanceof HTMLElement || (_el[a] instanceof jQuery && _el[a].length))) {return false;}}
			return true;                
		}
		return (_el instanceof HTMLElement || (_el instanceof jQuery && _el.length));
	}
  
	object_to_keys(_obj){
		var _r={};
		var _ak1=Object.keys(_obj);
		for(var i=0;i<_ak1.length;i++){
			var k1=_ak1[i];
			if(typeof _obj[k1] == "string"){_r[k1]=_obj[k1];continue;}
			var _ak2=Object.keys(_obj[k1]);
			for(var y=0;y<_ak2.length;y++){
				var k2=_ak2[y];
				var vn=k1+'.'+k2;
				_r[vn]=_obj[k1][k2];
			}
		}
		return _r;
	}
  
	clone(obj) {
      if (obj === null || typeof(obj) !== 'object' || 'isActiveClone' in obj){return obj;}
      var _t =(obj instanceof Date)?new obj.constructor():obj.constructor();
      for (var key in obj) {
        if (Object.prototype.hasOwnProperty.call(obj, key)) {
          obj['isActiveClone'] = null;
          _t[key] = this.clone(obj[key]);
          delete obj['isActiveClone'];
        }
      }
      return _t;
    }
}
