<?php
namespace mcf\ethereum;

class mcf_ethereum_utils{
	const JSON_RPC_VERSION = '2.0';
	const JSON_RPC_MEDIA_TYPE = 'application/json';
	
	private $_unlocked_accounts=array();
	private $_active_wallet_key=false;
	private $_web3_sha3_pool=false;
	
	
	protected $_rpc_settings=array();
	protected $_wallet_settings=array();

	public function activate_wallet_key($wallet_key=false){
		if(isset($this->_wallet_settings[$wallet_key])){$this->_active_wallet_key=$wallet_key;}
	}
	
	protected function _etherium_unlock_account($wallet='0x0',$password=false,$time=false){
		if(isset($this->_unlocked_accounts[$wallet])){return array('_err'=>false,'_data'=>true);}
		
		$_r=$this->_etherium_send_request('personal_unlockAccount',array($wallet,$password));
		if(empty($_r)){return array('_err'=>array('_codes'=>array('NETWORK_UNREACHABLE')),'_data'=>false);}
		if(isset($_r['error']['message'])){return array('_err'=>array('_message'=>array($_r['error']['message'])),'_data'=>false);}
		$this->_unlocked_accounts[$wallet]=true;
		return array('_err'=>false,'_data'=>true);
	}
	
	
	protected function _etherium_call_function($from=false,$to=false,$func_definition,$args=array(),$block_position='latest'){
		$tx=$this->_etherium_create_tx($from,$to,$func_definition,$args,'latest');
		return $this->_etherium_send_request('eth_call',$tx);
	}
	
	protected function _etherium_sendTransaction($from=false,$to=false,$func_definition,$args=array()){
		$tx=$this->_etherium_create_tx($from,$to,$func_definition,$args);
		return $this->_etherium_send_request('eth_sendTransaction',$tx);
	}

	protected function BigHexToDec($hex=0){
		$dec = 0;
		$len = strlen($hex);
		for ($i = 1; $i <= $len; $i++){$dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));}
		return $dec;
	}
	
	protected function set_wallet_settings($wallet_key,$source_address='0x0',$contract_address='0x0',$source_password=''){
		$args=array(
			'address'=>array(
				'source'=>$source_address, //base wallet address
				'target'=>'0x0',
				'contract'=>$contract_address
			
			),
			'password'=>$source_password,
		);		
		$this->_wallet_settings[$wallet_key]=$args;
		return $this;
	}
	
	
	protected function get_wallet_settings($target_address='0x0',$extra=array()){
		$args=$this->_wallet_settings[$this->_active_wallet_key];
		$args['address']['target']=$target_address;
		//$args['amount']=$amount;
		$args['args']=(is_array($extra))?array_values($extra):array();
		return $args;
	}
	
	
	private function _etherium_create_tx($from=false,$to=false,$func_definition,$args=array(),$block_position=false){
		$_cargs=array();
		$_cargs[]=$this->_function_definition_to_address($func_definition);
		if(empty($args)){
			$args=array();
		}elseif(!is_array($args)){
			$args=array($args);
		}
		if($block_position && !in_array($block_position,array('latest','earliest','pending'))){$block_position='latest';}
		
		while(count($args)<3){$args[]=0;}
		$_cargs[]=$this->_function_args_to_string($args);
		$bin=implode('',$_cargs);
		$tx=array(0=>array('data'=>$bin));
		if($block_position){$tx[]=$block_position;}
		if(!empty($from)){$tx[0]['from']=$from;}
		if(!empty($to)){$tx[0]['to']=$to;}		
		return $tx;
	}
	
	private function _function_definition_to_address($func_definition){
		list($func,$params)=explode('(',$func_definition,2);
		$func=trim($func);
		$params=trim($params);
		if(!empty($params)){
			list($params,)=explode(')',$params);
			$params=trim($params);
			$z=explode(',',$params);
			$params=array();
			foreach($z as $v){
				$v=trim($v);
				$x=explode(' ',$v);
				$params[]=$x[0];
			}
		}
		$func_definition=(empty($params))?sprintf('%s()',$func):sprintf('%s(%s)',$func,implode(',',$params));
		return $this->_convert_funcdef_to_web3_sha3($func_definition);	
	}
	
	private function _function_args_to_string($params=array()){
		if(empty($params)){return str_repeat('0',96);}
		foreach($params as $k => $v){
			switch(true){
				case (empty($v)):				$params[$k]=str_repeat('0',32);break;
				case (is_bool($v)):				$params[$k]=str_repeat('0',31).intval($v);break;
				case (substr($v,0,2)=='0x'):	$v=ltrim($v,'0x');									
												$params[$k]=str_repeat('0',32-strlen($v)).$v;
												$params[$k]=strtolower($params[$k]);
												break;
				case (is_numeric($v)):			$v=dechex($v);
												$params[$k]=str_repeat('0',32-strlen($v)).$v;
												break;
				case (ctype_xdigit($v)):		$v=strtolower($v);
												$params[$k]=str_repeat('0',32-strlen($v)).$v;
												break;
				
			}
		}
		$params=array_slice($params,0,3);
		return implode($params);
	}

	
	private function _convert_funcdef_to_web3_sha3($func_definition=false){
		if(!$func_definition){return '0x0';}
		$fdh=unpack('H*',$func_definition);
		$fdh=sprintf('0x%s',end($fdh));
		if(is_array($this->_web3_sha3_pool) && isset($this->_web3_sha3_pool[$fdh])){return $this->_web3_sha3_pool[$fdh];}
		$cfn=sprintf('%s%sweb3_sha3_spool.json.db',__DIR__,DIRECTORY_SEPARATOR);
		
		if($this->_web3_sha3_pool===false){
			$this->_web3_sha3_pool=array();
			if(file_exists($cfn)){
				$z=@json_decode(file_get_contents($cfn),true);
				if($z){$this->_web3_sha3_pool=$z;}
				if(isset($this->_web3_sha3_pool[$fdh])){return $this->_web3_sha3_pool[$fdh];}
			}
		}
		$r=$this->_etherium_send_request('web3_sha3',array($fdh));
		$this->_web3_sha3_pool[$fdh]=substr($r['result'],0,10).str_repeat('0',24);
		
		
		$fh=@fopen($cfn,'w+');
		if($fh){
			$fd=json_encode($this->_web3_sha3_pool,JSON_UNESCAPED_UNICODE);
			fwrite($fh,$fd);
			fclose($fh);
		}
		return $this->_web3_sha3_pool[$fdh];
	}
	
	
	protected function _etherium_send_request($_callmethod,$args=array()){
		$request = array(
			'jsonrpc' => self::JSON_RPC_VERSION,
			'method' => $_callmethod,
			'params' => array_values($args),
			'id' => $this->_rpc_settings['network_id'],
		);
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type:'.self::JSON_RPC_MEDIA_TYPE));
		curl_setopt($ch, CURLOPT_URL,$this->_rpc_settings['host']);
		curl_setopt($ch, CURLOPT_PORT, $this->_rpc_settings['port']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($request,JSON_UNESCAPED_UNICODE));
		//curl_setopt($ch, CURLOPT_POSTFIELDS,$line);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$_r= curl_exec($ch);
		$_e= curl_errno($ch);
		curl_close ($ch);
		if(!empty($_e)){
			switch($_e){
				case 7: return array('_err'=>array('NETWORK_UNREACHABLE'));
				default:  return array('_err'=>array('NETWORK_ERROR_UNDEFINED'));
			}
		}
		return @json_decode($_r,true);
	}
}
?>