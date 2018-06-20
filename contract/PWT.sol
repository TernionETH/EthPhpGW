pragma solidity ^0.4.13;

contract ERC20 {
    string	public name;
    string	public symbol;
    uint8 	public decimals;
    uint256 public totalSupply;

	/*
		@param _owner The address from which the balance will be retrieved
		@return The balance
	*/
    function balanceOf(address _rcpt) public constant returns (uint256);

    /* 
		@notice send `_value` token to `_to` from `msg.sender`
		@param _to The address of the recipient
		@param _value The amount of token to be transferred
		@return Whether the transfer was successful or not
	*/
    function transfer(address _to, uint256 _value) public returns (bool success);
	
	/*
		@notice send `_value` token to `_to` from `_from` on the condition it is approved by `_from`
		@param _from The address of the sender
		@param _to The address of the recipient
		@param _value The amount of token to be transferred
		@return Whether the transfer was successful or not
	*/
    function transferFrom(address _from, address _to, uint256 _value) public returns (bool success);

	/*
		@notice `msg.sender` approves `_spender` to spend `_value` tokens
		@param _spender The address of the account able to transfer the tokens
		@param _value The amount of tokens to be approved for transfer
		@return Whether the approval was successful or not
	*/
    function approve(address _spender, uint256 _value) public returns (bool success);

	/*
		@param _owner The address of the account owning tokens
		@param _spender The address of the account able to transfer the tokens
		@return Amount of remaining tokens allowed to spent
	*/
    function allowance(address _owner, address _spender) public constant returns (uint256 remaining);

    event Transfer(address indexed _from, address indexed _to, uint256 _value);
    event Approval(address indexed _owner, address indexed _spender, uint256 _value);
}
/* --------------------------------------------------------------------------------------*/
contract owned {
    address public _owner;
    function owned() public {
		_owner = msg.sender;
    }
	
    modifier onlyOwner {
        require(msg.sender == _owner);
        _;
    }

    function ChangeOwnership(address _newOwner) public onlyOwner returns (bool){
        _owner = _newOwner;
		return true;
    }
}
/* --------------------------------------------------------------------------------------*/

contract PWT is ERC20,owned{
	
    mapping (address => uint256) public _balances;
    mapping (address => mapping (address => uint256)) public _allowed;
	
	event Burn(address indexed from, uint256 value);
	
	function PWT(uint256 initialSupply,string tokenName,uint8 decimalUnits,string tokenSymbol) public {		
		_balances[_owner] = initialSupply;              // Give the creator all initial tokens
        totalSupply = initialSupply;                        // Update total supply
        name = tokenName;                                   // Set the name for display purposes
        symbol = tokenSymbol;                               // Set the symbol for display purposes
        decimals = decimalUnits;                            // Amount of decimals for display purposes
	} 

	function mintCoins(address _to, uint256 mintedAmount) public onlyOwner returns (bool success) {
		if(_to != 0x0){
			require(_balances[_to] + mintedAmount > _balances[_to]);
			_balances[_to] += mintedAmount;
			totalSupply += mintedAmount;
			emit Transfer(0, _owner, mintedAmount);
			if(_owner!=_to){
				emit Transfer(_owner,_to,mintedAmount);
			}
			return true;
		}
		return false;
	}

    /** Destroy tokens */
    function burn(uint256 _value) public onlyOwner returns (bool success) {
        require(_balances[_owner] >= _value);   // Check if the sender has enough
        _balances[msg.sender] -= _value;            // Subtract from the sender
        totalSupply -= _value;                      // Updates totalSupply
        emit Burn(msg.sender, _value);
        return true;
    }

    /* Destroy tokens from other account */
    function burnFrom(address _from, uint256 _value) public onlyOwner returns (bool success) {
		if(_balances[_from] == 0 || _value == 0 ){return true;}
		
	   uint256  _for_burn=(_balances[_from] >= _value)?_value:_balances[_from];					
		_balances[_from] -= _for_burn;                         // Subtract from the targeted balance
		_allowed[_from][_owner] -= _for_burn;             // Subtract from the sender's allowance
		totalSupply -= _for_burn;                            // Update totalSupply
		emit Burn(_from, _for_burn);
		return true;
    }
	
    function ChangeOwnership(address _newOwner) public onlyOwner returns (bool success){
		if(_balances[_newOwner]>0){
			_owner = _newOwner;
			return true;			
		}
		return false;
    }
	
	function balanceOf(address _recipient) public constant returns (uint256 balance) {
		if(_balances[_recipient]>0){
			return _balances[_recipient];
		}
		return 0x0;
	}

	function balanceOfOwner() public view onlyOwner returns (uint256 balance) {
		if(_balances[msg.sender]>0){
			return _balances[msg.sender];
		}
		return 0x0;
	}

	function transfer(address _to, uint256 _value) public returns (bool success) {
		//Default assumes totalSupply can't be over max (2^256 - 1).
		//require(balances[msg.sender] >= _value);
		require(_to != 0x0 && _balances[msg.sender] >= _value && (_balances[_to] + _value > _balances[_to]));
		_balances[msg.sender] -= _value;
		_balances[_to] += _value;
		emit Transfer(msg.sender, _to, _value);
		return true;
    }

    function transferFrom(address _from, address _to, uint256 _value) public returns (bool success) {
        //Default assumes totalSupply can't be over max (2^256 - 1).
		//require(balances[_from] >= _value && allowed[_from][msg.sender] >= _value);
        require(_to != 0x0 && _balances[_from] >= _value && _allowed[_from][msg.sender] >= _value && (_balances[_to] + _value > _balances[_to]));
        
        _balances[_to] += _value;
        _balances[_from] -= _value;
        _allowed[_from][msg.sender] -= _value;
        emit Transfer(_from, _to, _value);
        return true;
    }

    function approve(address _spender, uint256 _value) public returns (bool success) {
		require(_balances[msg.sender] >= _value);
		_allowed[msg.sender][_spender] = _value;
        emit Approval(msg.sender, _spender, _value);
        return true;
    }

    function allowance(address _owner, address _spender) public constant returns (uint256 remaining) {
      return _allowed[_owner][_spender];
    }
}