<?php 
namespace Aquino\Core\JWT;

interface ApiTokenValidation
{
	public function Session($decrypted_token);
	public function TokenExpired();
	public function InvalidToken();
	public function TokenNotFound();
	public function Auth_post();
}