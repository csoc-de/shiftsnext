<?php

namespace OCA\ShiftsNext\Settings;

use OCA\ShiftsNext\AppInfo\Application;
use OCA\ShiftsNext\Service\CalendarService;
use OCA\ShiftsNext\Service\ConfigService;
use OCA\ShiftsNext\Service\ExchangeApprovalType;
use OCA\ShiftsNext\Service\GroupShiftAdminRelationService;
use OCA\ShiftsNext\Service\UserService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\IL10N;
use OCP\Settings\ISettings;

use function array_map;

class AdminSettings implements ISettings {
	public function __construct(
		private IL10N $l10n,
		private IConfig $config,
		private IInitialState $initialState,
		private CalendarService $calendarService,
		private ConfigService $configService,
		private UserService $userService,
		private GroupShiftAdminRelationService $groupShiftAdminRelationService,
	) {
	}

	public function getForm(): TemplateResponse {
		$this->initialState->provideInitialState(
			'users',
			$this->userService->getAllSerializable(),
		);
		$this->initialState->provideInitialState(
			'calendars',
			$this->calendarService->getCalendars(),
		);
		$commonCalendar = $this->calendarService->safeGetCommonCalendar();
		if ($commonCalendar !== null) {
			$this->initialState->provideInitialState(
				'common_calendar',
				$commonCalendar,
			);
		}
		$absenceCalendar = $this->calendarService->safeGetAbsenceCalendar();
		if ($absenceCalendar !== null) {
			$this->initialState->provideInitialState(
				'absence_calendar',
				$absenceCalendar,
			);
		}
		$this->initialState->provideInitialState(
			'sync_to_personal_calendar',
			$this->configService->getSyncToPersonalCalendar(),
		);
		$this->initialState->provideInitialState(
			'ignore_absence_for_by_week_shifts',
			$this->configService->getIgnoreAbsenceForByWeekShifts(),
		);
		$this->initialState->provideInitialState(
			'exchange_approval_types',
			array_map(
				fn ($type) => $type->value,
				ExchangeApprovalType::cases(),
			),
		);
		$this->initialState->provideInitialState(
			'exchange_approval_type',
			$this->configService->getExchangeApprovalType()->value,
		);
		$this->initialState->provideInitialState(
			'group_shift_admin_relations_by_group',
			$this->groupShiftAdminRelationService->getAllGroupedByGroup(),
		);
		return new TemplateResponse(Application::APP_ID, 'mainAdminSettings');
	}

	public function getSection(): string {
		return Application::APP_ID;
	}

	public function getPriority(): int {
		return 98;
	}
}
