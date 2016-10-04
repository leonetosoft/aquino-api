<?php
namespace Aquino\Core;

use Aquino\Core\JWT\JWT;

use ReflectionMethod;

class ApiModule {
  protected $type;
  protected $user = NULL;
  protected $app = NULL;
  protected $body = NULL;
  protected $request;
  protected $response;
  protected $json = NULL;
  protected $container;
  private $JWT_SESSION = NULL;

  function __construct($identify) {
    
    global $app;
    $this->app = $app;

    $this->container = $this->app->getContainer();

    if($this instanceof ApiTokenValidation){
       $this->container['auth_class'] = $this;
    }
    
    $class = $this;
    $processRequestFunc = function($request, $response, $args) use($class){

      //Coleta os parametros da pagina
      $params = explode('/', $request->getAttribute('params'));
      //Passando para JSON o header
      $class->body = $request->getParsedBody();
      $class->json = json_decode(json_encode($class->body));
      //Split parametro para produzir o chamado a classe
      $paramsCall =  $params[0] . '_' . strtolower($request->getMethod());

      //Verificando se o método existe
      if(!method_exists($class, $paramsCall)){
        return $response->withJson(['msg' => ("Function not found!"), 'status' => 500 , 'type' => 'ERROR']);
      }

      //remove call of array
      unset($params[0]);

      $ref = new ReflectionMethod($class, $paramsCall);

      //verify if function is private
      if($ref->isPrivate()){
        return $response->withJson(['msg' => "Access Violation! Function is private!" , 'status' => 500 , 'type' => 'ERROR']);
      }

      if($ref->isProtected()){
        $auhApiClass = $class->getContainer('auth_class');

        if($auhApiClass){
           $token = $request->getHeader($class->getConfig('auth', 'headerToken'));
           if(sizeof($token) == 1){
              try
              {
                $decoded = JWT::decode($token[0], $class->getConfig('auth', 'awtKey'), array('HS256'));
                $class->JWT_SESSION = $decoded;
                //Renovar sessao
                $response = $response->withAddedHeader('Token' , $class->JWToken($decoded));
              }catch(Exception $e){
                switch (get_class($e)) {
                  case 'UnexpectedValueException':
                    return $response->withJson($auhApiClass->InvalidToken())->withStatus(401);
                  break;

                  default:
                    return $response->withJson($auhApiClass->TokenExpired())->withStatus(401);
                  break;
                }
                return $response->withJson(['msg' => get_class($e) , 'type' => 'ERROR']);
              }

           }else{
            return $response->withJson($auhApiClass->TokenNotFound())->withStatus(401);
           }

        }else{
          return $response->withJson(['msg' => "API ApiTokenValidation not found!" , 'type' => 'ERROR'])->withStatus(401);
        }
      }

      //Checando o numero de parametros
      if($ref->getNumberOfParameters() != sizeof($params))
      {
        return $response->withJson(['msg' => "Function not contains " . sizeof($params) . " params." , 'status' => 500 , 'type' => 'ERROR']);
      }

      $class->response = $response;
      $class->request = $request;

      $callFunc = call_user_func_array(array($class, $paramsCall), $params);

      $return;
      if(empty($callFunc)){
        $return = $response->withJson(['msg' => "Return api is empty" , 'status' => 500 , 'type' => 'ERROR'])->withStatus(500);
      }
      else if($callFunc instanceof Response){
        $return = $callFunc;
      }
      else if(is_string($callFunc)){
        $return = $response->withJson(array($callFunc));
      }
      else if(is_array($callFunc)){
        $return = $response->withJson($callFunc);
      }
      else if(is_object($callFunc)){
        $return = $response->withJson($callFunc);
      }

      return $return;
    };


    $this->app->map(['GET', 'POST', 'DELETE', 'PUT'],'/api/' . $identify. '[/{params:.*}]', $processRequestFunc);

  }

  protected function getContainer($container_name){
    if(isset($this->container[$container_name])){
      return $this->container[$container_name];
    }
    return false;
  }

  //retorna instancia do query builder
  //doc: https://github.com/usmanhalalit/pixie#alias
  protected function QB($con_cfg = NULL){

    if($con_cfg == NULL){
      $conf = $this->getConfig('databaseQueryBuilder', 'default');
    }else{
      $conf = $this->getConfig('databaseQueryBuilder', $con_cfg);
    }

    echo $this->container['config'];
    
    $connection = new \Pixie\Connection($conf['driver'], $conf);
    return new \Pixie\QueryBuilder\QueryBuilderHandler($connection);
  }

  protected function getSession(){
    return $this->JWT_SESSION;
  }

  public function JWToken($object){
    $object = (array)$object;
    $object['exp'] = (time() + intval($this->getConfig('auth', 'authTime')));
    return JWT::encode($object, $this->getConfig('auth', 'awtKey'));
  }

  protected function getConfig($name, $key){
    //print_r($this->container['config']);exit;
    if(isset($this->container['config'])){
      $conf = $this->container['config'];
      if(isset($conf[$name])){
        $confProp = $conf[$name];
        if(isset($confProp[$key]))
        {
          $valueProp = $confProp[$key];
          return $valueProp;
        }
      }     
    }
    return false;
  }

  protected function Body(){
    return $this->body;
  }

  protected function putRet($code, $msgdta = 'Request success process!', $type = NULL){

    if(is_array($msgdta) && sizeof($msgdta) === 0){
      $this->return_data = [$code, $this->return_data[1]];
      return;
    }

    if(is_array($msgdta) && $type !== NULL)
    {
      $msg_dta_arr = [];
      foreach ($msgdta as $msg) {
      # code...
        $msg_dta_arr[] = ['msg' => $msg , 'type' => $type];
      }

      $this->return_data = [$code, $msg_dta_arr];
      return;
    }

    if(is_string($msgdta))
    {
      $msgdta = [['msg' => $msgdta, 'type' => $type]];
    }

    $this->return_data = [$code, $msgdta];
  }


}

 ?>
