<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use DateTime;
use OCA\ShiftsNext\AppInfo\Application;
use OCA\ShiftsNext\Enum\ExchangeNotificationSubject;
use OCA\ShiftsNext\Enum\ShiftNotificationSubject;
use OCA\ShiftsNext\Extended\ShiftExchangeExtended;
use OCA\ShiftsNext\Extended\ShiftExtended;
use OCP\Notification\IManager;
use Throwable;

final class NotificationService {
	public function __construct(
		private string $userId,
		private IManager $manager,
	) {
	}

	/**
	 * Sends a notification regarding the creation/update/deletion of a shift
	 *
	 * @param string $userId The ID of the user the notification will be sent to
	 * @param ShiftNotificationSubject $subject The subject of the notification
	 * @param ShiftExtended $shiftExtended The related shift
	 */
	public function sendShiftNotification(
		string $userId,
		ShiftNotificationSubject $subject,
		ShiftExtended $shiftExtended,
	): void {
		try {
			$notification = $this->manager->createNotification()
				->setApp(Application::APP_ID)
				->setUser($userId)
				->setDateTime(new DateTime())
				->setObject('shift', "$shiftExtended->id")
				->setSubject(
					"$subject->value",
					['shiftAdminId' => $this->userId, 'shiftExtended' => $shiftExtended],
				);
			$this->manager->notify($notification);
		} catch (Throwable) {
		}
	}

	/**
	 * Dismisses a previously queried shift notification
	 *
	 * @param string $userId The ID of the user the notification would be sent to
	 * @param ShiftNotificationSubject $subject The subject of the notification
	 * @param ShiftExtended $shiftExtended The related shift
	 */
	public function dismissShiftNotification(
		string $userId,
		ShiftNotificationSubject $subject,
		int $shiftId,
	): void {
		$notification = $this->manager->createNotification()
			->setApp(Application::APP_ID)
			->setUser($userId)
			->setObject('shift', "$shiftId")
			->setSubject("$subject->value");
		$this->manager->dismissNotification($notification);
	}

	/**
	 * Sends notifications regarding a shift exchange
	 *
	 * @param ExchangeNotificationSubject $subject The subject of the notification
	 * @param ShiftExchangeExtended $exchangeExtended The related shift exchange
	 */
	public function sendExchangeNotifications(
		ExchangeNotificationSubject $subject,
		ShiftExchangeExtended $exchangeExtended,
	): void {
		$userAId = $exchangeExtended->userAApproval->user?->getUID();
		$userBId = $exchangeExtended->userBApproval->user?->getUID();
	}

	/**
	 * Sends a notification regarding a shift exchange
	 *
	 * @param string $userId The ID of the user the notification will be sent to
	 * @param ExchangeNotificationSubject $subject The subject of the notification
	 * @param ShiftExchangeExtended $exchangeExtended The related shift exchange
	 */
	public function sendExchangeNotification(
		string $userId,
		ExchangeNotificationSubject $subject,
		ShiftExchangeExtended $exchangeExtended,
	): void {
		try {
			$notification = $this->manager->createNotification()
				->setApp(Application::APP_ID)
				->setUser($userId)
				->setDateTime(new DateTime())
				->setObject('exchange', "$exchangeExtended->id")
				->setSubject(
					"$subject->value",
					['actorId' => $this->userId, 'exchangeExtended' => $exchangeExtended],
				);
			$this->manager->notify($notification);
		} catch (Throwable) {
		}
	}

	/**
	 * Dismisses a previously queried shift exchange notification
	 *
	 * @param string $userId The ID of the user the notification would be sent to
	 * @param ExchangeNotificationSubject $subject The subject of the notification
	 * @param ShiftExchangeExtended $exchangeExtended The related shift exchange
	 */
	public function dismissExchangeNotification(
		string $userId,
		ExchangeNotificationSubject $subject,
		int $exchangeId,
	): void {
		$notification = $this->manager->createNotification()
			->setApp(Application::APP_ID)
			->setUser($userId)
			->setObject('exchange', "$exchangeId")
			->setSubject("$subject->value");
		$this->manager->dismissNotification($notification);
	}
}
