<?php declare(strict_types = 1);

/**
 * MiddlewareDispatcher.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Routing
 * @since          0.1.0
 *
 * @date           15.03.20
 */

namespace IPub\SlimRouter\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Router middleware dispatcher
 *
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Middleware
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class MiddlewareDispatcher implements IMiddlewareDispatcher
{

	/**
	 * Tip of the middleware call stack
	 *
	 * @var RequestHandlerInterface
	 */
	private RequestHandlerInterface $tip;

	/**
	 * @param RequestHandlerInterface $kernel
	 */
	public function __construct(
		RequestHandlerInterface $kernel
	) {
		$this->seedMiddlewareStack($kernel);
	}

	/**
	 * {@inheritDoc}
	 */
	public function seedMiddlewareStack(RequestHandlerInterface $kernel): void
	{
		$this->tip = $kernel;
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		return $this->tip->handle($request);
	}

	/**
	 * {@inheritDoc}
	 */
	public function add(MiddlewareInterface $middleware): void
	{
		$next = $this->tip;

		$this->tip = new class ($middleware, $next) implements RequestHandlerInterface {

			/** @var MiddlewareInterface */
			private $middleware;

			/** @var RequestHandlerInterface */
			private $next;

			public function __construct(MiddlewareInterface $middleware, RequestHandlerInterface $next)
			{
				$this->middleware = $middleware;
				$this->next = $next;
			}

			public function handle(ServerRequestInterface $request): ResponseInterface
			{
				return $this->middleware->process($request, $this->next);
			}

		};
	}

}
