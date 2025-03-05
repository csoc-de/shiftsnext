<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Controller;

use OCA\ShiftsNext\Db\ShiftMapper;
use OCA\ShiftsNext\Exception\HttpException;
use OCA\ShiftsNext\Exception\ShiftNotFoundException;
use OCA\ShiftsNext\Exception\ShiftTypeNotFoundException;
use OCA\ShiftsNext\Service\CalendarChangeService;
use OCA\ShiftsNext\Service\CalendarService;
use OCA\ShiftsNext\Service\GroupShiftAdminRelationService;
use OCA\ShiftsNext\Service\GroupUserRelationService;
use OCA\ShiftsNext\Service\ShiftService;
use OCA\ShiftsNext\Service\ShiftTypeService;
use OCA\ShiftsNext\Service\UserService;
use OCA\ShiftsNext\Util\Util;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Throwable;

class ShiftController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private ShiftMapper $shiftMapper,
		private ShiftService $shiftService,
		private ShiftTypeService $shiftTypeService,
		private GroupUserRelationService $groupUserService,
		private GroupShiftAdminRelationService $groupShiftAdminRelationService,
		private CalendarChangeService $calendarChangeService,
		private UserService $userService,
		private CalendarService $calendarService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @param null|string[] $group_ids
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'GET', url: '/api/shifts')]
	public function index(
		?array $group_ids = null,
		?string $user_id = null,
		?string $calendar_date = null,
		?string $week_date = null,
	): DataResponse {
		try {
			if ($calendar_date !== null && $week_date !== null) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					'Filters `calendar_date` and `week_date` are mutually exclusive',
				);
			}
			try {
				$shiftsExtended = $this->shiftService->getAllExtended(
					$group_ids,
					$user_id,
					$calendar_date,
					$week_date,
				);
			} catch (ShiftTypeNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `shift_type_id` could not be resolved',
					$e,
				);
			}
			return new DataResponse($shiftsExtended);
		} catch (Throwable $th) {
			$responseCode = $th instanceof HttpException ? $th->getStatusCode() : Http::STATUS_INTERNAL_SERVER_ERROR;
			return new DataResponse(['error' => $th->getMessage()], $responseCode);
		}
	}

	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'GET', url: '/api/shifts/{id}')]
	public function show(int $id): DataResponse {
		try {
			try {
				$shiftExtended = $this->shiftService->getExtended($id);
			} catch (ShiftNotFoundException $e) {
				throw new HttpException(Http::STATUS_NOT_FOUND, null, $e);
			} catch (ShiftTypeNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `shift_type_id` could not be resolved',
					$e,
				);
			}
			return new DataResponse($shiftExtended);
		} catch (Throwable $th) {
			$responseCode = $th instanceof HttpException ? $th->getStatusCode() : Http::STATUS_INTERNAL_SERVER_ERROR;
			return new DataResponse(['error' => $th->getMessage()], $responseCode);
		}
	}

	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'POST', url: '/api/shifts')]
	public function create(
		string $user_id,
		int $shift_type_id,
		string $start,
		string $end,
	): DataResponse {
		try {
			try {
				$shiftType = $this->shiftTypeService->getRestricted($shift_type_id);
			} catch (ShiftTypeNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					"The shift_type_id `$shift_type_id` does not exist",
					$e,
				);
			}
			$groupId = $shiftType->getGroupId();
			if (!$this->groupShiftAdminRelationService->isShiftAdmin($groupId)) {
				throw new HttpException(
					Http::STATUS_FORBIDDEN,
					"You are not a group shift admin of group `\"$groupId\"` of shift_type_id `$shift_type_id`",
				);
			}
			if (!$this->groupUserService->isMember($groupId, $user_id)) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					"The user_id `\"$user_id\"` is not a member of group `\"$groupId\"` of shift_type_id `$shift_type_id`",
				);
			}
			if (!$shiftType->getActive()) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					"Cannot create shift for inactive shift_type_id `$shift_type_id`",
				);
			}
			$start = Util::unlocalizeEcma($start, $startDateTime);
			$end = Util::unlocalizeEcma($end, $endDateTime);
			if ($this->calendarService->isUserAbsent(
				$user_id,
				$startDateTime,
				$endDateTime,
			)) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					"Cannot create shift for absent user_id `\"$user_id\"`",
				);
			}
			$shift = $this->shiftMapper->create(
				$user_id,
				$shift_type_id,
				$start,
				$end,
			);
			$shiftExtended = $this->shiftService->getExtended($shift);
			$this->calendarChangeService->safeCreate($shiftExtended);
			return new DataResponse($shiftExtended);
		} catch (Throwable $th) {
			$responseCode = $th instanceof HttpException ? $th->getStatusCode() : Http::STATUS_INTERNAL_SERVER_ERROR;
			return new DataResponse(['error' => $th->getMessage()], $responseCode);
		}
	}

	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'PATCH', url: '/api/shifts/{id}')]
	public function update(
		int $id,
		string $user_id,
	): DataResponse {
		try {
			try {
				$shift = $this->shiftMapper->findById($id);
			} catch (ShiftNotFoundException $e) {
				throw new HttpException(Http::STATUS_NOT_FOUND, null, $e);
			}
			$shiftTypeId = $shift->getShiftTypeId();
			try {
				$shiftType = $this->shiftTypeService->getRestricted($shiftTypeId);
			} catch (ShiftTypeNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `shift_type_id` could not be resolved',
					$e,
				);
			}
			$groupId = $shiftType->getGroupId();
			if (!$this->groupShiftAdminRelationService->isShiftAdmin($groupId)) {
				throw new HttpException(
					Http::STATUS_FORBIDDEN,
					"You are not a group shift admin of group `\"$groupId\"` of shift_type_id `$shiftTypeId`",
				);
			}
			if (!$this->groupUserService->isMember($groupId, $user_id)) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					"The user_id `\"$user_id\"` is not a member of group `\"$groupId\"` of shift_type_id `$shiftTypeId`",
				);
			}
			if ($this->calendarService->isUserAbsent(
				$user_id,
				Util::parseEcma($shift->getStart()),
				Util::parseEcma($shift->getEnd()),
			)) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					"Cannot move shift to absent user_id `\"$user_id\"`",
				);
			}
			// This queues a removal of the shift from the previous user's calendar
			$this->calendarChangeService->safeCreate($shift);
			$shift = $this->shiftMapper->updateById($shift, $user_id);
			$shiftExtended = $this->shiftService->getExtended($shift);
			// This queues a creation of the shift in $user_id's calendar
			$this->calendarChangeService->safeCreate($shiftExtended);
			return new DataResponse($shiftExtended);
		} catch (Throwable $th) {
			$responseCode = $th instanceof HttpException ? $th->getStatusCode() : Http::STATUS_INTERNAL_SERVER_ERROR;
			return new DataResponse(['error' => $th->getMessage()], $responseCode);
		}
	}

	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'DELETE', url: '/api/shifts/{id}')]
	public function destroy(int $id): DataResponse {
		try {
			try {
				$shift = $this->shiftMapper->findById($id);
			} catch (ShiftNotFoundException $e) {
				throw new HttpException(Http::STATUS_NOT_FOUND, null, $e);
			}
			$shiftTypeId = $shift->getShiftTypeId();
			try {
				$this->shiftTypeService->getRestricted($shiftTypeId);
			} catch (ShiftTypeNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `shift_type_id` could not be resolved',
					$e,
				);
			}
			$shift = $this->shiftMapper->deleteById($shift);
			$shiftExtended = $this->shiftService->getExtended($shift);
			$this->calendarChangeService->safeCreate($shiftExtended);
			return new DataResponse($shiftExtended);
		} catch (Throwable $th) {
			$responseCode = $th instanceof HttpException ? $th->getStatusCode() : Http::STATUS_INTERNAL_SERVER_ERROR;
			return new DataResponse(['error' => $th->getMessage()], $responseCode);
		}
	}
}
