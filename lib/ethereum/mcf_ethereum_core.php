<?php
namespace mcf\ethereum;

class mcf_ethereum_core{
	/* objects */
	private $_loaded_extentions=array();
	private $_rpc_settings=array();
	private $_wallet_settings=array();
	private $_active_wallet_key=false;
	
	
	public function __construct($host=false,$port=false,$network_id=1,$wallet_settings=array()){
		if($host && $port){$this->_init($host,$port,$network_id);}
		if(!empty($wallet_settings) && is_array($wallet_settings)){
			foreach($wallet_settings as $wallet_key => $v){$this->_set_wallet_settings($wallet_key,$v['source'],$v['contract'],$v['password']);}
		}
	}

	public function activate_wallet_key($wallet_key=false){
		if(isset($this->_wallet_settings[$wallet_key])){$this->_active_wallet_key=$wallet_key;}
	}
	
	public function __call($name,$args=array()){
		if(!$this->_active_wallet_key){return array('_err'=>array('INACTIVE_WALLET_KEY'),'_code'=>false);}
		list($g,)=explode('_',$name);
		$_cf=false;
		switch($g){
			case 'personal':
			case 'eth':
			case 'admin':
			case 'contract':
			case 'miner':
			case 'web3':	$_cf=sprintf('\%s\extensions\mcf_ethereum_%s',__NAMESPACE__,$g);
							break;
			default: return array('_err'=>array('UNDEFINED_EXTENSION'),'_code'=>false);
		}
		
		if($_cf){
			if(!isset($this->_loaded_extentions[$_cf])){
				$this->_loaded_extentions[$_cf]= new $_cf($this->_rpc_settings['_usr'],$this->_wallet_settings);
			}
			if($this->_loaded_extentions[$_cf]){
				$this->_loaded_extentions[$_cf]->activate_wallet_key($this->_active_wallet_key);
				$_r=call_user_func(array($this->_loaded_extentions[$_cf],$name),$args);
				return $_r;
			}			
		}
		return array('_err'=>array('UNDEFINED_EXTENSION'),'_code'=>false);
	}
	
	public function _init($host='127.0.0.1',$port='8545',$network_id=1){
		$this->_rpc_settings=array('_usr'=>array(),'_def'=>array());
		$this->_rpc_settings['_def']=array('host'=>'127.0.0.1','port'=>'8545','network_id'=>1);
		$this->_rpc_settings['_usr']=$this->_rpc_settings['_def'];
		if($this->_rpc_settings['_usr']['host']!=$host){$this->_rpc_settings['_usr']['host']=$host;}
		if($this->_rpc_settings['_usr']['port']!=$port){$this->_rpc_settings['_usr']['port']=$port;}
		if($this->_rpc_settings['_usr']['network_id']!=$network_id){$this->_rpc_settings['_usr']['network_id']=$network_id;}	
	}
	
	private function _set_wallet_settings($key='_def',$source_address='0x0',$contract_address='0x0',$source_password=''){
		if(empty($key)){$key='_def';}
		$this->_wallet_settings[$key]=array(
			'address'=>array(
				'source'=>$source_address,
				'target'=>'0x0',
				'contract'=>$contract_address
			),
			'password'=>$source_password
		);
		return $this;
	}
}
?>