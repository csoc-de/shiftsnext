<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Controller;

use OCA\ShiftsNext\Db\CalendarChangeMapper;
use OCA\ShiftsNext\Db\ShiftMapper;
use OCA\ShiftsNext\Service\CalendarService;
use OCA\ShiftsNext\Service\GroupShiftAdminRelationService;
use OCA\ShiftsNext\Service\ShiftTypeService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
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
		private ShiftMapper $shiftMapper,
		private ShiftTypeService $shiftTypeService,
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
			return new DataResponse(
				['error' => $th->getMessage()],
				Http::STATUS_INTERNAL_SERVER_ERROR,
			);
		}
	}

	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'POST', url: '/api/calendars/synchronize-by-shift')]
	public function synchronizeByShift(int $shift_id): DataResponse {
		try {
			$shift = $this->shiftMapper->findById($shift_id);
			$shiftType = $this->shiftTypeService->getRestricted(
				$shift->getShiftTypeId(),
			);

			$this->calendarChangeMapper->create(
				$shiftType->getGroupId(),
				$shift->getUserId(),
				$shift->getId(),
			);

			$changes = $this->calendarChangeMapper->findAll(shiftId: $shift_id);

			$errors = $this->calendarService->applyChanges($changes);

			return new DataResponse($errors);
		} catch (Throwable $th) {
			return new DataResponse(
				['error' => $th->getMessage()],
				Http::STATUS_INTERNAL_SERVER_ERROR,
			);
		}
	}
}
