<?php
use Aquino\Core\JWT\JWT;
use Aquino\Core\ApiModule;

class TestApi extends ApiModule
{
	function __construct() {
	    parent::__construct('test');
  	}

	public function teste_post(){

		return $this->response->withJson(array('oi'));
	}

	public function str_post(){

		//$t = $this->body;
		
		$jwt = JWT::encode(array('a' => 5), "saasdasdas");

		return $jwt;
	}

	public function auth_get(){
		//JWT::$leeway = 10;
		$jwt = JWT::encode(array('user' => 5 , 'exp' => (time() + 10) ), "kokymymw");
		return $jwt;
	}

	protected function deleta_get(){
		return "Estou protegido " . json_encode($this->getSession());
	}

	public function conectaBanco_get(){
		return $this->QB('default')->table('teste')->get();
	}

	public function session_get($key){

		$auh = $this->getContainer('auth_classs');

		if(empty($auh)){
			return "lol";
		}

		return $auh->TokenExpired();
		JWT::$leeway = 10;

		try
		{
			$decoded = JWT::decode($key, "kokymymw", array('HS256'));
			return (array) $decoded;
		}catch(BeforeValidException $e){
			return "BeforeValidException";

		}catch(ExpiredException $e){
			return "ExpiredException";

		}catch(SignatureInvalidException $e){
			return "SignatureInvalidException";

		}catch(Exception $e){

			if($e instanceof ExpiredException){
				return "ExpiredException";
			}
			//return "Exception" . get_class($e);

			switch (get_class($e)) {
				case "ExpiredException":
				return "exp";
				break;
			}
		}

		
		return "nadi";
		//if($decoded instanceof KeyParserError){
		//	echo "key invalida";
		//}

		
	}
}
?>