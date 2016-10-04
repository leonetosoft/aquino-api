<?php 
/**
 *
 * Classe que implementa ApiTokenValidation
 * Para autenticação
 */
//use Aquino\JWT\ApiTokenValidation;
use Aquino\Core\ApiModule;
use Aquino\Core\JWT\ApiTokenValidation;

class AuthApi extends ApiModule implements ApiTokenValidation
{
	function __construct() {
	    parent::__construct('auth');
  	}

	public function Session($decrypted_token){
		return true;
	}

	public function TokenExpired(){
		return ['msg' => 'Token Expired'];
	}

	public function InvalidToken(){
		return ['msg' => 'Invalid Token'];
	}

	public function TokenNotFound(){
		return ['msg' => 'Uknow token !'];
	}
	
	public function Auth_post(){
		return $this->JWToken(['leonardo' => "neto"]);
	}
}