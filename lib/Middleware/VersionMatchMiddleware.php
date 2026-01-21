<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Middleware;

use Exception;
use OCA\ShiftsNext\AppInfo\Application;
use OCA\ShiftsNext\Exception\HttpException;
use OCA\ShiftsNext\Exception\VersionMismatchException;
use OCA\ShiftsNext\Response\ErrorResponse;
use OCA\ShiftsNext\Service\ConfigService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Middleware;
use OCP\IL10N;
use OCP\IRequest;

final class VersionMatchMiddleware extends Middleware {
	public function __construct(
		private IL10N $l,
		private IRequest $request,
		private ConfigService $configService,
	) {
	}

	/**
	 * @throws VersionMismatchException if `$controller` is an ApiController and
	 *                                  the client's app version does not match
	 *                                  the installed app version
	 */
	#[\Override]
	public function beforeController(
		Controller $controller,
		string $methodName,
	): void {
		$clientVersion = $this->request->getHeader(
			'X-' . Application::APP_ID . '-appVersion'
		);
		$installedVersion = $this->configService->getInstalledVersion();
		if (
			$controller instanceof ApiController
			&& $clientVersion !== $installedVersion
		) {
			throw new VersionMismatchException();
		}
	}

	/**
	 * @throws Exception if `$exception` is not a
	 *                   {@see OCA\ShiftsNext\Exception\VersionMismatchException}
	 */
	#[\Override]
	public function afterException(
		Controller $controller,
		string $methodName,
		Exception $exception,
	): Response {
		if ($exception instanceof VersionMismatchException) {
			return new ErrorResponse(
				new HttpException(
					Http::STATUS_CONFLICT,
					$exception->getMessage(),
					$exception,
					$this->l->t('The app has been updated. Please reload the page.'),
				)
			);
		}
		throw $exception;
	}
}
