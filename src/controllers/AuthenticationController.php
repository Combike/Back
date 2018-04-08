<?php
/**
 * Class AuthenticationController
 * @package UberHack\controllers
 */

namespace UberHack\controllers;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Firebase\JWT\JWT;
use UberHack\interfaces\ControllerInterface;

class AuthenticationController extends controller implements ControllerInterface {
	private $data;

	public function __construct( ServerRequestInterface $request, ResponseInterface $response ) {
		$this->setData($request->getBody() );
		return $this;
	}

	public function setData( $data ): void {
		$this->data = json_decode($data);
	}

	private function authenticate() {
		$authenticationData = $this->data;

		$return = array(
			"authenticate" => false,
			"error" => ""
		);


		if ( isset( $authenticationData->username ) && isset( $authenticationData->password ) ) {

			$sql = sprintf( "SELECT id, email, nome, password FROM users WHERE email = '%s';", $authenticationData->username);

			foreach ( $this->query($sql,ARRAY_FILTER_USE_BOTH) as $row ) {
				$password_hash = $row['password'];
				$data = array(
					"id"       => $row['id'],
					"username" => $row['nome'],
					"email"    => $row['email'],
				);
			};

			if ( isset($password_hash) &&  password_verify( $authenticationData->password,$password_hash ) ) {
				return array(
					"authenticate" => true,
					"data" => $data
				);
			} else {
				$return['authenticate'] = false;
				$return['error'] = "Usuário ou Senha incorretos";
			}
		} else {
			$return['error'] = "Usuário ou Senha não definida.";
		}

		return $return;

	}

	private function getToken( $data ){
		$secret = UBH_SECRET;
		$token = JWT::encode($data, UBH_SECRET);
		return array( "token" =>  $token );
	}

	public static function extractUserData( $token ){
		try {
			$token = is_array($token) ? array_shift( $token) : $token;
			$jwt = (array) JWT::decode( $token, "EM6b3twPJ32PbrCQwa7qWXrVjCcu", array('HS256') );
		} catch ( \Exception $exception ) {
			return [ 'valid' => false ];
		}
		return $jwt;
	}

	public static function tokenIsValid( $token ) {
		$jwt = AuthenticationController::extractUserData($token );
		return !in_array( "email",$jwt );
	}

	public function render(){
		$auth = $this->authenticate();
		if ( $auth['authenticate'] ) {
			return [ $this->getToken( $auth['data'] ), 200 ];
		} else {
			return [ $auth, 401 ];
		};
	}
}