<?php declare(strict_types = 1);

/**
 * Response.php
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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Basic http response
 *
 * @package        iPublikuj:SlimRouter!
 * @subpackage     Http
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Response implements ResponseInterface
{

	/** @var int */
	private $status;

	/** @var string */
	private $reason;

	/** @var StreamInterface */
	private $body;

	/** @var string */
	private $version;

	/** @var string[][] */
	private $headers;

	/** @var string[] */
	private $supportedProtocolVersions = ['1.0', '1.1', '2'];

	/** @var string[] */
	private $headerNames = [];

	/** @var mixed[] */
	private $statusCodes = [
		// INFORMATIONAL CODES
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		// SUCCESS CODES
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-status',
		208 => 'Already Reported',
		// REDIRECTION CODES
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Switch Proxy', // Deprecated
		307 => 'Temporary Redirect',
		// CLIENT ERROR
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Time-out',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Large',
		415 => 'Unsupported Media Type',
		416 => 'Requested range not satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		425 => 'Unordered Collection',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		451 => 'Unavailable For Legal Reasons',
		// SERVER ERROR
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Time-out',
		505 => 'HTTP Version not supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		511 => 'Network Authentication Required',
	];

	/**
	 * @param int $statusCode             Normally one of the status codes defined by RFC 7231 section 6
	 * @param StreamInterface $body
	 * @param mixed[] $headers            Associative array of header strings or arrays of header strings
	 * @param mixed[] $params             Associative array with following keys and its default values
	 *                                    when key is not present or its value is null:
	 *                                    - version - http protocol version (default: '1.1')
	 *                                    - reason - reason phrase normally associated with $statusCode, so by
	 *                                    default it will be resolved from it.
	 *
	 * @see https://tools.ietf.org/html/rfc7231#section-6
	 */
	public function __construct(
		int $statusCode,
		StreamInterface $body,
		array $headers = [],
		array $params = []
	) {
		$this->status = $this->validStatusCode($statusCode);
		$this->body = $body;
		$this->reason = $this->validReasonPhrase($params['reason'] ?? '');
		$this->version = isset($params['version']) ? $this->validProtocolVersion((string) $params['version']) : '1.1';

		$this->loadHeaders($headers);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getStatusCode(): int
	{
		return $this->status;
	}

	/**
	 * {@inheritDoc}
	 */
	public function withStatus($code, $reasonPhrase = ''): ResponseInterface
	{
		$clone = clone $this;
		$clone->status = $this->validStatusCode($code);
		$clone->reason = $clone->validReasonPhrase($reasonPhrase);

		return $clone;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getReasonPhrase(): string
	{
		return $this->reason;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getProtocolVersion(): string
	{
		return $this->version;
	}

	/**
	 * {@inheritDoc}
	 */
	public function withProtocolVersion($version): ResponseInterface
	{
		$clone = clone $this;
		$clone->version = $this->validProtocolVersion($version);

		return $clone;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHeaders(): array
	{
		return $this->headers ?? [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasHeader($name): bool
	{
		return isset($this->headerNames[strtolower($name)]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHeader($name): array
	{
		if (!$this->hasHeader($name)) {
			return [];
		}

		$index = strtolower($name);
		$name = $this->headerNames[$index];

		return $this->headers[$name];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHeaderLine($name): string
	{
		$header = $this->getHeader($name);

		return $header === [] ? '' : implode(', ', $header);
	}

	/**
	 * {@inheritDoc}
	 */
	public function withHeader($name, $value): ResponseInterface
	{
		$clone = clone $this;
		$clone->removeHeader($name);
		$clone->setHeader($name, $value);

		return $clone;
	}

	/**
	 * {@inheritDoc}
	 */
	public function withAddedHeader($name, $value): ResponseInterface
	{
		if (!$this->hasHeader($name)) {
			$clone = clone $this;
			$clone->removeHeader($name);
			$clone->setHeader($name, $value);

			return $clone;
		}

		$index = strtolower($name);

		$name = $this->headerNames[$index];

		$value = $this->validHeaderValues($value);

		$clone = clone $this;
		$clone->headers[$name] = array_merge($clone->headers[$name], $value);

		return $clone;
	}

	/**
	 * {@inheritDoc}
	 */
	public function withoutHeader($name): ResponseInterface
	{
		$clone = clone $this;
		$clone->removeHeader($name);

		return $clone;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBody(): StreamInterface
	{
		return $this->body;
	}

	/**
	 * {@inheritDoc}
	 */
	public function withBody(StreamInterface $body): ResponseInterface
	{
		$clone = clone $this;
		$clone->body = $body;

		return $clone;
	}

	/**
	 * @param string $text
	 * @param int $statusCode
	 *
	 * @return ResponseInterface
	 */
	public static function text(string $text, int $statusCode = 200): ResponseInterface
	{
		return new self($statusCode, Stream::fromBodyString($text), ['Content-Type' => 'text/plain']);
	}

	/**
	 * @param string $html
	 * @param int $statusCode
	 *
	 * @return ResponseInterface
	 */
	public static function html(string $html, int $statusCode = 200): ResponseInterface
	{
		return new self($statusCode, Stream::fromBodyString($html), ['Content-Type' => 'text/html']);
	}

	/**
	 * @param string $xml
	 * @param int $statusCode
	 *
	 * @return ResponseInterface
	 */
	public static function xml(string $xml, int $statusCode = 200): ResponseInterface
	{
		return new self($statusCode, Stream::fromBodyString($xml), ['Content-Type' => 'application/xml']);
	}

	/**
	 * There's a XOR operator between $defaultEncode and $encodeOptions,
	 * which means that if option is set in both provided and default it
	 * will be switched off.
	 *
	 * @param mixed[] $data
	 * @param int $statusCode
	 * @param int $encodeOptions
	 *
	 * @return ResponseInterface
	 */
	public static function json(array $data, int $statusCode = 200, int $encodeOptions = 0): ResponseInterface
	{
		$defaultEncode = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT;
		$serialized = json_encode($data, $defaultEncode ^ $encodeOptions);

		if ($serialized === false) {
			throw new Exceptions\RuntimeException('Resource body could not be created');
		}

		return new self($statusCode, Stream::fromBodyString($serialized), ['Content-Type' => 'application/json']);
	}

	/**
	 * @param string $uri
	 * @param int $status
	 *
	 * @return ResponseInterface
	 */
	public static function redirect(string $uri, int $status = 303): ResponseInterface
	{
		if ($status < 300 || $status > 399) {
			throw new Exceptions\InvalidArgumentException('Invalid status code for redirect response');
		}

		$resource = fopen('php://temp', 'r');

		if ($resource === false) {
			throw new Exceptions\RuntimeException('Resource could not be created');
		}

		return new self($status, new Stream($resource), ['Location' => $uri]);
	}

	/**
	 * @param StreamInterface|null $body
	 *
	 * @return ResponseInterface
	 */
	public static function notFound(?StreamInterface $body = null): ResponseInterface
	{
		return new self(404, $body ?? Stream::fromResourceUri('php://temp'));
	}

	/**
	 * @param string[][] $headers
	 *
	 * @return void
	 */
	private function loadHeaders(array $headers): void
	{
		foreach ($headers as $name => $value) {
			$this->setHeader($name, $value);
		}
	}

	/**
	 * @param string $name
	 * @param string|string[] $value
	 *
	 * @return void
	 */
	private function setHeader(string $name, $value): void
	{
		if (preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $name) !== 1) {
			throw new Exceptions\InvalidArgumentException('Invalid header name argument type - expected valid string token');
		}

		$index = strtolower($name);

		$this->headers[$name] = $this->validHeaderValues($value);
		$this->headerNames[$index] = $name;
	}

	/**
	 * @param string $name
	 *
	 * @return void
	 *
	 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements.UnusedMethod
	 */
	private function removeHeader(string $name): void
	{
		$headerIndex = strtolower($name);

		if (!isset($this->headerNames[$headerIndex])) {
			return;
		}

		unset($this->headers[$this->headerNames[$headerIndex]], $this->headerNames[$headerIndex]);
	}

	/**
	 * @param string|string[]|mixed $headerValues
	 *
	 * @return string[]
	 */
	private function validHeaderValues($headerValues): array
	{
		if (is_string($headerValues)) {
			$headerValues = [$headerValues];
		}

		if (!is_array($headerValues) || !$this->legalHeaderStrings($headerValues)) {
			throw new Exceptions\InvalidArgumentException('Invalid HTTP header value argument - expected legal strings[] or string');
		}

		return array_values($headerValues);
	}

	/**
	 * @param string $version
	 *
	 * @return string
	 */
	private function validProtocolVersion(string $version): string
	{
		if (!in_array($version, $this->supportedProtocolVersions, true)) {
			throw new Exceptions\InvalidArgumentException(sprintf('Unsupported HTTP protocol version - expected <%s> string', implode('|', $this->supportedProtocolVersions)));
		}

		return $version;
	}

	/**
	 * @param int $code
	 *
	 * @return int
	 */
	private function validStatusCode(int $code): int
	{
		if ($code < 100 || $code >= 600) {
			throw new Exceptions\InvalidArgumentException('Invalid status code argument - integer <100-599> expected');
		}

		return $code;
	}

	/**
	 * @param string $reason
	 *
	 * @return string
	 */
	private function validReasonPhrase(string $reason): string
	{
		if ($reason === '' && isset($this->statusCodes[$this->status])) {
			$reason = $this->statusCodes[$this->status];
		}

		return $reason;
	}

	/**
	 * @param mixed[] $headerValues
	 *
	 * @return bool
	 */
	private function legalHeaderStrings(array $headerValues): bool
	{
		foreach ($headerValues as $value) {
			if (!is_string($value) || $this->illegalHeaderChars($value)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param string $header
	 *
	 * @return bool
	 */
	private function illegalHeaderChars(string $header): bool
	{
		$illegalCharset = preg_match("/[^\t\r\n\x20-\x7E\x80-\xFE]/", $header);
		$invalidLineBreak = preg_match("/(?:[^\r]\n|\r[^\n]|\n[^ \t])/", $header);

		return $illegalCharset === false || $invalidLineBreak === false;
	}

}
