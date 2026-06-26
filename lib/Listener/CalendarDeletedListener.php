<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Listener;

use OCA\DAV\Events\CalendarDeletedEvent;
use OCA\ShiftsNext\Db\ShiftTypeMapper;
use OCA\ShiftsNext\Service\ConfigService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use Override;

/**
 * @psalm-suppress InvalidTemplateParam
 * @implements IEventListener<CalendarDeletedEvent>
 */
final class CalendarDeletedListener implements IEventListener {
	public function __construct(
		private ShiftTypeMapper $shiftTypeMapper,
		private ConfigService $configService,
	) {
	}

	/**
	 * @psalm-suppress ImplementedParamTypeMismatch
	 */
	#[Override]
	public function handle(Event $event): void {
		if (!($event instanceof CalendarDeletedEvent)) {
			return;
		}
		/** @var int */
		$calendarId = $event->getCalendarId();
		match ($calendarId) {
			$this->configService->getCommonCalendarId() => $this->configService->deleteCommonCalendarId(),
			$this->configService->getAbsenceCalendarId() => $this->configService->deleteAbsenceCalendarId(),
			default => null,
		};
		$shiftTypes = $this->shiftTypeMapper->findAll(calendarIds: [$calendarId]);
		foreach ($shiftTypes as $shiftType) {
			$this->shiftTypeMapper->updateById($shiftType, null);
		}
	}
}
