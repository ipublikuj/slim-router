<?php declare(strict_types = 1);

/**
 * Route.php
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

use IPub\SlimRouter\Controllers;
use IPub\SlimRouter\Exceptions;
use IPub\SlimRouter\Middleware;
use IPub\SlimRouter\Routing;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Throwable;

class Route implements IRoute, RequestHandlerInterface
{

	/** @var string[] */
	private array $methods = [];

	/** @var string */
	private string $pattern;

	/** @var string */
	private string $identifier;

	/** @var string|null */
	private ?string $name = null;

	/** @var mixed[] */
	private array $arguments = [];

	/** @var mixed[] */
	private array $savedArguments = [];

	/** @var callable|string|mixed[] */
	private $callable;

	/** @var IRouteCollector */
	private IRouteCollector $routeCollector;

	/** @var Routing\Handlers\IHandler */
	private Routing\Handlers\IHandler $invocationHandler;

	/** @var Controllers\IControllerResolver */
	private Controllers\IControllerResolver $controllerResolver;

	/** @var ResponseFactoryInterface */
	private ResponseFactoryInterface $responseFactory;

	/** @var Middleware\MiddlewareDispatcher */
	private Middleware\MiddlewareDispatcher $middlewareDispatcher;

	/** @var bool */
	private bool $groupMiddlewareAppended = false;

	/**
	 * @param string[] $methods                 The route HTTP methods
	 * @param string $pattern                   The route pattern
	 * @param callable|string|mixed[] $callable The route callable
	 * @param IRouteCollector $routeCollector
	 * @param ResponseFactoryInterface $responseFactory
	 * @param Controllers\IControllerResolver $controllerResolver
	 * @param Routing\Handlers\IHandler $invocationHandler
	 */
	public function __construct(
		array $methods,
		string $pattern,
		$callable,
		IRouteCollector $routeCollector,
		ResponseFactoryInterface $responseFactory,
		Controllers\IControllerResolver $controllerResolver,
		Routing\Handlers\IHandler $invocationHandler
	) {
		$this->methods = $methods;
		$this->pattern = $pattern;
		$this->callable = $callable;

		try {
			$this->identifier = Uuid::uuid4()->toString();

		} catch (Throwable $ex) {
			throw new Exceptions\RuntimeException('Could not create route identifier');
		}

		$this->routeCollector = $routeCollector;
		$this->invocationHandler = $invocationHandler;
		$this->controllerResolver = $controllerResolver;
		$this->responseFactory = $responseFactory;

		$this->middlewareDispatcher = new Middleware\MiddlewareDispatcher($this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setInvocationHandler(Routing\Handlers\IHandler $invocationHandler): void
	{
		$this->invocationHandler = $invocationHandler;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMethods(): array
	{
		return $this->methods;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPattern(): string
	{
		return $this->routeCollector->getPattern() . $this->pattern;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCallable()
	{
		return $this->callable;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIdentifier(): string
	{
		return $this->identifier;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setArgument(string $name, string $value, bool $includeInSavedArguments = true): void
	{
		if ($includeInSavedArguments) {
			$this->savedArguments[$name] = $value;
		}

		$this->arguments[$name] = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getArgument(string $name, ?string $default = null): ?string
	{
		if (array_key_exists($name, $this->arguments)) {
			return $this->arguments[$name];
		}

		return $default;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setArguments(array $arguments, bool $includeInSavedArguments = true): void
	{
		if ($includeInSavedArguments) {
			$this->savedArguments = $arguments;
		}

		$this->arguments = $arguments;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getArguments(): array
	{
		return $this->arguments;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMiddleware(MiddlewareInterface $middleware): void
	{
		$this->middlewareDispatcher->add($middleware);
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepare(array $arguments): void
	{
		$this->arguments = array_replace($this->savedArguments, $arguments) ?? [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function run(ServerRequestInterface $request): ResponseInterface
	{
		if (!$this->groupMiddlewareAppended) {
			$inner = $this->middlewareDispatcher;

			$this->middlewareDispatcher = new Middleware\MiddlewareDispatcher($inner);

			$this->routeCollector->appendMiddlewareToDispatcher($this->middlewareDispatcher);

			$this->groupMiddlewareAppended = true;
		}

		return $this->middlewareDispatcher->handle($request);
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$callable = $this->controllerResolver->resolve($this->callable);
		$strategy = $this->invocationHandler;

		if (
			is_array($callable)
			&& $callable[0] instanceof RequestHandlerInterface
			&& class_implements($strategy) !== false
			&& !in_array(Routing\Handlers\IRequestHandler::class, class_implements($strategy), true)
		) {
			$strategy = new Routing\Handlers\RequestHandler();
		}

		$response = $this->responseFactory->createResponse();

		return $strategy($callable, $request, $response, $this->arguments);
	}

}
