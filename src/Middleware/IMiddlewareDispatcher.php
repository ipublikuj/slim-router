<?php declare(strict_types = 1);

/**
 * IMiddlewareDispatcher.php
 *
 * @license        More in license.md
 * @copyright      https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Middleware
 * @since          0.1.0
 *
 * @date           15.03.20
 */

namespace IPub\SlimRouter\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Router middleware dispatcher interface
 *
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Middleware
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IMiddlewareDispatcher extends RequestHandlerInterface
{

	/**
	 * Add a new middleware to the stack
	 *
	 * Middleware are organized as a stack. That means middleware
	 * that have been added before will be executed after the newly
	 * added one (last in, first out).
	 *
	 * @param MiddlewareInterface $middleware
	 *
	 * @return void
	 */
	public function add(MiddlewareInterface $middleware): void;

	/**
	 * Seed the middleware stack with the inner request handler
	 *
	 * @param RequestHandlerInterface $kernel
	 *
	 * @return void
	 */
	public function seedMiddlewareStack(RequestHandlerInterface $kernel): void;

}
