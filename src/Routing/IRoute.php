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

use IPub\SlimRouter\Routing;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

interface IRoute
{

	/**
	 * @param Routing\Handlers\IHandler $invocationStrategy
	 *
	 * @return void
	 */
	public function setInvocationHandler(Routing\Handlers\IHandler $invocationStrategy): void;

	/**
	 * @return string[]
	 */
	public function getMethods(): array;

	/**
	 * @return string
	 */
	public function getPattern(): string;

	/**
	 * @return callable|string|mixed[]
	 */
	public function getCallable();

	/**
	 * @param string $name
	 *
	 * @return void
	 */
	public function setName(string $name): void;

	/**
	 * @return string|null
	 */
	public function getName(): ?string;

	/**
	 * @return string
	 */
	public function getIdentifier(): string;

	/**
	 * @param string $name
	 * @param string $value
	 *
	 * @return void
	 */
	public function setArgument(string $name, string $value): void;

	/**
	 * @param string $name
	 * @param string|null $default
	 *
	 * @return string|null
	 */
	public function getArgument(string $name, ?string $default = null): ?string;

	/**
	 * @param string[] $arguments
	 *
	 * @return void
	 */
	public function setArguments(array $arguments): void;

	/**
	 * @return string[]
	 */
	public function getArguments(): array;

	/**
	 * @param MiddlewareInterface $middleware
	 *
	 * @return void
	 */
	public function addMiddleware(MiddlewareInterface $middleware): void;

	/**
	 * @param mixed[] $arguments
	 *
	 * @return void
	 */
	public function prepare(array $arguments): void;

	/**
	 * Run route
	 *
	 * This method traverses the middleware stack, including the route's callable
	 * and captures the resultant HTTP response object. It then sends the response
	 * back to the Application.
	 *
	 * @param ServerRequestInterface $request
	 *
	 * @return ResponseInterface
	 */
	public function run(ServerRequestInterface $request): ResponseInterface;

}
