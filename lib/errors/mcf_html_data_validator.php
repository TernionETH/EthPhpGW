<?php
namespace mcf\errors;
class mcf_html_data_validator{
	private $_fm=array();
	public function __construct($_fm=array()){
		if(is_array($_fm)){$this->set_fields_matching($_fm);}
		
	}

	public function set_fields_matching($_fm=array()){
		if(!is_array($_fm)){return $this;}
		$this->_fm=$_fm;
		return $this;
	}

	public function check_for_empty_fields_combined($data=array(),$_mfn='_mf',$clean_data=true){
		$_r=array('_valid'=>true,'_err'=>array('_fields'=>array()),'_data'=>array());
		if(empty($data)){return $_r;}
		$input_as_array=(is_array($data));
		if(!$input_as_array){$data=array($data);}
		if($clean_data){$data=$this->clean_data_from_non_printable($data);}
		$_mf=array();
		if(isset($data[$_mfn])){
			$_mf=$data[$_mfn];
			if(!is_array($_mf)){$_mf=array($_mf);}
			unset($data[$_mfn]);
		}
		$_r['_data']=$data;
		if(!empty($_mf)){
			if(!is_array($_mf)){$_mf=array($_mf);}
			$_r=$this->check_for_empty_fields($_r['_data'],$_mf);
		}
		if(!$input_as_array){$_r['_data']=end($_r['_data']);}
		return $_r;
	}
	
	public function check_for_empty_fields($data=array(),$_mf=array(),$clean_data=true){
		$_r=array('_valid'=>true,'_err'=>array('_fields'=>array()),'_data'=>array());
		if(empty($_mf)){return $_r;}
		if($clean_data){$data=$this->clean_data_from_non_printable($data);}
		$_r['_data']=$data;
		foreach($_mf as $fn){
			$z=explode('[',$fn);
			$lnk=&$data;
			foreach($z as $pos => $cfn){
				$cfn=rtrim($cfn,']');
				if(!isset($lnk[$cfn]) || ($pos==(count($z)-1) && empty($lnk[$cfn]))){
					$_r['_err']['_fields'][]=$fn;
					$_r['_valid']=false;
					break;
				}
				$lnk=&$lnk[$cfn];
			}
		}
		return $_r;
	}

	public function validate($data=array(),$field_type=array()){
		$_r=$this->_validate($data,$field_type);
		if(!empty($_r['_err']['_invalid'])){
			$_r['_err']['_fields']=array_keys($_r['_err']['_invalid']);
			$_r['_err']['_codes']=array_values($_r['_err']['_invalid']);
			$_r['_err']['_codes']=array_unique($_r['_err']['_codes']);
		}
		unset($_r['_err']['_invalid']);
		return $_r;
	}

	private function _validate($data=array(),$field_type=array(),$root=''){
		$_r=array('_valid'=>true,'_err'=>array('_invalid'=>array()),'_data'=>$data);
		if(empty($data)){return $_r;}
		foreach($data as $k => $_v){
			if(is_array($_v)){
				$z=$this->_validate($_v,$field_type,$k);
				if(!$z['_valid']){
					$_r['_valid']=false;
					$_r['_err']['_invalid']=array_merge($_r['_err']['_invalid'],$z['_err']['_invalid']);
				}
			}else{
				$_lk=strtolower($k);
				$_y=(empty($root))?$_lk:sprintf('%s[%s]',trim($root),$_lk);
				
				switch(true){
					case (isset($field_type[$_y])): $_t=$field_type[$_y];break;
					case (isset($this->_fm[$_y])):  $_t=$this->_fm[$_y];break;
					default: $_t=$_lk;
				}
				
				$errcode=false;
				switch($_t){
					case 'email': 	if(empty($_v) || !filter_var($_v,FILTER_VALIDATE_EMAIL)){$errcode='email_format';}
									break;
					case 'url': 	if(!filter_var($_v,FILTER_VALIDATE_URL)){$errcode='url_format';}
									break;
					case 'postcode':
									$_v=preg_replace('/\s+/','',$_v);
									if(strlen($_v)<4){
										$errcode='postcode_format';
									}elseif((strlen($_v)>=4 && strlen($_v)<6) && !(preg_match('/^[0-9]+$/',$_v))){
										$errcode='postcode_format';
									}
									break;
					default:		$errcode=false;
										
				}
				if($errcode){$_r['_err']['_invalid'][$_y]='invalid_'.$errcode;}
			}
		}
		if(count($_r['_err']['_invalid'])){$_r['_valid']=false;}
		//print_r($_r);
		return $_r;
		
		
		print_r($_r);
		return false;
		
		
/*		
		$ht=array();
		foreach($data as $k => $v){
			$k=strtolower($k);
			if(isset($field_type[$k])){
				$type=$field_type[$k];
			}elseif(isset($this->_fm[$k])){
				$type=$this->_fm[$k];
			}else{
				$type=$this->_fm[$k];
			}
			$ht[$type][$k]=$v;
		}
		
		print_r($ht);
*/		
		
		$errcode=false;
		foreach($ht as $type => $row){
			foreach($row as $_f => $_v){
				$errcode=false;
				switch($type){
					case 'email': 	if(!filter_var($_v,FILTER_VALIDATE_EMAIL)){$errcode='email_format';}
									break;
					case 'url': 	if(!filter_var($_v,FILTER_VALIDATE_URL)){$errcode='email_format';}
									break;
					case 'postcode':
									$_v=preg_replace('/\s+/','',$_v);
									switch(strlen($_v)){
										case 0:
										case 1:
										case 2:
										case 3:	$errcode='postcode_format';
												break;
										case 4:
										case 5:	if(!(preg_match('/^[0-9]+$/',$_v))){$errcode='postcode_format';}
												break;
										case 6:
										
										default:$errcode='postcode_format';
									}
					default:		$errcode=false;
										
				}
				if($errcode!==false){
					$_r['_errors']=true;
					$_r['_invalid'][$_f]='invalid_'.$errcode;
				}
				
			}
		}
		print_r($_r);
		return $_r;
	}
	
	public function create_err_codes($_err=array(),$_errc_group='',$_errc_start='',$_errc_end=''){
		if(empty($_err)){return array();}
		if(!is_array($_err)){$_err=array($_err);}
		
		$_errc_group=trim($_errc_group);
		$_errc_group=trim($_errc_group,'.');
		$_errc=array();
		foreach($_err as $_error){
			$_error=strtolower($_error);
			$_error=str_replace(array('[',']'),array('.',''),$_error);
			$_errc[]=sprintf('%s.%s%s%s',$_errc_group,$_errc_start,$_error,$_errc_end);
		}
		return $_errc;
	}
	
	private function clean_data_from_non_printable($data=array()){
		if(empty($data)){return $data;}
		foreach($data as $k =>$v){
			if(is_array($v)){
				$data[$k]=$this->clean_data_from_non_printable($v);
			}else{
				$v=preg_replace('/[^[:print:]]/','',$v);
				$data[$k]=trim($v);
			}
		}
		return $data;
	}	
}
?>