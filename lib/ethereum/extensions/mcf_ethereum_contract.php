<?php
namespace mcf\ethereum\extensions;

class mcf_ethereum_contract extends \mcf\ethereum\mcf_ethereum_utils{
	public function __construct($_rpc_settings=array(),$_wallet_settings=array()){
		$this->_rpc_settings=$_rpc_settings;
		$this->_wallet_settings=$_wallet_settings;
	}
	
	public function __call($name,$args=array()){
		
	}

	public function contract_balanceOf($data=array()){
		if(isset($data[0]) && count($data)==1){$data=end($data);}		
		$_wx=$this->get_wallet_settings('0x0',$data);	
		$_r=$this->_etherium_call_function(false,$_wx['address']['contract'],'balanceOf(address _recipient)',$_wx['args']);	
		$balance=(empty($_r['result']))?0:$this->BigHexToDec($_r['result']);
		return array('_err'=>false,'_data'=>array('balance'=>$balance));
	}

	public function contract_balanceOfOwner($data=array()){
		//balanceOfOwner() onlyOwner returns (uint256 balance)
		if(isset($data[0]) && count($data)==1){$data=end($data);}		
		$_wx=$this->get_wallet_settings('0x0',array());	
		$_r=$this->_etherium_call_function(false,$_wx['address']['contract'],'balanceOfOwner()',$_wx['args']);	
		$balance=(empty($_r['result']))?0:$this->BigHexToDec($_r['result']);
		return array('_err'=>false,'_data'=>array('balance'=>$balance));
	}
	
	public function contract_transfer($data=array()){
		if(isset($data[0]) && count($data)==1){$data=end($data);}
		$func_definition='transfer(address _to, uint256 _value)';
		
		$_wx=$this->get_wallet_settings('0x0',array($data['target'],0,$data['amount']));
		
		$_r=$this->_etherium_unlock_account($_wx['address']['source'],$_wx['password'],false);
		if(isset($_r['error']['message'])){return array('_err'=>array('INVALID_AUTHENTICATION'),'_data'=>false);}
		
		
		$_r=$this->_etherium_sendTransaction($_wx['address']['source'],$_wx['address']['contract'],$func_definition,$_wx['args']);
		
		if(isset($_r['error']['message'])){
			return array('_err'=>array($_r['error']['message']),'_data'=>false);
		}
		return array('_err'=>false,'_data'=>array('transaction'=>$_r['result']));
	}
	
	public function contract_transferfrom($data=array()){
		if(isset($data[0]) && count($data)==1){$data=end($data);}
		$func_definition='transferFrom(address _from, address _to, uint256 _value)';
		
		$_wx=$this->get_wallet_settings('0x0',array($data['source'],$data['target'],$data['amount']));
		
		$_r=$this->_etherium_unlock_account($data['source'],$data['password'],false);
		if(isset($_r['error']['message'])){return array('_err'=>array('INVALID_AUTHENTICATION'),'_data'=>false);}

		$_r=$this->_etherium_sendTransaction($_wx['address']['source'],$_wx['address']['contract'],$func_definition,$_wx['args']);
		
		if(isset($_r['error']['message'])){
			return array('_err'=>array($_r['error']['message']),'_data'=>false);
		}
		return array('_err'=>false,'_data'=>array('transaction'=>$_r['result']));
	}

	public function contract_mintCoins($data=array()){
		//mintCoins(address _to, uint256 mintedAmount) onlyOwner returns (bool success)

		if(isset($data[0]) && count($data)==1){$data=end($data);}
		$func_definition='mintCoins(address _to, uint256 mintedAmount)';
		
		if(!isset($data['target'])){
			$_wx=$this->get_wallet_settings('0x0',array());
			$data['target']=$_wx['address']['source'];
		}
		$_wx=$this->get_wallet_settings('0x0',array($data['target'],0,$data['amount']));
		$_r=$this->_etherium_call_function(false,$_wx['address']['contract'],$func_definition,$_wx['args']);	
		//$_r=$this->_etherium_sendTransaction($_wx['address']['source'],$_wx['address']['contract'],$func_definition,$_wx['args']);
		
		if(isset($_r['error']['message'])){
			return array('_err'=>array($_r['error']['message']),'_data'=>false);
		}
		return array('_err'=>false,'_data'=>array('transaction'=>$_r['result']));
	}
	
