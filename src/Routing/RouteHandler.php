<?php declare(strict_types = 1);

/**
 * Router.php
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

use FastRoute;
use FastRoute\RouteCollector as FastRouteCollector;
use FastRoute\RouteParser\Std;
use IPub\SlimRouter\Exceptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteHandler implements RequestHandlerInterface
{

	/** @var IRouter */
	private $router;

	/** @var FastRouteDispatcher|null */
	private $dispatcher;

	public function __construct(IRouter $router)
	{
		$this->router = $router;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Exceptions\HttpMethodNotAllowedException
	 * @throws Exceptions\HttpNotFoundException
	 */
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		// If routing hasn't been done, then do it now so we can dispatch
		if ($request->getAttribute(Router::ROUTING_RESULTS) === null) {
			$request = $this->performRouting($request);
		}

		$request = $request->withAttribute(
			Router::BASE_PATH,
			$this->router->getBasePath()
		);

		/** @var IRoute $route */
		$route = $request->getAttribute(Router::ROUTE);

		return $route->run($request);
	}

	/**
	 * @param ServerRequestInterface $request
	 *
	 * @return ServerRequestInterface
	 *
	 * @throws Exceptions\HttpMethodNotAllowedException
	 * @throws Exceptions\HttpNotFoundException
	 */
	public function performRouting(ServerRequestInterface $request): ServerRequestInterface
	{
		$routingResults = $this->computeRoutingResults(
			$request->getUri()->getPath(),
			$request->getMethod()
		);

		$routeStatus = $routingResults->getRouteStatus();

		$request = $request->withAttribute(Router::ROUTING_RESULTS, $routingResults);

		switch ($routeStatus) {
			case RoutingResults::FOUND:
				$routeArguments = $routingResults->getRouteArguments();
				$routeIdentifier = $routingResults->getRouteIdentifier() ?? '';

				$route = $this->router->lookupRoute($routeIdentifier);
				$route->prepare($routeArguments);

				return $request->withAttribute(Router::ROUTE, $route);

			case RoutingResults::NOT_FOUND:
				throw new Exceptions\HttpNotFoundException($request);

			case RoutingResults::METHOD_NOT_ALLOWED:
				$exception = new Exceptions\HttpMethodNotAllowedException($request);
				$exception->setAllowedMethods($this->getDispatcher()->getAllowedMethods($request->getUri()->getPath()));

				throw $exception;

			default:
				throw new Exceptions\RuntimeException('An unexpected error occurred while performing routing.');
		}
	}

	/**
	 * @param string $uri Should be $request->getUri()->getPath()
	 * @param string $method
	 *
	 * @return RoutingResults
	 */
	private function computeRoutingResults(string $uri, string $method): RoutingResults
	{
		$uri = rawurldecode($uri);

		if ($uri === '' || $uri[0] !== '/') {
			$uri = '/' . $uri;
		}

		$dispatcher = $this->getDispatcher();

		$results = $dispatcher->dispatch($method, $uri);

		return new RoutingResults($method, $uri, $results[0], $results[1], $results[2]);
	}

	/**
	 * @return FastRouteDispatcher
	 */
	private function getDispatcher(): FastRouteDispatcher
	{
		if ($this->dispatcher !== null) {
			return $this->dispatcher;
		}

		$routeDefinitionCallback = function (FastRouteCollector $r): void {
			$basePath = $this->router->getBasePath();

			/** @var IRoute $route */
			foreach ($this->router->getIterator() as $route) {
				$r->addRoute($route->getMethods(), $basePath . $route->getPattern(), $route->getIdentifier());
			}
		};

		/** @var FastRouteDispatcher $dispatcher */
		$dispatcher = FastRoute\simpleDispatcher($routeDefinitionCallback, [
			'dispatcher'  => FastRouteDispatcher::class,
			'routeParser' => new Std(),
		]);

		$this->dispatcher = $dispatcher;

		return $this->dispatcher;
	}

}
