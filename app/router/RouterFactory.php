<?php

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


class RouterFactory
{
	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;
        $router[] = new Route('users/sign/in', 'Front:Sign:in');
        $router[] = new Route('users/sign/out', 'Front:Sign:out');
        $router[] = new Route('users/renew_password/<token>', 'Front:Password:renew');
        $router[] = new Route('users/forgotten_password', 'Front:Password:forgotten');
		$router[] = new Route('<presenter>/<action>[/<id>]', 'User:Homepage:default');
		return $router;
	}
}
