<?php
class ether_wallet{
	private $ethereum;	
	
	private $_selfwallets=array();
	private $_hdv;
	private $_keystore;
	private $_sendfiles=array();

	public function __construct(){
		$_w=array(
			'main'=>array(
				'source'=>'0x0',			//main wallet address
				'contract'=>'0x0',			//contract address
				'password'=>'1234567890'	//password for unlock wallet
			)
		);
		//'xxx.xxx.xxx.xxx' - ip geth rpc
		//'xx'				- port geth rpc
		$this->ethereum = new \mcf\ethereum\mcf_ethereum_core('xxx.xxx.xxx.xxx','xx',1,$_w);

		$this->ethereum->activate_wallet_key('main');
		$this->_keystore=DOCUMENT_ROOT.DIRECTORY_SEPARATOR.'keystore';
	}
	
	public function __destruct(){
		if(!empty($this->_sendfiles)){
			//foreach($this->_sendfiles as $filename){$this->sendfiletobrowser($filename);}
		}
	}
	
	public function rpc_wallet_create($data=array()){
		$data['password']=trim($data['password']);
		if(!$this->_hdv){$this->_hdv= new \mcf\errors\mcf_html_data_validator();}
		$r=$this->_hdv->check_for_empty_fields_combined($data,'_mf',true);
		if(!$r['_valid']){
			$_errc=$this->_hdv->create_err_codes($r['_err']['_fields'],'_application.codes.errors.wallets');
			$r['_err']['_codes']=$_errc;
			return array('_err'=>array('_fields'=>$r['_err']['_fields']),'_render'=>array('error-fields'=>$_errc));
        }
		
		if(strlen($data['password'])<8){
			return array('_err'=>false,'_render'=>array('password_len_to_low'=>array('min_len'=>8)));
		}
		
		$_r=$this->ethereum->personal_newAccount($data['password']);
		if(!empty($_r['_err'])){
			$_errc=$this->_hdv->create_err_codes($_r['_err'],'_application.codes.errors.wallets');
			return array('_err'=>array(),'_render'=>array('error-fields'=>$_errc));
		}
		$wallet=$_r['_data'];
		$tpl=sprintf('%s/*%s',rtrim($this->_keystore,DIRECTORY_SEPARATOR),substr($wallet,5));
		$z=glob($tpl);
		$filepath=$z[0];
		chmod($filepath,0444);
		
		
		$_rd=array('wallet'=>$wallet,'uri'=>'/'.ltrim($filepath,DOCUMENT_ROOT));
		$_rd['name']=basename($_rd['uri']);
		$this->_sendfiles[]=$filepath;
		return array('_err'=>false,'_data'=>$_rd,'_render'=>array('wallet_successfully_create'=>$_rd));
	}
	
	public function rpc_wallet_authentication($data=array()){
		if(!$this->_hdv){$this->_hdv= new \mcf\errors\mcf_html_data_validator();}
		
		$r=$this->_hdv->check_for_empty_fields_combined($data,'_mf',true);
		if(!$r['_valid']){
			$_errc=$this->_hdv->create_err_codes($r['_err']['_fields'],'_application.codes.errors.wallets');
			$r['_err']['_codes']=$_errc;
			return array('_err'=>array('_fields'=>$r['_err']['_fields']),'_render'=>array('error-fields'=>$_errc));
        }
		$_r=$this->ethereum->personal_unlockAccount($data['wallet'],$data['password']);
		if(!empty($_r['_err'])){
			$_errc=$this->_hdv->create_err_codes($_r['_err'],'_application.codes.errors.wallets');
			return array('_err'=>array(),'_render'=>array('error-fields'=>$_errc));
		}
		session_start();
		$_r=$this->ethereum->contract_balanceOf(array('target'=>$data['wallet']));
		$balance=$_r['_data']['balance'];
		$_SESSION['eth_coin']=serialize(array('wallet'=>$data['wallet'],'password'=>$data['password']));
		return array('_err'=>false,'_data'=>array('wallet'=>$data['wallet'],'balance'=>$balance),'_render'=>array('wallet_authentication_success'=>array('wallet'=>$data['wallet'])));
	}
	
	public function rpc_wallet_buy_tokens($data=array()){		
		if(!$this->_hdv){$this->_hdv= new \mcf\errors\mcf_html_data_validator();}
		
		$r=$this->_hdv->check_for_empty_fields_combined($data,'_mf',true);
		if(!$r['_valid']){
			$_errc=$this->_hdv->create_err_codes($r['_err']['_fields'],'_application.codes.errors.wallets');
			$r['_err']['_codes']=$_errc;
			return array('_err'=>array('_fields'=>$r['_err']['_fields']),'_render'=>array('error-fields'=>$_errc));
        }		

		$_r=$this->ethereum->contract_transfer(array('target'=>$data['wallet'],'amount'=>$data['amount']));
		
		if($_r['_err']){
			$_errc=$this->_hdv->create_err_codes($_r['_err'],'_application.codes.errors.wallets');
			return array('_err'=>array(),'_render'=>array('error-fields'=>$_errc));
		}
		$_rd=array('transaction'=>$_r['_data']['transaction']);
		return array('_err'=>array(),'_data'=>$_rd,'_render'=>array('transaction_successfully_send'=>$_rd));
	}
	
	
	public function transfer_coins($wallet_key,$to_address='0x0',$amount=0){
		$args=$this->_get_token_transfer_settings($wallet_key,$to_address,$amount);
		$_r=$this->ethereum->contract_transfer($args);
		return $_r;
	}
	
/*
	private function sendfiletobrowser($filename=false){
		if(empty($filename) || !is_readable($filename)){return false;}

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		header('Content-Type: '.finfo_file($finfo, $filename));
		finfo_close($finfo);
		header('Content-Disposition: attachment; filename='.basename($filename));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filename));
		ob_clean();
		flush();
		readfile($filename);
	}
*/

}
?>