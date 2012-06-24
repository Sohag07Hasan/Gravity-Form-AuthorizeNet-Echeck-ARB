<?php
/*
 * Helper class to control the curl request and parsing the request for both Echeck and ARB
 * */

class AuthorizeNetEcheckARB{
	
	//api url based on November 2011 release
	const TRANSACTION_TYPE = 'WEB';
	const LIVE_URL = 'https://secure.authorize.net/gateway/transact.dll';
    const SANDBOX_URL = 'https://test.authorize.net/gateway/transact.dll';

	//setting private variables and aim fields	
	
	private $x_login = '';
	private $x_tran_key = '';
	
	//will add x_ prefix while parsing
	private $aim_fields = array();
	
	function __construct($login, $tran_key){
		$this->x_login;
		$this->x_tran_key;
	}
	
	
	/*
	 * returns true if the authorizenet credentials are supplied
	 * */
	public function is_credential_supplied(){
		if($this->x_login = '' || $this->x_tran_key = '') {
			return false;
		}
		else{
			return true;
		}
	}
}