<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Controller;

use IntlDateFormatter;
use OCA\ShiftsNext\Db\ShiftExchangeMapper;
use OCA\ShiftsNext\Db\ShiftMapper;
use OCA\ShiftsNext\Db\ShiftTypeMapper;
use OCA\ShiftsNext\Exception\HttpException;
use OCA\ShiftsNext\Exception\ShiftNotFoundException;
use OCA\ShiftsNext\Exception\ShiftTypeNotFoundException;
use OCA\ShiftsNext\Response\ErrorResponse;
use OCA\ShiftsNext\Service\CalendarChangeService;
use OCA\ShiftsNext\Service\CalendarService;
use OCA\ShiftsNext\Service\ConfigService;
use OCA\ShiftsNext\Service\GroupService;
use OCA\ShiftsNext\Service\GroupShiftAdminRelationService;
use OCA\ShiftsNext\Service\GroupUserRelationService;
use OCA\ShiftsNext\Service\ShiftService;
use OCA\ShiftsNext\Service\UserService;
use OCA\ShiftsNext\Util\Util;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IL10N;
use OCP\IRequest;
use Throwable;

final class ShiftController extends ApiController {
	public function __construct(
		private IL10N $l,
		string $appName,
		IRequest $request,
		private ShiftMapper $shiftMapper,
		private ShiftTypeMapper $shiftTypeMapper,
		private ShiftExchangeMapper $shiftExchangeMapper,
		private ShiftService $shiftService,
		private GroupUserRelationService $groupUserService,
		private GroupShiftAdminRelationService $groupShiftAdminRelationService,
		private CalendarChangeService $calendarChangeService,
		private GroupService $groupService,
		private UserService $userService,
		private CalendarService $calendarService,
		private ConfigService $configService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @param null|list<string> $group_ids
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
					'Filters calendar_date and week_date are mutually exclusive',
					null,
					$this->l->t('You cannot filter for a calendar date and week date at the same time.'),
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
			return new ErrorResponse($th);
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
			return new ErrorResponse($th);
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
				$shiftType = $this->shiftTypeMapper->findById($shift_type_id);
			} catch (ShiftTypeNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					"The shift type `$shift_type_id` does not exist",
					$e,
				);
			}
			$groupId = $shiftType->getGroupId();
			$name = $shiftType->getName();
			$groupName = $this->groupService->get($groupId)->getDisplayName();
			if (!$this->groupShiftAdminRelationService->isShiftAdmin($groupId)) {
				throw new HttpException(
					Http::STATUS_FORBIDDEN,
					"You are not a group shift admin of group `\"$groupId\"` of shift type `$shift_type_id`",
					null,
					$this->l->t('You do not have permissions to create shifts for group %1$s.', [$groupName]),
				);
			}
			$userName = $this->userService->get($user_id)->getDisplayName();
			if (!$this->groupUserService->isMember($groupId, $user_id)) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					"The user `\"$user_id\"` is not a member of group `\"$groupId\"` of shift type `$shift_type_id`",
					null,
					$this->l->t('User %1$s is not a member of group %2$s.', [$userName, $groupName]),
				);
			}
			if (!$shiftType->getActive()) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					"Cannot create shift from inactive shift type `$shift_type_id`",
					null,
					$this->l->t('You cannot create shifts from inactive shift type %1$s.', [$name]),
				);
			}
			[$start, $startDateTime] = Util::unlocalizeEcma($start);
			[$end, $endDateTime] = Util::unlocalizeEcma($end);
			$ignoreAbsenceForByWeekShifts = $this->configService->getIgnoreAbsenceForByWeekShifts();
			$weeklyType = $shiftType->getRepetition()['weekly_type'];
			$checkAbsence = $weeklyType !== 'by_week' || !$ignoreAbsenceForByWeekShifts;
			if (
				$checkAbsence
				&& $this->calendarService->isUserAbsent(
					$user_id,
					$startDateTime,
					$endDateTime,
				)
			) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					"Cannot create shift for absent user `\"$user_id\"`",
					null,
					$this->l->t(
						'User %1$s is absent during the shift period (%2$s).',
						[
							$userName,
							Util::formatRange(
								$startDateTime,
								$endDateTime,
								IntlDateFormatter::SHORT,
								$weeklyType === 'by_day' ? IntlDateFormatter::SHORT : IntlDateFormatter::NONE,
							),
						]
					),
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
			return new ErrorResponse($th);
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
				$shiftType = $this->shiftTypeMapper->findById($shiftTypeId);
			} catch (ShiftTypeNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `shift_type_id` could not be resolved',
					$e,
				);
			}
			$groupId = $shiftType->getGroupId();
			$groupName = $this->groupService->get($groupId)->getDisplayName();
			if (!$this->groupShiftAdminRelationService->isShiftAdmin($groupId)) {
				throw new HttpException(
					Http::STATUS_FORBIDDEN,
					"You are not a group shift admin of group `\"$groupId\"` of shift type `$shiftTypeId`",
					null,
					$this->l->t('You do not have permissions to update shifts for group %1$s.', [$groupName]),
				);
			}
			$userName = $this->userService->get($user_id)->getDisplayName();
			if (!$this->groupUserService->isMember($groupId, $user_id)) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					"The user `\"$user_id\"` is not a member of group `\"$groupId\"` of shift type `$shiftTypeId`",
					null,
					$this->l->t('User %1$s is not a member of group %2$s.', [$userName, $groupName]),
				);
			}
			$startDateTime = Util::parseEcma($shift->getStart())[0];
			$endDateTime = Util::parseEcma($shift->getEnd())[0];
			$ignoreAbsenceForByWeekShifts = $this->configService->getIgnoreAbsenceForByWeekShifts();
			$weeklyType = $shiftType->getRepetition()['weekly_type'];
			$checkAbsence = $weeklyType !== 'by_week' || !$ignoreAbsenceForByWeekShifts;
			if (
				$checkAbsence
				&& $this->calendarService->isUserAbsent(
					$user_id,
					$startDateTime,
					$endDateTime,
				)
			) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					"Cannot move shift to absent user `\"$user_id\"`",
					null,
					$this->l->t(
						'User %1$s is absent during the shift period (%2$s).',
						[
							$userName,
							Util::formatRange(
								$startDateTime,
								$endDateTime,
								IntlDateFormatter::SHORT,
								$weeklyType === 'by_day' ? IntlDateFormatter::SHORT : IntlDateFormatter::NONE,
							),
						]
					),
				);
			}
			$shiftId = $shift->getId();
			// Check for pending exchanges
			$pendingExchanges = [
				...$this->shiftExchangeMapper->findAll(
					shiftAId: $shiftId,
					done: false
				),
				...$this->shiftExchangeMapper->findAll(
					shiftBId: $shiftId,
					done: false
				),
			];
			if ($pendingExchanges) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					"Cannot move shift as there is a pending shift exchange for shift `$shiftId`",
					null,
					$this->l->t('Cannot move shift as there is a pending shift exchange for the shift.'),
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
			return new ErrorResponse($th);
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
				$shiftType = $this->shiftTypeMapper->findById($shiftTypeId);
			} catch (ShiftTypeNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `shift_type_id` could not be resolved',
					$e,
				);
			}
			$groupId = $shiftType->getGroupId();
			$groupName = $this->groupService->get($groupId)->getDisplayName();
			if (!$this->groupShiftAdminRelationService->isShiftAdmin($groupId)) {
				throw new HttpException(
					Http::STATUS_FORBIDDEN,
					"You are not a group shift admin of group `\"$groupId\"` of shift type `$shiftTypeId`",
					null,
					$this->l->t('You do not have permissions to delete shifts for group %1$s.', [$groupName]),
				);
			}
			$shift = $this->shiftMapper->deleteById($shift);
			$shiftExtended = $this->shiftService->getExtended($shift);
			$this->calendarChangeService->safeCreate($shiftExtended);
			return new DataResponse($shiftExtended);
		} catch (Throwable $th) {
			return new ErrorResponse($th);
		}
	}
}
