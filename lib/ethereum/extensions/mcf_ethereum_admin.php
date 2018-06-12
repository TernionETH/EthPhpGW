<?php
namespace mcf\ethereum\extensions;

class mcf_ethereum_admin{
	private $_rpc_send=false;
	
	public function __construct($_rpc_send=false){
		$this->_rpc_send=$_rpc_send;
	}
	
	public function __call($name,$args=array()){
		
	}
}


/*
You would need to open the console (CMD/CTRL + ALT + i) and type: CustomContracts.find().fetch()

Then look at the contract you want to remove and copy its _id. Then type: CustomContracts.remove('the_id_you_copied')


*/


?>