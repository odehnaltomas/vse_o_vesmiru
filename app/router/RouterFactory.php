<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;

		$router[] = new Route('[<locale=cs cs|en>/]article/article-list/page-<visualPaginator-page>', "Article:articleList");

		$router[] = new Route('[<locale=cs cs|en>/]article/<action>/<articleId>', "Article:show");

		$router[] = new Route('[<locale=cs cs|en>/]user/<action>/<userId>', "User:editProfile");

		$router[] = new Route('[<locale=cs cs|en>/]<presenter>/<action>', "Homepage:default");
		return $router;
	}

}
