<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Controller;

use OCA\ShiftsNext\Db\CalendarChangeMapper;
use OCA\ShiftsNext\Response\ErrorResponse;
use OCA\ShiftsNext\Service\CalendarService;
use OCA\ShiftsNext\Service\GroupShiftAdminRelationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Throwable;

use function array_intersect;

final class CalendarController extends Controller {
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
	 * @param string[] $group_ids
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'POST', url: '/api/calendars/synchronize-by-groups')]
	public function synchronizeByGroups(array $group_ids): DataResponse {
		try {
			$adminGroupIds
				= $this->groupShiftAdminRelationService->getShiftAdminGroupIds();

			$groupIds = array_intersect($group_ids, $adminGroupIds);

			$changes = $this->calendarChangeMapper->findAll($groupIds);

			$errors = $this->calendarService->applyChanges($changes);

			return new DataResponse($errors);
		} catch (Throwable $th) {
			return new ErrorResponse($th);
		}
	}

	/**
	 * @param int[] $shift_ids
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'POST', url: '/api/calendars/synchronize-by-shifts')]
	public function synchronizeByShifts(array $shift_ids): DataResponse {
		try {
			$changes = $this->calendarChangeMapper->findAll(shiftIds: $shift_ids);

			$errors = $this->calendarService->applyChanges($changes);

			return new DataResponse($errors);
		} catch (Throwable $th) {
			return new ErrorResponse($th);
		}
	}
}
