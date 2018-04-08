<?php
/**
 * Created by PhpStorm.
 * User: thiagoalencar
 * Date: 08/04/18
 * Time: 04:00
 */
/**
 * Class RoutesController
 * @package UberHack\controllers
 */

namespace UberHack\controllers;

use UberHack\interfaces\ControllerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class RoutesController extends Controller implements ControllerInterface {

	private $response;
	private $request;
	private $args;

	public function __construct( ServerRequestInterface $request, ResponseInterface $response, $args ) {
		$this->response = $response;
		$this->request  = $request;
		$this->args = $args;
	}

	private function getAllRotes( $user_id ) {

		$sql = sprintf(
	"SELECT
			  routes.name,
			  routes.id,
			  routes.cordinates,
			  routes.working_days,
			  CASE
			    WHEN favorite_routes.route_id IS NULL THEN 'N'
			    ELSE 'S'
			  END AS 'FAVORITE'
			FROM
			  routes
			LEFT JOIN
			  favorite_routes on routes.id = favorite_routes.route_id AND favorite_routes.route_id = '%s';",
	$user_id);
		$routes = array();
		$result = (array) $this->query( $sql );

		array_walk( $result,function( $route ) use ( &$routes ) {
			if ( is_array($route) ) {
				$routes[] = array(
					"id"            => (int) $route["id"],
					"name"          => $route["name"],
					"cordinates"    => json_decode($route["cordinates"], true ),
					"working_days"  => json_decode($route["working_days"], true ),
					"bookmark"      => ($route["working_days"] == 'S')
				);
			}
		});

		return $routes;
	}


	function render() {
		return [ $this->getAllRotes( $this->args['id'] ), 200 ];
	}
}