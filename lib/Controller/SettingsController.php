<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Controller;

use OCA\ShiftsNext\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

class SettingsController extends Controller {
	private LoggerInterface $logger;

	public function __construct(LoggerInterface $logger, IRequest $request) {
		parent::__construct(Application::APP_ID, $request);
		$this->logger = $logger;
	}

	public function index(): TemplateResponse {
		$data = $this->getSettings();
		return new TemplateResponse(Application::APP_ID, 'settings', $data, 'blank');
	}

	public function saveSettings(): DataResponse {
		return new DataResponse($this->getSettings());
	}

	#[NoAdminRequired]
	public function getSettings(): array {
		return [];
	}
}
