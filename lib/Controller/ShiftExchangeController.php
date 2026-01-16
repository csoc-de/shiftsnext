<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Controller;

use IntlDateFormatter;
use OCA\ShiftsNext\Db\ShiftExchangeApprovalMapper;
use OCA\ShiftsNext\Db\ShiftExchangeMapper;
use OCA\ShiftsNext\Db\ShiftMapper;
use OCA\ShiftsNext\Enum\ExchangeApprovalType;
use OCA\ShiftsNext\Exception\HttpException;
use OCA\ShiftsNext\Exception\ShiftExchangeApprovalNotFoundException;
use OCA\ShiftsNext\Exception\ShiftExchangeNotFoundException;
use OCA\ShiftsNext\Exception\ShiftNotFoundException;
use OCA\ShiftsNext\Exception\ShiftTypeNotFoundException;
use OCA\ShiftsNext\Response\ErrorResponse;
use OCA\ShiftsNext\Service\CalendarChangeService;
use OCA\ShiftsNext\Service\CalendarService;
use OCA\ShiftsNext\Service\ConfigService;
use OCA\ShiftsNext\Service\GroupShiftAdminRelationService;
use OCA\ShiftsNext\Service\GroupUserRelationService;
use OCA\ShiftsNext\Service\ShiftExchangeService;
use OCA\ShiftsNext\Service\ShiftService;
use OCA\ShiftsNext\Service\UserService;
use OCA\ShiftsNext\Util\Util;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IL10N;
use OCP\IRequest;
use Throwable;

use function array_key_exists;
use function array_walk;
use function in_array;

