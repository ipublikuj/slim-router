<?php declare(strict_types = 1);

/**
 * Stream.php
 *
 * @license        More in license.md
 * @copyright      https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Http
 * @since          0.1.0
 *
 * @date           14.03.20
 */

namespace IPub\SlimRouter\Http;

use IPub\SlimRouter\Exceptions;
use Psr\Http\Message\StreamInterface;

/**
 * Basic http response resource
 *
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Http
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Stream implements StreamInterface
{

	/** @var resource|null */
	private $resource = null;

	/** @var mixed[]|null */
	private $metaData = null;

	/** @var bool|null */
	private $readable = null;

	/** @var bool|null */
	private $writable = null;

	/** @var bool|null */
	private $seekable = null;

	/**
	 * @param resource $resource One of the stream type resources
	 *
	 * @see https://www.php.net/manual/en/resource.php
	 */
	public function __construct($resource)
	{
		if (get_resource_type($resource) !== 'stream') {
			throw new Exceptions\InvalidArgumentException('Invalid stream resource');
		}

		$this->resource = $resource;
	}

	/**
	 * @param string $streamUri
	 * @param string $mode
	 *
	 * @return Stream
	 */
	public static function fromResourceUri(string $streamUri, string $mode = 'r'): Stream
	{
		$resource = fopen($streamUri, $mode);

		if ($resource === false) {
			throw preg_match('/^[acrwx](?:\+?[tb]?|[tb]?\+?)$/', $mode) === false
				? new Exceptions\InvalidArgumentException('Invalid stream resource mode')
				: new Exceptions\RuntimeException('Invalid stream reference');
		}

		return new self($resource);
	}

	/**
	 * @param string $body
	 *
	 * @return Stream
	 */
	public static function fromBodyString(string $body): Stream
	{
		$resource = fopen('php://temp', 'w+b');

		if ($resource === false) {
			throw new Exceptions\RuntimeException('Resource could not be created');
		}

		$stream = new self($resource);
		$stream->write($body);
		$stream->rewind();

		return $stream;
	}

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string
	{
		try {
			$this->rewind();

			return $this->getContents();

		} catch (Exceptions\RuntimeException $e) {
			return '';
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function close(): void
	{
		$resource = $this->detach();

		if ($resource !== null) {
			fclose($resource);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function detach()
	{
		if ($this->resource === null) {
			return null;
		}

		$resource = $this->resource;

		$this->resource = null;
		$this->metaData = null;
		$this->readable = false;
		$this->seekable = false;
		$this->writable = false;

		return $resource;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSize(): ?int
	{
		if ($this->resource === null) {
			return null;
		}

		$fileInfo = fstat($this->resource);

		return $fileInfo !== false ? $fileInfo['size'] : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function tell(): int
	{
		if ($this->resource === null) {
			throw new Exceptions\RuntimeException('Pointer position not available in detached resource');
		}

		$position = ftell($this->resource);

		if ($position === false) {
			throw new Exceptions\StreamResourceCallException('Failed to tell pointer position');
		}

		return $position;
	}

	/**
	 * {@inheritDoc}
	 */
	public function eof(): bool
	{
		return $this->resource !== null ? feof($this->resource) : true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isSeekable(): bool
	{
		if ($this->seekable !== null) {
			return $this->seekable;
		}

		return $this->seekable = (bool) $this->getMetadata('seekable');
	}

	/**
	 * {@inheritDoc}
	 */
	public function seek($offset, $whence = SEEK_SET): void
	{
		if ($this->resource === null) {
			throw new Exceptions\RuntimeException('No resource available; cannot read');
		}

		if (!$this->isSeekable()) {
			throw new Exceptions\RuntimeException('Stream is not seekable or detached');
		}

		$exitCode = fseek($this->resource, $offset, $whence);

		if ($exitCode === -1) {
			throw new Exceptions\StreamResourceCallException('Failed to seek the stream');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function rewind(): void
	{
		$this->seek(0);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isWritable()
	{
		if ($this->writable !== null) {
			return $this->writable;
		}

		$mode = $this->getMetadata('mode');
		$writable = ['w' => true, 'a' => true, 'x' => true, 'c' => true];

		return $this->writable = (isset($writable[$mode[0]]) || strstr($mode, '+') !== false);
	}

	/**
	 * {@inheritDoc}
	 */
	public function write($string)
	{
		if ($this->resource === null) {
			throw new Exceptions\RuntimeException('No resource available; cannot write');
		}

		if (!$this->isWritable()) {
			throw new Exceptions\RuntimeException('Stream is not writable');
		}

		$bytesWritten = fwrite($this->resource, $string);

		if ($bytesWritten === false) {
			throw new Exceptions\StreamResourceCallException('Failed writing to stream');
		}

		return $bytesWritten;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isReadable()
	{
		if ($this->readable !== null) {
			return $this->readable;
		}

		$mode = $this->getMetadata('mode');

		return $this->readable = ($mode[0] === 'r' || strstr($mode, '+') !== false);
	}

	/**
	 * {@inheritDoc}
	 */
	public function read($length)
	{
		if ($this->resource === null) {
			throw new Exceptions\RuntimeException('No resource available; cannot read');
		}

		if (!$this->isReadable()) {
			throw new Exceptions\RuntimeException('Stream is not readable');
		}

		$streamData = fread($this->resource, $length);

		if ($streamData === false) {
			throw new Exceptions\StreamResourceCallException('Failed reading from stream');
		}

		return $streamData;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getContents()
	{
		if ($this->resource === null) {
			throw new Exceptions\RuntimeException('No resource available; cannot read');
		}

		if (!$this->isReadable()) {
			throw new Exceptions\RuntimeException('Stream is not readable or detached');
		}

		$streamContents = stream_get_contents($this->resource);

		if ($streamContents === false) {
			throw new Exceptions\StreamResourceCallException('Failed to retrieve stream contents');
		}

		return $streamContents;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMetadata($key = null)
	{
		if ($this->resource === null) {
			return $key !== null ? null : [];
		}

		$this->metaData = $this->metaData ?? stream_get_meta_data($this->resource);

		return $key !== null ? $this->metaData[$key] ?? null : $this->metaData;
	}

}