	public function contract_burn($data=array()){
		///burn(uint256 _value) onlyOwner returns (bool success)
		if(isset($data[0]) && count($data)==1){$data=end($data);}
		$func_definition='burn(uint256 _value)';

		$_wx=$this->get_wallet_settings('0x0',array(0,0,$data['amount']));
		$_r=$this->_etherium_call_function(false,$_wx['address']['contract'],$func_definition,$_wx['args']);	
		//$_r=$this->_etherium_sendTransaction($_wx['address']['source'],$_wx['address']['contract'],$func_definition,$_wx['args']);
		
		if(isset($_r['error']['message'])){
			return array('_err'=>array($_r['error']['message']),'_data'=>false);
		}
		return array('_err'=>false,'_data'=>array('transaction'=>$_r['result']));
	}

	public function contract_burnFrom($data=array()){
		///burnFrom(address _from, uint256 _value) onlyOwner returns (bool success)
		if(isset($data[0]) && count($data)==1){$data=end($data);}
		$func_definition='burnFrom(address _from, uint256 _value)';

		if(!isset($data['target'])){
			$_wx=$this->get_wallet_settings('0x0',array());
			$data['target']=$_wx['address']['source'];
		}
		
		$_wx=$this->get_wallet_settings('0x0',array($data['target'],0,$data['amount']));
		$_r=$this->_etherium_call_function(false,$_wx['address']['contract'],$func_definition,$_wx['args']);	
		//$_r=$this->_etherium_sendTransaction($_wx['address']['source'],$_wx['address']['contract'],$func_definition,$_wx['args']);
		
		if(isset($_r['error']['message'])){
			return array('_err'=>array($_r['error']['message']),'_data'=>false);
		}
		return array('_err'=>false,'_data'=>true);
	}

	public function contract_ChangeOwnership($data=array()){
		///ChangeOwnership(address _newOwner) onlyOwner returns (bool success)
		if(isset($data[0]) && count($data)==1){$data=end($data);}
		$func_definition='ChangeOwnership(address _newOwner)';

		if(!isset($data['target'])){return array('_err'=>false,'_data'=>true);}
		
		$_wx=$this->get_wallet_settings('0x0',array($data['target'],0,0));
		$_r=$this->_etherium_call_function(false,$_wx['address']['contract'],$func_definition,$_wx['args']);	
		//$_r=$this->_etherium_sendTransaction($_wx['address']['source'],$_wx['address']['contract'],$func_definition,$_wx['args']);
		
		if(isset($_r['error']['message'])){return array('_err'=>array($_r['error']['message']),'_data'=>false);}
		return array('_err'=>false,'_data'=>true);
	}	
	

	
	public function contract_approve($data=array()){
		///approve(address _spender, uint256 _value) returns (bool success)
		if(isset($data[0]) && count($data)==1){$data=end($data);}
		$func_definition='approve(address _spender, uint256 _value)';

		if(!isset($data['source']) || !isset($data['target'])){return array('_err'=>false,'_data'=>array('approved'=>0));}

		$_wx=$this->get_wallet_settings('0x0',array($data['target'],0,$data['amount']));
		$_r=$this->_etherium_call_function(false,$_wx['address']['contract'],$func_definition,$_wx['args']);	
		//$_r=$this->_etherium_sendTransaction($_wx['address']['source'],$_wx['address']['contract'],$func_definition,$_wx['args']);
		
		if(isset($_r['error']['message'])){
			return array('_err'=>array($_r['error']['message']),'_data'=>false);
		}
		return array('_err'=>false,'_data'=>array('approved'=>$data['amount']));
	}
	
	public function contract_allowance($data=array()){
		///allowance(address _owner, address _spender) constant returns (uint256 remaining)
		if(isset($data[0]) && count($data)==1){$data=end($data);}
		$func_definition='allowance(address _owner, address _spender)';

		if(!isset($data['source']) || !isset($data['target'])){return array('_err'=>false,'_data'=>array('allowance'=>0));}
		
		$_wx=$this->get_wallet_settings('0x0',array($data['source'],0,$data['target']));
		$_r=$this->_etherium_call_function(false,$_wx['address']['contract'],$func_definition,$_wx['args']);
		
		if(isset($_r['error']['message'])){return array('_err'=>array($_r['error']['message']),'_data'=>false);}
		
		$allowance=(empty($_r['result']))?0:$this->BigHexToDec($_r['result']);
		return array('_err'=>false,'_data'=>array('allowance'=>$allowance));
	}
}

?>