<?php declare(strict_types = 1);

/**
 * HttpException.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Exceptions
 * @since          0.1.0
 *
 * @date           15.03.20
 */

namespace IPub\SlimRouter\Exceptions;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class HttpException extends Exception
{

	/** @var ServerRequestInterface */
	protected ServerRequestInterface $request;

	/** @var string */
	protected string $title = '';

	/** @var string */
	protected string $description = '';

	/**
	 * @param ServerRequestInterface $request
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 */
	public function __construct(
		ServerRequestInterface $request,
		string $message = '',
		int $code = 0,
		?Throwable $previous = null
	) {
		parent::__construct($message, $code, $previous);
		$this->request = $request;
	}

	/**
	 * @param string $title
	 *
	 * @return void
	 */
	public function setTitle(string $title): void
	{
		$this->title = $title;
	}

	/**
	 * @return ServerRequestInterface
	 */
	public function getRequest(): ServerRequestInterface
	{
		return $this->request;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @param string $description
	 *
	 * @return void
	 */
	public function setDescription(string $description): void
	{
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

}
