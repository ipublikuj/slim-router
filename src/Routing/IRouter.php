<?php declare(strict_types = 1);

/**
 * IRouter.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Routing
 * @since          0.1.0
 *
 * @date           14.03.20
 */

namespace IPub\SlimRouter\Routing;

use IteratorAggregate;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * @phpstan-extends IteratorAggregate<int, IRoute>
 */
interface IRouter extends IteratorAggregate
{

	/**
	 * @return string
	 */
	public function getBasePath(): string;

	/**
	 * @param string $basePath
	 *
	 * @return void
	 */
	public function setBasePath(string $basePath): void;

	/**
	 * @param string $name
	 *
	 * @return IRoute|null
	 */
	public function getNamedRoute(string $name): ?IRoute;

	/**
	 * @param string $identifier
	 *
	 * @return IRoute
	 */
	public function lookupRoute(string $identifier): IRoute;

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
	 * Add route with multiple methods
	 *
	 * @param string[] $methods                 Numeric array of HTTP method names
	 * @param string $pattern                   The route URI pattern
	 * @param callable|string|mixed[] $callable The route callback routine
	 *
	 * @return IRoute
	 */
	public function map(array $methods, string $pattern, $callable): IRoute;

	/**
	 * Route Groups
	 *
	 * This method accepts a route pattern and a callback. All route
	 * declarations in the callback will be prepended by the group(s)
	 * that it is in.
	 *
	 * @param string $pattern
	 * @param callable $callable
	 *
	 * @return IRouteGroup
	 */
	public function group(string $pattern, callable $callable): IRouteGroup;

	/**
	 * Build the path for a named route including the base path
	 *
	 * @param string $routeName    Route name
	 * @param mixed[] $data        Named argument replacement data
	 * @param mixed[] $queryParams Optional query string parameters
	 *
	 * @return string
	 */
	public function urlFor(string $routeName, array $data = [], array $queryParams = []): string;

	/**
	 * @param ServerRequestInterface $request
	 *
	 * @return ResponseInterface
	 */
	public function handle(ServerRequestInterface $request): ResponseInterface;

}
