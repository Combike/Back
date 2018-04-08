<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use UberHack\controllers\AuthenticationController;

$loader = require './vendor/autoload.php';

define( "UBH_SECRET", "EM6b3twPJ32PbrCQwa7qWXrVjCcu" );

$app = new \Slim\App;

$container = $app->getContainer();

$routes = [
	"post" => [
		[ "path" => "/authentication", "controller" => "AuthenticationController", "authenticated" => false ],
	],
	"get" => [
		[ "path" => "/routes/{id}", "controller" => "RoutesController" ],
	]
];

foreach ( $routes['post'] as $route ) {
	$app->post( $route["path"], function ( ServerRequestInterface $request, ResponseInterface $response, $args ) use($route) {

		if ( !array_key_exists( "authenticated", $route ) || $route["authenticated"] === true ) {
			$authorization = $request->getHeader("Authorization");
			if ( empty( $authorization  ) ) {
				return $response->withJson( array(
					"error" => "Token não informado",
				), 401);
			} else {
				if ( !AuthenticationController::tokenIsValid( $authorization ) ) {;
					return $response->withJson( array(
						"error" => "O Token informado não é válido",
					), 401);
				};
			}
		}

		$routeClass = "UberHack\\controllers\\" . $route["controller"];
		$controller = new $routeClass( $request, $response, $args );
		list ( $data, $code ) = $controller->render();
		return $response->withJson( (array) $data , $code );
	});
}

foreach ( $routes['get'] as $route ) {
	$app->get( $route["path"], function ( ServerRequestInterface $request, ResponseInterface $response, $args ) use($route) {
		if ( !array_key_exists( "authenticated", $route ) || $route["authenticated"] === true ) {
			$authorization = $request->getHeader("Authorization");
			if ( empty( $authorization  ) ) {
				return $response->withJson( array(
					"error" => "O Token informado não é válido",
				), 401);
			} else {
				if ( !AuthenticationController::tokenIsValid( $authorization ) ) {;
					return $response->withJson( array(
						"error" => "Token não é válido",
					), 401);
				};
			}
		}
		$routeClass = "UberHack\\controllers\\" . $route["controller"];
		$controller = new $routeClass( $request, $response, $args );
		list ( $data, $code ) = $controller->render();
		return $response->withJson( (array) $data , $code );
	});
}

$app->run();
