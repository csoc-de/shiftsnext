<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Controller;

use OCA\ShiftsNext\Enum\AppConfigKey;
use OCA\ShiftsNext\Enum\UserConfigKey;
use OCA\ShiftsNext\Psalm\ConfigAlias;
use OCA\ShiftsNext\Service\ConfigService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Response;
use OCP\IRequest;

/**
 * @psalm-import-type AppConfig from ConfigAlias
 * @psalm-import-type UserConfig from ConfigAlias
 */
final class ConfigController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private ConfigService $configService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @param AppConfig $values
	 */
	#[FrontpageRoute(verb: 'PUT', url: '/api/config/app')]
	public function app(array $values): Response {
		foreach ($values as $key => $value) {
			$key = AppConfigKey::from($key);
			$this->configService->setConfigValue($key, $value);
		}
		return new Response(Http::STATUS_NO_CONTENT);
	}

	/**
	 * @param UserConfig $values
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'PUT', url: '/api/config/user')]
	public function user(array $values): Response {
		foreach ($values as $key => $value) {
			$key = UserConfigKey::from($key);
			$this->configService->setConfigValue($key, $value);
		}
		return new Response(Http::STATUS_NO_CONTENT);
	}

	/**
	 * @param string[] $group_ids
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'PUT', url: '/api/config/user/default-groups')]
	public function defaultGroups(array $group_ids): Response {
		$this->configService->setDefaultGroupIds($group_ids);
		return new Response(Http::STATUS_NO_CONTENT);
	}
}
