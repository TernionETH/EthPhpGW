<?php
namespace mcf\ethereum\extensions;

class mcf_ethereum_personal extends \mcf\ethereum\mcf_ethereum_utils{
	
	public function __construct($_rpc_settings=array(),$_wallet_settings=array()){
		$this->_rpc_settings=$_rpc_settings;
		$this->_wallet_settings=$_wallet_settings;
	}
	
	
	public function __call($name,$args=array()){
		if(count($args)==1 && isset($args[0])){$args=$args[0];}
		
		switch($name){
			case 'personal_newAccount':		$_r=$this->_etherium_send_request('personal_newAccount',array($args[0]));
											if(isset($_r['error']['code'])){return array('_err'=>array('INVALID_AUTHENTICATION'),'_data'=>false);}
											return array('_err'=>false,'_data'=>$_r['result']);
			case 'personal_unlockAccount': 
											$_r=$this->_etherium_unlock_account($args[0],$args[1],false);
											if(isset($_r['error']['message'])){return array('_err'=>array('INVALID_AUTHENTICATION'));}
											return $_r;
			default: print_r($args);
		}
	}
}

?>