<?php declare(strict_types = 1);

/**
 * IControllerResolver.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           14.04.19
 */

namespace IPub\SlimRouter\Controllers;

/**
 * Endpoint controller callback resolver interface
 *
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IControllerResolver
{

	/**
	 * Resolve $toResolve into a callable
	 *
	 * @param string|callable|mixed[] $toResolve
	 *
	 * @return callable
	 */
	public function resolve($toResolve): callable;

}
