<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\AppInfo;

use OCA\ShiftsNext\Listener\GroupDeletedListener;
use OCA\ShiftsNext\Listener\UserChangedListener;
use OCA\ShiftsNext\Listener\UserDeletedListener;
use OCA\ShiftsNext\Middleware\VersionMatchMiddleware;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Group\Events\GroupDeletedEvent;
use OCP\User\Events\UserChangedEvent;
use OCP\User\Events\UserDeletedEvent;

final class Application extends App implements IBootstrap {
	public const string APP_ID = 'shiftsnext';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);

		/**
		 * @psalm-suppress DeprecatedInterface
		 * @var IEventDispatcher
		 */
		$dispatcher = $this->getContainer()->get(IEventDispatcher::class);

		$dispatcher->addServiceListener(GroupDeletedEvent::class, GroupDeletedListener::class);
		$dispatcher->addServiceListener(UserChangedEvent::class, UserChangedListener::class);
		$dispatcher->addServiceListener(UserDeletedEvent::class, UserDeletedListener::class);
	}

	#[\Override]
	public function register(IRegistrationContext $context): void {
		include_once __DIR__ . '/../../vendor/autoload.php';
		$context->registerMiddleware(VersionMatchMiddleware::class);
	}

	#[\Override]
	public function boot(IBootContext $context): void {
	}
}
