<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Notification;

use IntlDateFormatter;
use OCA\ShiftsNext\AppInfo\Application;
use OCA\ShiftsNext\Enum\ExchangeNotificationSubject;
use OCA\ShiftsNext\Enum\ShiftNotificationSubject;
use OCA\ShiftsNext\Psalm\ShiftAlias;
use OCA\ShiftsNext\Psalm\ShiftExchangeAlias;
use OCA\ShiftsNext\Service\ConfigService;
use OCA\ShiftsNext\Service\UserService;
use OCA\ShiftsNext\Util\Util;
use OCP\IL10N;
use OCP\L10N\IFactory;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;
use OCP\Notification\UnknownNotificationException;

/**
 * @psalm-import-type ShiftNotificationSubjectParameters from ShiftAlias
 * @psalm-import-type ExchangeNotificationSubjectParameters from ShiftExchangeAlias
 */
final class Notifier implements INotifier {
	public function __construct(
		private IFactory $factory,
		private ConfigService $configService,
		private UserService $userService,
	) {
	}

	#[\Override]
	public function getID(): string {
		return Application::APP_ID;
	}

	#[\Override]
	public function getName(): string {
		return $this->factory->get(Application::APP_ID)->t('Shifts Next');
	}

	#[\Override]
	public function prepare(
		INotification $notification,
		string $languageCode,
	): INotification {
		if ($notification->getApp() !== Application::APP_ID) {
			throw new UnknownNotificationException();
		}
		$l = $this->factory->get(Application::APP_ID, $languageCode);
		$origSubject = $notification->getSubject();
		$subject = ShiftNotificationSubject::tryFrom($origSubject);
		if ($subject instanceof ShiftNotificationSubject) {
			return $this->getParsedShiftNotification($subject, $notification, $l);
		}
		$subject = ExchangeNotificationSubject::tryFrom($origSubject);
		if ($subject instanceof ExchangeNotificationSubject) {
			return $this->getParsedExchangeNotification($subject, $notification, $l);
		}
		throw new UnknownNotificationException();
	}

	private function getParsedShiftNotification(
		ShiftNotificationSubject $subject,
		INotification $notification,
		IL10N $l,
	): INotification {
		/** @var ShiftNotificationSubjectParameters */
		$subjectParams = $notification->getSubjectParameters();
		$shiftAdminId = $subjectParams['shiftAdminId'];
		$shiftExtended = $subjectParams['shiftExtended'];
		$weeklyType = $shiftExtended['shift_type']['repetition']['weekly_type'];
		$translationParams = [
			$this->userService->get($shiftAdminId)->getDisplayName(),
			$shiftExtended['shift_type']['group']['display_name'],
			$shiftExtended['shift_type']['name'],
			Util::formatRange(
				Util::parseEcma($shiftExtended['start'])[0],
				Util::parseEcma($shiftExtended['end'])[0],
				IntlDateFormatter::SHORT,
				$weeklyType === 'by_day'
					? IntlDateFormatter::SHORT
					: IntlDateFormatter::NONE,
				$this->configService->getLocale($notification->getUser()),
			),
			$shiftExtended['user']['display_name'],
		];
		return match ($subject) {
			ShiftNotificationSubject::Assigned => $notification
				->setParsedSubject($l->t('Shift assigned'))
				->setParsedMessage($l->t(
					'%1$s assigned %2$s %3$s (%4$s) to you.',
					$translationParams,
				)),
			ShiftNotificationSubject::Moved => $notification
				->setParsedSubject($l->t('Shift moved'))
				->setParsedMessage($l->t(
					'%1$s moved %2$s %3$s (%4$s) from you to %5$s.',
					$translationParams,
				)),
			ShiftNotificationSubject::Deleted => $notification
				->setParsedSubject($l->t('Shift deleted'))
				->setParsedMessage($l->t(
					'%1$s deleted %2$s %3$s (%4$s).',
					$translationParams,
				))
		};
	}

	private function getParsedExchangeNotification(
		ExchangeNotificationSubject $subject,
		INotification $notification,
		IL10N $l,
	): INotification {
		$receiverId = $notification->getUser();
		/** @var ExchangeNotificationSubjectParameters */
		$subjectParams = $notification->getSubjectParameters();
		$actorId = $subjectParams['actorId'];
		$exchangeExtended = $subjectParams['exchangeExtended'];
		$actorName = $this->userService->get($actorId)->getDisplayName();
		$participantA = $exchangeExtended['user_a_approval']['user'];
		$participantB = $exchangeExtended['user_b_approval']['user'];
		$otherName = $receiverId === $participantA['id']
			? $participantB['display_name'] : $participantA['display_name'];
		return match ($subject) {
			ExchangeNotificationSubject::DeletedByParticipant => $notification
				->setParsedSubject($l->t('Shift exchange deleted'))
				->setParsedMessage($l->t(
					'%1$s deleted your common shift exchange.',
					[$actorName]
				)),
			ExchangeNotificationSubject::DeletedByAdmin => $notification
				->setParsedSubject($l->t('Shift exchange deleted by shift admin'))
				->setParsedMessage($l->t(
					'%1$s deleted a shift exchange between you and %2$s.',
					[$actorName, $otherName]
				)),
		};
	}
}
