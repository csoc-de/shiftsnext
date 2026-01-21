<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Controller;

use OCA\ShiftsNext\Db\CalendarChangeMapper;
use OCA\ShiftsNext\Response\ErrorResponse;
use OCA\ShiftsNext\Service\CalendarService;
use OCA\ShiftsNext\Service\GroupShiftAdminRelationService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use Throwable;

use function array_intersect;

final class CalendarController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private GroupShiftAdminRelationService $groupShiftAdminRelationService,
		private CalendarService $calendarService,
		private CalendarChangeMapper $calendarChangeMapper,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @param list<string> $group_ids
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'POST', url: '/api/calendars/synchronize-by-groups')]
	public function synchronizeByGroups(array $group_ids): JSONResponse {
		try {
			$adminGroupIds
				= $this->groupShiftAdminRelationService->getShiftAdminGroupIds();

			$groupIds = array_intersect($group_ids, $adminGroupIds);

			$changes = $this->calendarChangeMapper->findAll($groupIds);

			$errors = $this->calendarService->applyChanges($changes);

			return new JSONResponse($errors);
		} catch (Throwable $th) {
			return new ErrorResponse($th);
		}
	}

	/**
	 * @param list<int> $shift_ids
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'POST', url: '/api/calendars/synchronize-by-shifts')]
	public function synchronizeByShifts(array $shift_ids): JSONResponse {
		try {
			$changes = $this->calendarChangeMapper->findAll(shiftIds: $shift_ids);

			$errors = $this->calendarService->applyChanges($changes);

			return new JSONResponse($errors);
		} catch (Throwable $th) {
			return new ErrorResponse($th);
		}
	}
}