final class ShiftExchangeController extends Controller {
	public function __construct(
		private IL10N $l,
		string $appName,
		IRequest $request,
		private ShiftExchangeApprovalMapper $shiftExchangeApprovalMapper,
		private ShiftExchangeMapper $shiftExchangeMapper,
		private ShiftMapper $shiftMapper,
		private ShiftExchangeService $shiftExchangeService,
		private ShiftService $shiftService,
		private GroupUserRelationService $groupUserService,
		private GroupShiftAdminRelationService $groupShiftAdminRelationService,
		private string $userId,
		private CalendarChangeService $calendarChangeService,
		private UserService $userService,
		private ConfigService $configService,
		private CalendarService $calendarService,
	) {
		parent::__construct($appName, $request);
	}

	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'GET', url: '/api/shift-exchanges')]
	public function index(): DataResponse {
		try {
			try {
				$shiftExchangesExtended = $this->shiftExchangeService->getAllExtended();
			} catch (ShiftNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `shift_*_id` could not be resolved',
					$e,
				);
			} catch (ShiftTypeNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `shift_type_id` of `shift_*` could not be resolved',
					$e,
				);
			} catch (ShiftExchangeApprovalNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `*_approval_id` could not be resolved',
					$e,
				);
			}
			return new DataResponse($shiftExchangesExtended);
		} catch (Throwable $th) {
			return new ErrorResponse($th);
		}
	}

	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'GET', url: '/api/shift-exchanges/{id}')]
	public function show(int $id): DataResponse {
		try {
			try {
				$shiftExchangeExtended = $this->shiftExchangeService->getExtended($id);
			} catch (ShiftExchangeNotFoundException $e) {
				throw new HttpException(Http::STATUS_NOT_FOUND, null, $e);
			} catch (ShiftNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `shift_*_id` could not be resolved',
					$e,
				);
			} catch (ShiftTypeNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `shift_type_id` of `shift_*` could not be resolved',
					$e,
				);
			} catch (ShiftExchangeApprovalNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `*_approval_id` could not be resolved',
					$e,
				);
			}
			return new DataResponse($shiftExchangeExtended);
		} catch (Throwable $th) {
			return new ErrorResponse($th);
		}
	}

	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'POST', url: '/api/shift-exchanges')]
	public function create(
		int $shift_a_id,
		string $comment,
		?int $shift_b_id = null,
		?string $transfer_to_user_id = null,
	): DataResponse {
		try {
			try {
				$shiftA = $this->shiftService->getExtended($shift_a_id);
				$userAId = $shiftA->user->getUID();
				$groupIds = [$shiftA->shiftType->group->getGID()];
				if ($shift_b_id !== null && $transfer_to_user_id !== null) {
					throw new HttpException(
						Http::STATUS_BAD_REQUEST,
						'shift_b_id and transfer_to_user_id are mutually exclusive',
						null,
						$this->l->t('You can either exchange shifts or transfer a shift to another user, but not both.'),
					);
				}
				if ($shift_b_id !== null) {
					$shiftB = $this->shiftService->getExtended($shift_b_id);
					$userBId = $shiftB->user->getUID();
					$groupIds[] = $shiftB->shiftType->group->getGID();
				} elseif ($transfer_to_user_id !== null) {
					$shiftB = null;
					$userBId = $transfer_to_user_id;
				} else {
					throw new HttpException(
						Http::STATUS_BAD_REQUEST,
						'Either shift_b_id or transfer_to_user_id need to be set',
						null,
						$this->l->t('Specify a second shift or a user to transfer the shift to.'),
					);
				}
				$isGroupShiftAdmin = $this->groupShiftAdminRelationService->isShiftAdminAll($groupIds);
				if ($userAId !== $this->userId && !$isGroupShiftAdmin) {
					throw new HttpException(
						Http::STATUS_FORBIDDEN,
						'You can create a shift exchange for other users only if you have the appropriate shift admin privileges',
						null,
						$this->l->t('You do not have permissions to create shift exchanges for all specified shifts.'),
					);
				}
			} catch (ShiftNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `shift_*_id` could not be resolved',
					$e,
				);
			} catch (ShiftTypeNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `shift_type_id` of `shift_*` could not be resolved',
					$e,
				);
			}

			$ignoreAbsenceForByWeekShifts = $this->configService->getIgnoreAbsenceForByWeekShifts();

			if ($shiftB !== null) { // Regular
				if ($userAId === $userBId) {
					throw new HttpException(
						Http::STATUS_UNPROCESSABLE_ENTITY,
						'You cannot exchange shifts with yourself',
						null,
						$this->l->t('Exchanging shifts with yourself is not possible.'),
					);
				}
				if ($shiftA->shiftType->group->getGID() !== $shiftB->shiftType->group->getGID()) {
					throw new HttpException(
						Http::STATUS_UNPROCESSABLE_ENTITY,
						"You can only exchange shifts, whose shift types's groups are identical",
						null,
						$this->l->t('Shifts can only be exchanged within the same group.'),
					);
				}

				// Check for pending exchanges for shiftB
				$shiftBPendingExchanges = [
					...$this->shiftExchangeMapper->findAll(
						shiftAId: $shift_b_id,
						done: false,
					),
					...$this->shiftExchangeMapper->findAll(
						shiftBId: $shift_b_id,
						done: false,
					),
				];
				$shiftBStartDateTime = Util::parseEcma($shiftB->start)[0];
				$shiftBEndDateTime = Util::parseEcma($shiftB->end)[0];
				$weeklyType = $shiftB->shiftType->repetition['weekly_type'];
				if ($shiftBPendingExchanges) {
					throw new HttpException(
						Http::STATUS_UNPROCESSABLE_ENTITY,
						"There is a pending shift exchange for shift B `$shift_b_id`",
						null,
						$this->l->t(
							'You cannot create a new shift exchange as long as there is a pending shift exchange for %1$s %2$s (%3$s).',
							[
								$shiftB->shiftType->group->getDisplayName(),
								$shiftB->shiftType->name,
								Util::formatRange(
									$shiftBStartDateTime,
									$shiftBEndDateTime,
									IntlDateFormatter::SHORT,
									$weeklyType === 'by_day' ? IntlDateFormatter::SHORT : IntlDateFormatter::NONE,
								),
							]
						),
					);
				}

				$checkAbsence = $weeklyType !== 'by_week' || !$ignoreAbsenceForByWeekShifts;

				// Check if userA is absent during shiftB
				if (
					$checkAbsence
					&& $this->calendarService->isUserAbsent(
						$userAId,
						$shiftBStartDateTime,
						$shiftBEndDateTime,
					)
				) {
					$userName = $this->userService->get($userAId)->getDisplayName();
					throw new HttpException(
						Http::STATUS_UNPROCESSABLE_ENTITY,
						"Participant A `\"$userAId\"` appears to be absent during shift B `$shift_b_id`",
						null,
						$this->l->t(
							'Participant A %1$s is absent during shift B period (%2$s).',
							[
								$userName,
								Util::formatRange(
									$shiftBStartDateTime,
									$shiftBEndDateTime,
									IntlDateFormatter::SHORT,
									$weeklyType === 'by_day' ? IntlDateFormatter::SHORT : IntlDateFormatter::NONE,
								),
							]
						),
					);
				}
			} else { // Transfer
				if ($userAId === $userBId) {
					throw new HttpException(
						Http::STATUS_UNPROCESSABLE_ENTITY,
						'You cannot transfer shifts to yourself',
						null,
						$this->l->t('Transfering shifts to yourself is not possible.'),
					);
				}
				if (
					!$this->groupUserService->isMember(
						$shiftA->shiftType->group->getGID(),
						$userBId,
					)
				) {
					throw new HttpException(
						Http::STATUS_UNPROCESSABLE_ENTITY,
						'The user you are trying to transfer the shift to, is not a member of the group of the shift type of the shift',
						null,
						$this->l->t("Shifts can only be transfered to users who are members of the shift's group."),
					);
				}
			}

			// Check for pending exchanges for shiftA
			$shiftAPendingExchanges = [
				...$this->shiftExchangeMapper->findAll(
					shiftAId: $shift_a_id,
					done: false,
				),
				...$this->shiftExchangeMapper->findAll(
					shiftBId: $shift_a_id,
					done: false,
				)
			];
			$shiftAStartDateTime = Util::parseEcma($shiftA->start)[0];
			$shiftAEndDateTime = Util::parseEcma($shiftA->end)[0];
			$weeklyType = $shiftA->shiftType->repetition['weekly_type'];
			if ($shiftAPendingExchanges) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					"There is a pending shift exchange for shift A `$shift_a_id`",
					null,
					$this->l->t(
						'You cannot create a new shift exchange as long as there is a pending shift exchange for %1$s %2$s (%3$s).',
						[
							$shiftA->shiftType->group->getDisplayName(),
							$shiftA->shiftType->name,
							Util::formatRange(
								$shiftAStartDateTime,
								$shiftAEndDateTime,
								IntlDateFormatter::SHORT,
								$weeklyType === 'by_day' ? IntlDateFormatter::SHORT : IntlDateFormatter::NONE,
							),
						]
					),
				);
			}

			$checkAbsence = $weeklyType !== 'by_week' || !$ignoreAbsenceForByWeekShifts;

			// Check if userB is absent during shiftA
			if (
				$checkAbsence
				&& $this->calendarService->isUserAbsent(
					$userBId,
					$shiftAStartDateTime,
					$shiftAEndDateTime,
				)
			) {
				$userName = $this->userService->get($userBId)->getDisplayName();
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					"Participant B `\"$userBId\"` appears to be absent during shift A `$shift_a_id`",
					null,
					$this->l->t(
						'Participant B %1$s is absent during shift A period (%2$s).',
						[
							$userName,
							Util::formatRange(
								$shiftAStartDateTime,
								$shiftAEndDateTime,
								IntlDateFormatter::SHORT,
								$weeklyType === 'by_day' ? IntlDateFormatter::SHORT : IntlDateFormatter::NONE,
							),
						]
					),
				);
			}

			$userAApproval = $this->shiftExchangeApprovalMapper->create(
				$userAId,
				$this->userId === $userAId ? true : null,
			);
			$userBApproval = $this->shiftExchangeApprovalMapper->create(
				$userBId,
				$this->userId === $userBId ? true : null,
			);
			$adminApproval = $this->shiftExchangeApprovalMapper->create(
				$isGroupShiftAdmin ? $this->userId : null,
				$isGroupShiftAdmin ? true : null,
			);

			$approvalType = $this->configService->getExchangeApprovalType();

			$requiredApprovedList = match ($approvalType) {
				ExchangeApprovalType::All => [
					$userAApproval->getApproved(),
					$userBApproval->getApproved(),
					$adminApproval->getApproved(),
				],
				ExchangeApprovalType::Users => [
					$userAApproval->getApproved(),
					$userBApproval->getApproved(),
				],
				ExchangeApprovalType::Admin => [
					$adminApproval->getApproved(),
				],
			};

			$done = !in_array(null, $requiredApprovedList, true);

			$approved = !$done ? null : !in_array(false, $requiredApprovedList, true);

			$shiftExchange = $this->shiftExchangeMapper->create(
				$shift_a_id,
				$shift_b_id,
				$transfer_to_user_id,
				$comment,
				$done,
				$approved,
				$userAApproval->getId(),
				$userBApproval->getId(),
				$adminApproval->getId(),
			);

			if ($approved) {
				// This queues a removal of shift A from user A's calendar
				$this->calendarChangeService->safeCreate($shiftA);

				$updatedShiftA
					= $this->shiftMapper->updateById($shiftA->id, $userBId);

				// This queues a creation of shift A in user B's calendar
				$this->calendarChangeService->safeCreate($updatedShiftA);
				if ($shiftB !== null) {
					// This queues a removal of shift B from user B's calendar
					$this->calendarChangeService->safeCreate($shiftB);

					$updatedShiftB
						= $this->shiftMapper->updateById($shiftB->id, $userAId);

					// This queues a creation of shift B in user A's calendar
					$this->calendarChangeService->safeCreate($updatedShiftB);
				}
			}

			$shiftExchangeExtended = $this->shiftExchangeService->getExtended($shiftExchange);
			return new DataResponse($shiftExchangeExtended);
		} catch (Throwable $th) {
			return new ErrorResponse($th);
		}
	}

	/**
	 * @param array{user?: ?boolean, admin?: ?boolean} $approveds
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'PUT', url: '/api/shift-exchanges/{id}')]
	public function update(
		int $id,
		array $approveds,
		?string $comment = null,
	): DataResponse {
		try {
			try {
				$shiftExchange = $this->shiftExchangeMapper->findById($id);
				if ($shiftExchange->getDone()) {
					throw new HttpException(
						Http::STATUS_UNPROCESSABLE_ENTITY,
						'Cannot update a shift exchange that is already done',
						null,
						$this->l->t('Cannot update a shift exchange that has already been marked as done.'),
					);
				}
				if (
					array_key_exists('user', $approveds)
					&& array_key_exists('admin', $approveds)
				) {
					throw new HttpException(
						Http::STATUS_BAD_REQUEST,
						'approveds.user and approveds.admin are mutually exclusive',
						null,
						$this->l->t('You cannot update the participant and admin approval at the same time.'),
					);
				}

				$shiftExchangeExtended = $this->shiftExchangeService->getExtended($shiftExchange);

				$shiftA = $shiftExchangeExtended->shiftA;
				$userAId = $shiftA->user->getUID();
				$shiftB = $shiftExchangeExtended->shiftB;
				$transferToUserId = $shiftExchangeExtended->transferToUser?->getUID();
				$userBId = $shiftB?->user?->getUID() ?? $transferToUserId;

				$groupIds = [$shiftA->shiftType->group->getGID()];
				if ($shiftB) {
					$groupIds[] = $shiftB->shiftType->group->getGID();
				}
			} catch (ShiftExchangeNotFoundException $e) {
				throw new HttpException(Http::STATUS_NOT_FOUND, null, $e);
			} catch (ShiftNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `shift_*_id` could not be resolved',
					$e,
				);
			} catch (ShiftTypeNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `shift_type_id` of `shift_*` could not be resolved',
					$e,
				);
			} catch (ShiftExchangeApprovalNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `*_approval_id` could not be resolved',
					$e,
				);
			}

			$userAApproval = $this->shiftExchangeApprovalMapper->findById($shiftExchange->getUserAApprovalId());
			$userBApproval = $this->shiftExchangeApprovalMapper->findById($shiftExchange->getUserBApprovalId());
			$adminApproval = $this->shiftExchangeApprovalMapper->findById($shiftExchange->getAdminApprovalId());

			if (array_key_exists('user', $approveds)) {
				switch ($this->userId) {
					case $userAId:
						$userAApproval = $this->shiftExchangeApprovalMapper->updateById(
							$userAApproval,
							approved: $approveds['user'],
						);
						break;
					case $userBId:
						$userBApproval = $this->shiftExchangeApprovalMapper->updateById(
							$userBApproval,
							approved: $approveds['user'],
						);
						break;
					default:
						throw new HttpException(
							Http::STATUS_FORBIDDEN,
							'The participant approval can only be updated by the participating users',
							null,
							$this->l->t('The participant approval can only be updated by the participating users.'),

						);
				}
			} elseif (array_key_exists('admin', $approveds)) {
				$isGroupShiftAdmin = $this->groupShiftAdminRelationService->isShiftAdminAll($groupIds);
				if ($isGroupShiftAdmin) {
					$adminApproval = $this->shiftExchangeApprovalMapper->updateById(
						$adminApproval,
						$this->userId,
						$approveds['admin'],
					);
				} else {
					throw new HttpException(
						Http::STATUS_FORBIDDEN,
						'The admin approval can only be updated by appropriate group shift admins',
						null,
						$this->l->t('The admin approval can only be updated by appropriate group shift admins.'),
					);
				}
			}


			$approvalType = $this->configService->getExchangeApprovalType();

			$requiredApprovedList = match ($approvalType) {
				ExchangeApprovalType::All => [
					$userAApproval->getApproved(),
					$userBApproval->getApproved(),
					$adminApproval->getApproved(),
				],
				ExchangeApprovalType::Users => [
					$userAApproval->getApproved(),
					$userBApproval->getApproved(),
				],
				ExchangeApprovalType::Admin => [
					$adminApproval->getApproved(),
				],
			};

			$done = !in_array(null, $requiredApprovedList, true);

			$approved = !$done ? null : !in_array(false, $requiredApprovedList, true);

			$shiftExchange = $this->shiftExchangeMapper->updateById(
				$shiftExchange,
				$comment,
				$done,
				$approved
			);

			if ($approved) {
				// This queues a removal of shift A from user A's calendar
				$this->calendarChangeService->safeCreate($shiftA);

				$updatedShiftA
					= $this->shiftMapper->updateById($shiftA->id, $userBId);

				// This queues a creation of shift A in user B's calendar
				$this->calendarChangeService->safeCreate($updatedShiftA);
				if ($shiftB !== null) {
					// This queues a removal of shift B from user B's calendar
					$this->calendarChangeService->safeCreate($shiftB);

					$updatedShiftB
						= $this->shiftMapper->updateById($shiftB->id, $userAId);

					// This queues a creation of shift B in user A's calendar
					$this->calendarChangeService->safeCreate($updatedShiftB);
				}
			}

			$shiftExchangeExtended = $this->shiftExchangeService->getExtended($shiftExchange);
			return new DataResponse($shiftExchangeExtended);
		} catch (Throwable $th) {
			return new ErrorResponse($th);
		}
	}

	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'DELETE', url: '/api/shift-exchanges/{id}')]
	public function destroy(int $id): DataResponse {
		try {
			try {
				$shiftExchange = $this->shiftExchangeMapper->findById($id);
				$shiftExchangeExtended = $this->shiftExchangeService->getExtended($shiftExchange);
				$done = $shiftExchange->getDone();

				$shiftA = $shiftExchangeExtended->shiftA;
				$shiftB = $shiftExchangeExtended->shiftB;
				$transferToUser = $shiftExchangeExtended->transferToUser;

				$userAApprovalId = $shiftExchangeExtended->userAApproval->id;
				$userBApprovalId = $shiftExchangeExtended->userBApproval->id;
				$adminApprovalId = $shiftExchangeExtended->adminApproval->id;

				$groupIds = [$shiftA->shiftType->group->getGID()];
				if ($shiftB) {
					$groupIds[] = $shiftB->shiftType->group->getGID();
				}

				$isGroupShiftAdmin = $this->groupShiftAdminRelationService->isShiftAdminAll($groupIds);

				if ($done && !$isGroupShiftAdmin) {
					throw new HttpException(
						Http::STATUS_UNPROCESSABLE_ENTITY,
						'As a normal user, you are not allowed to delete a shift exchange that is already done',
						null,
						$this->l->t('Shift exchanges already marked as done can only be deleted by appropriate group shift admins.'),
					);
				}

				$userAId = $shiftA->user->getUID();
				$userBId = $shiftB?->user?->getUID() ?? $transferToUser?->getUID();

				$participantUserIds = [$userAId];
				if ($userBId !== null) {
					$participantUserIds[] = $userBId;
				}

				$isParticipant = in_array($this->userId, $participantUserIds, true);
				if (!$isParticipant && !$isGroupShiftAdmin) {
					throw new HttpException(
						Http::STATUS_FORBIDDEN,
						'Shift exchanges can only be deleted by the participating users or appropriate group shift admins',
						null,
						$this->l->t('Shift exchanges can only be deleted by the participating users or appropriate group shift admins.'),
					);
				}
				$this->shiftExchangeMapper->deleteById($shiftExchange);
				$approvalIds = [$userAApprovalId, $userBApprovalId, $adminApprovalId];
				array_walk($approvalIds, $this->shiftExchangeApprovalMapper->deleteById(...));
			} catch (ShiftExchangeNotFoundException $e) {
				throw new HttpException(Http::STATUS_NOT_FOUND, null, $e);
			} catch (ShiftNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `shift_*_id` could not be resolved',
					$e,
				);
			} catch (ShiftTypeNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `shift_type_id` of `shift_*` could not be resolved',
					$e,
				);
			} catch (ShiftExchangeApprovalNotFoundException $e) {
				throw new HttpException(
					Http::STATUS_INTERNAL_SERVER_ERROR,
					'Foreign key `*_approval_id` could not be resolved',
					$e,
				);
			}
			return new DataResponse($shiftExchangeExtended);
		} catch (Throwable $th) {
			return new ErrorResponse($th);
		}
	}
}
