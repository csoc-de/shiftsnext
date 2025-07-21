<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Controller;

use OCA\ShiftsNext\AppInfo\Application;
use OCA\ShiftsNext\Service\CalendarService;
use OCA\ShiftsNext\Service\ConfigService;
use OCA\ShiftsNext\Service\GroupService;
use OCA\ShiftsNext\Service\GroupShiftAdminRelationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IRequest;
use OCP\IURLGenerator;

class PageController extends Controller {
	public function __construct(IRequest $request) {
		parent::__construct(Application::APP_ID, $request);
	}

	#[NoCSRFRequired]
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'GET', url: '/')]
	public function index(IURLGenerator $urlGenerator): RedirectResponse {
		$route = Application::APP_ID . '.page.page';
		$parameters = ['page' => 'shifts'];
		$url = $urlGenerator->linkToRoute($route, $parameters);
		return new RedirectResponse($url);
	}

	#[NoCSRFRequired]
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'GET', url: '/{page}')]
	public function page(
		IInitialState $initialState,
		ConfigService $configService,
		GroupService $groupService,
		GroupShiftAdminRelationService $groupShiftAdminRelationService,
		CalendarService $calendarService,
	): TemplateResponse {
		$groups = $groupService->getAllSerializable();

		$adminGroupIds
			= $groupShiftAdminRelationService->getShiftAdminGroupIds();
		$shiftAdminGroups
			= $groupService->getAllSerializable($adminGroupIds);

		$defaultGroupIds = $configService->getDefaultGroupIds();
		$defaultGroups = $groupService->getAllSerializable(
			$defaultGroupIds,
		);

		$exchangeApprovalType
			= $configService->getExchangeApprovalType()->value;

		$initialState->provideInitialState(
			'groups',
			$groups,
		);
		$initialState->provideInitialState(
			'shift_admin_groups',
			$shiftAdminGroups,
		);
		$initialState->provideInitialState(
			'default_groups',
			$defaultGroups,
		);
		$initialState->provideInitialState(
			'exchange_approval_type',
			$exchangeApprovalType,
		);
		$commonCalendar = $calendarService->safeGetCommonCalendar();
		if ($commonCalendar !== null) {
			$initialState->provideInitialState(
				'common_calendar',
				$commonCalendar,
			);
		}
		$absenceCalendar = $calendarService->safeGetAbsenceCalendar();
		if ($absenceCalendar !== null) {
			$initialState->provideInitialState(
				'absence_calendar',
				$absenceCalendar,
			);
		}
		$initialState->provideInitialState(
			'group_shift_admin_relations_by_group',
			$groupShiftAdminRelationService->getAllGroupedByGroup(),
		);
		return new TemplateResponse(Application::APP_ID, 'mainApp');
	}
}
