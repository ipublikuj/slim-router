<?php declare(strict_types = 1);

/**
 * IRouteCollector.php
 *
 * @license        More in license.md
 * @copyright      https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Routing
 * @since          0.1.0
 *
 * @date           14.03.20
 */

namespace IPub\SlimRouter\Routing;

use IPub\SlimRouter\Middleware;
use IPub\SlimRouter\Routing;
use Psr\Http\Server\MiddlewareInterface;

interface IRouteCollector
{

	/**
	 * @param Routing\Handlers\IHandler $strategy
	 *
	 * @return void
	 */
	public function setDefaultInvocationHandler(Routing\Handlers\IHandler $strategy): void;

	/**
	 * @return string
	 */
	public function getPattern(): string;

	/**
	 * @return IRoute[]
	 */
	public function getRoutes(): array;

	/**
	 * @param string $name
	 * @param bool $throw
	 *
	 * @return IRoute|null
	 */
	public function getNamedRoute(string $name, bool $throw = true): ?IRoute;

	/**
	 * @param string $name Route name
	 *
	 * @return bool
	 */
	public function removeNamedRoute(string $name): bool;

	/**
	 * @param string $identifier
	 * @param bool $throw
	 *
	 * @return IRoute|null
	 */
	public function lookupRoute(string $identifier, bool $throw = true): ?IRoute;

	/**
	 * @param MiddlewareInterface $middleware
	 *
	 * @return void
	 */
	public function addMiddleware(MiddlewareInterface $middleware): void;

	/**
	 * Add GET route
	 *
	 * @param string $pattern                   The route URI pattern
	 * @param callable|string|mixed[] $callable The route callback routine
	 *
	 * @return IRoute
	 */
	public function get(string $pattern, $callable): IRoute;

	/**
	 * Add POST route
	 *
	 * @param string $pattern                   The route URI pattern
	 * @param callable|string|mixed[] $callable The route callback routine
	 *
	 * @return IRoute
	 */
	public function post(string $pattern, $callable): IRoute;

	/**
	 * Add PUT route
	 *
	 * @param string $pattern                   The route URI pattern
	 * @param callable|string|mixed[] $callable The route callback routine
	 *
	 * @return IRoute
	 */
	public function put(string $pattern, $callable): IRoute;

	/**
	 * Add PATCH route
	 *
	 * @param string $pattern                   The route URI pattern
	 * @param callable|string|mixed[] $callable The route callback routine
	 *
	 * @return IRoute
	 */
	public function patch(string $pattern, $callable): IRoute;

	/**
	 * Add DELETE route
	 *
	 * @param string $pattern                   The route URI pattern
	 * @param callable|string|mixed[] $callable The route callback routine
	 *
	 * @return IRoute
	 */
	public function delete(string $pattern, $callable): IRoute;

	/**
	 * Add OPTIONS route
	 *
	 * @param string $pattern                   The route URI pattern
	 * @param callable|string|mixed[] $callable The route callback routine
	 *
	 * @return IRoute
	 */
	public function options(string $pattern, $callable): IRoute;

	/**
	 * Add route for any HTTP method
	 *
	 * @param string $pattern                   The route URI pattern
	 * @param callable|string|mixed[] $callable The route callback routine
	 *
	 * @return IRoute
	 */
	public function any(string $pattern, $callable): IRoute;

	/**
	 * Add route
	 *
	 * @param string[] $methods                Array of HTTP methods
	 * @param string $pattern                  The route pattern
	 * @param callable|string|mixed[] $handler The route callable
	 *
	 * @return IRoute
	 */
	public function map(array $methods, string $pattern, $handler): IRoute;

	/**
	 * Add route group
	 *
	 * @param string $pattern
	 * @param callable $callable
	 *
	 * @return IRouteGroup
	 */
	public function group(string $pattern, callable $callable): IRouteGroup;

	/**
	 * @param Middleware\MiddlewareDispatcher $dispatcher
	 */
	public function appendMiddlewareToDispatcher(Middleware\MiddlewareDispatcher $dispatcher): void;

}
