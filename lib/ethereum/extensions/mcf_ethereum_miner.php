<?php
namespace mcf\ethereum\extensions;

class mcf_ethereum_miner extends \mcf\ethereum\mcf_ethereum_utils{
	
	public function __construct($_setting=array()){
		$this->_setting=$_setting;
	}
	
	
	public function __call($name,$args=array()){
		if(count($args)==1 && isset($args[0])){$args=$args[0];}
		
		switch($name){
			case 'personal_newAccount':
											$_r=$this->_etherium_send_request('personal_newAccount',array($args[0]));
											//if(isset($_r['error']['code'])){return array('_err'=>array('INVALID_AUTHENTICATION'));}
											return $_r;			
			case 'personal_unlockAccount': 
											$_r=$this->_etherium_send_request('personal_unlockAccount',array($args[0],$args[1]));
											if(isset($_r['error']['code'])){return array('_err'=>array('INVALID_AUTHENTICATION'));}
											return $_r;
			default: print_r($args);
		}
	}
}

?>