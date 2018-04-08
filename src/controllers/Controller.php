<?php
/**
 * Created by PhpStorm.
 * User: thiagoalencar
 * Date: 08/04/18
 * Time: 06:25
 */
/**
 * Class Controller
 * @package UberHack\controllers
 */


namespace UberHack\controllers;
use UberHack\controllers\AuthenticationController;
use UberHack\interfaces\ControllerInterface;
use Psr\Http\Message\ServerRequestInterface;
use PDO;

class Controller {
	public function getUserData(){
		$request = $this->request->getHeader('Authorization');
		return AuthenticationController::extractUserData(array_shift( $request ));
	}

	public function query( $sql ) {

		try {
			$db = new PDO("sqlite:" . __DIR__ . "/../../db/db.sqlite", '', '', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") );
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);


			$sth = $db->query($sql);
		}
		catch(PDOException $e) {
			# There was an error, die
			die('There was an error.');
		}

		$sth->setFetchMode(PDO::FETCH_ASSOC);
		return $sth->fetchAll();

	}
}