<?php declare(strict_types = 1);

/**
 * RouteCollector.php
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

use Fig\Http\Message\RequestMethodInterface;
use IPub\SlimRouter\Controllers;
use IPub\SlimRouter\Exceptions;
use IPub\SlimRouter\Middleware;
use IPub\SlimRouter\Routing;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * RouteCollector is used to collect routes and route groups
 * as well as generate paths and URLs relative to its environment
 */
class RouteCollector implements IRouteCollector
{

	/** @var string */
	private string $pattern;

	/** @var IRouteCollector|null */
	private ?IRouteCollector $routeCollector = null;

	/** @var IRouteParser */
	private IRouteParser $routeParser;

	/** @var Controllers\IControllerResolver */
	private Controllers\IControllerResolver $controllerResolver;

	/** @var Routing\Handlers\IHandler */
	private Routing\Handlers\IHandler $defaultInvocationHandler;

	/** @var IRoute[] */
	private array $routes = [];

	/** @var IRouteGroup[] */
	private array $groups = [];

	/** @var MiddlewareInterface[] */
	private array $middleware = [];

	/** @var ResponseFactoryInterface */
	private ResponseFactoryInterface $responseFactory;

	public function __construct(
		ResponseFactoryInterface $responseFactory,
		Controllers\IControllerResolver $controllerResolver,
		IRouteParser $routeParser,
		?IRouteCollector $routeCollector = null,
		?Routing\Handlers\IHandler $defaultInvocationHandler = null,
		string $pattern = ''
	) {
		$this->responseFactory = $responseFactory;
		$this->controllerResolver = $controllerResolver;
		$this->routeCollector = $routeCollector;
		$this->defaultInvocationHandler = $defaultInvocationHandler ?? new Routing\Handlers\RequestResponseHandler();
		$this->routeParser = $routeParser;

		$this->pattern = $pattern;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefaultInvocationHandler(Routing\Handlers\IHandler $strategy): void
	{
		$this->defaultInvocationHandler = $strategy;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPattern(): string
	{
		return ($this->routeCollector !== null ? $this->routeCollector->getPattern() : '') . $this->pattern;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRoutes(): array
	{
		$routes = [];

		$routes = array_merge($routes, $this->routes);

		foreach ($this->groups as $group) {
			$routes = array_merge($routes, $group->getRouteCollector()->getRoutes());
		}

		return $routes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getNamedRoute(string $name, bool $throw = true): ?IRoute
	{
		foreach ($this->routes as $route) {
			if ($name === $route->getName()) {
				return $route;
			}
		}

		foreach ($this->groups as $group) {
			$route = $group->getRouteCollector()->getNamedRoute($name, false);

			if ($route !== null) {
				return $route;
			}
		}

		if ($throw) {
			throw new Exceptions\RuntimeException('Named route does not exist for name: ' . $name);
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeNamedRoute(string $name): bool
	{
		$route = $this->getNamedRoute($name);

		if ($route !== null && isset($this->routes[$route->getIdentifier()])) {
			unset($this->routes[$route->getIdentifier()]);

			return true;
		}

		foreach ($this->groups as $group) {
			$result = $group->getRouteCollector()->removeNamedRoute($name);

			if ($result) {
				return true;
			}
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function lookupRoute(string $identifier, bool $throw = true): ?IRoute
	{
		if (isset($this->routes[$identifier])) {
			return $this->routes[$identifier];
		}

		foreach ($this->groups as $group) {
			$route = $group->getRouteCollector()->lookupRoute($identifier, false);

			if ($route !== null) {
				return $route;
			}
		}

		if ($throw) {
			throw new Exceptions\RuntimeException('Route not found, looks like your route cache is stale.');
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMiddleware(MiddlewareInterface $middleware): void
	{
		$this->middleware[] = $middleware;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $pattern, $callable): IRoute
	{
		return $this->map([RequestMethodInterface::METHOD_GET], $pattern, $callable);
	}

	/**
	 * {@inheritDoc}
	 */
	public function post(string $pattern, $callable): IRoute
	{
		return $this->map([RequestMethodInterface::METHOD_POST], $pattern, $callable);
	}

	/**
	 * {@inheritDoc}
	 */
	public function put(string $pattern, $callable): IRoute
	{
		return $this->map([RequestMethodInterface::METHOD_PUT], $pattern, $callable);
	}

	/**
	 * {@inheritDoc}
	 */
	public function patch(string $pattern, $callable): IRoute
	{
		return $this->map([RequestMethodInterface::METHOD_PATCH], $pattern, $callable);
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(string $pattern, $callable): IRoute
	{
		return $this->map([RequestMethodInterface::METHOD_DELETE], $pattern, $callable);
	}

	/**
	 * {@inheritDoc}
	 */
	public function options(string $pattern, $callable): IRoute
	{
		return $this->map([RequestMethodInterface::METHOD_OPTIONS], $pattern, $callable);
	}

	/**
	 * {@inheritDoc}
	 */
	public function any(string $pattern, $callable): IRoute
	{
		return $this->map([
			RequestMethodInterface::METHOD_GET,
			RequestMethodInterface::METHOD_POST,
			RequestMethodInterface::METHOD_PUT,
			RequestMethodInterface::METHOD_PATCH,
			RequestMethodInterface::METHOD_DELETE,
			RequestMethodInterface::METHOD_OPTIONS,
		], $pattern, $callable);
	}

	/**
	 * {@inheritDoc}
	 */
	public function map(array $methods, string $pattern, $handler): IRoute
	{
		$route = $this->createRoute($methods, $pattern, $handler);

		$this->routes[$route->getIdentifier()] = $route;

		return $route;
	}

	/**
	 * {@inheritDoc}
	 */
	public function group(string $pattern, callable $callable): IRouteGroup
	{
		$routeCollector = new RouteCollector(
			$this->responseFactory,
			$this->controllerResolver,
			$this->routeParser,
			$this,
			$this->defaultInvocationHandler,
			$pattern
		);

		$group = new RouteGroup($pattern, $routeCollector);

		$this->groups[] = $group;

		$callable($routeCollector);

		return $group;
	}

	/**
	 * {@inheritdoc}
	 */
	public function appendMiddlewareToDispatcher(Middleware\MiddlewareDispatcher $dispatcher): void
	{
		foreach ($this->middleware as $middleware) {
			$dispatcher->add($middleware);
		}

		if ($this->routeCollector !== null) {
			$this->routeCollector->appendMiddlewareToDispatcher($dispatcher);
		}
	}

	/**
	 * @param string[] $methods
	 * @param string $pattern
	 * @param callable|string|mixed[] $callable
	 *
	 * @return IRoute
	 */
	private function createRoute(array $methods, string $pattern, $callable): IRoute
	{
		return new Route(
			$methods,
			$pattern,
			$callable,
			$this,
			$this->responseFactory,
			$this->controllerResolver,
			$this->defaultInvocationHandler
		);
	}

}
