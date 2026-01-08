<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Response;

use OCA\ShiftsNext\Exception\HttpException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use Throwable;

/**
 * @psalm-suppress MissingTemplateParam
 */
final class ErrorResponse extends DataResponse {
	public function __construct(Throwable $th) {
		if ($th instanceof HttpException) {
			$responseCode = $th->getStatusCode();
			$localizedMessage = $th->getLocalizedMessage();
		} else {
			$responseCode = Http::STATUS_INTERNAL_SERVER_ERROR;
			$localizedMessage = '';
		}
		parent::__construct([
			'error' => $th->getMessage(),
			'localizedError' => $localizedMessage,
		], $responseCode);
	}
}
