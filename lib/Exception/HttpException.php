<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Exception;

use Exception;
use OCP\AppFramework\Http;
use Throwable;

/**
 * @template S of Http::STATUS_*
 */
final class HttpException extends Exception implements LocalizedThrowable {
	/**
	 * An array mapping HTTP status codes to their reason phrase
	 *
	 * @var array<int, string>
	 */
	public const array STATUSES = [
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		208 => 'Already Reported',
		226 => 'IM Used',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Reserved',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		425 => 'Too Early',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		451 => 'Unavailable For Legal Reasons',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		509 => 'Bandwidth Limit Exceeded',
		510 => 'Not Extended',
		511 => 'Network Authentication Required',
	];

	/**
	 * @param S $statusCode Defaults to 500
	 * @param null|string $message The message that is used in the JSON response
	 *                             body. If `null`, defaults to the HTTP status
	 *                             reason phrase of `$statusCode`.
	 * @param string $localizedMessage The localized message that is used in the JSON response body
	 *
	 * @return void
	 */
	public function __construct(
		int $statusCode = 500,
		?string $message = null,
		?Throwable $previous = null,
		protected string $localizedMessage = '',
	) {
		parent::__construct($message ?? self::STATUSES[$statusCode] ?? '', $statusCode, $previous);
	}

	/**
	 * Get the HTTP status code
	 *
	 * @return S
	 */
	public function getStatusCode(): int {
		/** @var S */
		return $this->code;
	}

	#[\Override]
	public function getLocalizedMessage(): string {
		return $this->localizedMessage;
	}
}
