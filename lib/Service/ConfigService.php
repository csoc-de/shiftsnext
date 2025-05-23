<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use InvalidArgumentException;
use Jawira\CaseConverter\Convert;
use OCA\ShiftsNext\AppInfo\Application;
use OCP\IAppConfig;
use OCP\IConfig;

use function is_string;
use function json_decode;
use function json_encode;

enum AppConfigKey: string {
	case CommonCalendarId = 'common_calendar_id';
	case AbsenceCalendarId = 'absence_calendar_id';
	case SyncToPersonalCalendar = 'sync_to_personal_calendar';
	case IgnoreAbsenceForByWeekShifts = 'ignore_absence_for_by_week_shifts';
	case ExchangeApprovalType = 'exchange_approval_type';
}

enum UserConfigKey: string {
	case DefaultGroupIds = 'defaultGroupIds';
}

enum ExchangeApprovalType: string {
	case Users = 'users';
	case Admin = 'admin';
	case All = 'all';
}

// Redeclaring these aliases is somehow required because Psalm's value-of
// utility type does not work with imported aliases
/**
 * @psalm-type AppConfig = array{
 *     common_calendar_id: int,
 *     absence_calendar_id: int,
 *     sync_to_personal_calendar: bool,
 *     ignore_absence_for_by_week_shifts: bool,
 *     exchange_approval_type: 'users'|'admin'|'all',
 * }
 *
 * @psalm-type UserConfig = array{
 *     defaultGroupIds: string[],
 * }
 */
class ConfigService {
	public function __construct(
		private string $userId,
		private IAppConfig $appConfig,
		private IConfig $config,
	) {
	}

	/**
	 * @psalm-suppress PossiblyUnusedMethod Called dynamically by
	 * {@see OCA\ShiftsNext\Service\ConfigService::setConfigValue()}
	 */
	public function setCommonCalendarId(int $id): static {
		$this->appConfig->setValueInt(
			Application::APP_ID,
			AppConfigKey::CommonCalendarId->value,
			$id,
		);
		return $this;
	}

	public function getCommonCalendarId(): int {
		return $this->appConfig->getValueInt(
			Application::APP_ID,
			AppConfigKey::CommonCalendarId->value,
		);
	}

	/**
	 * @psalm-suppress PossiblyUnusedMethod Called dynamically by
	 * {@see OCA\ShiftsNext\Service\ConfigService::setConfigValue()}
	 */
	public function setAbsenceCalendarId(int $id): static {
		$this->appConfig->setValueInt(
			Application::APP_ID,
			AppConfigKey::AbsenceCalendarId->value,
			$id,
		);
		return $this;
	}

	public function getAbsenceCalendarId(): int {
		return $this->appConfig->getValueInt(
			Application::APP_ID,
			AppConfigKey::AbsenceCalendarId->value,
		);
	}

	/**
	 * @psalm-suppress PossiblyUnusedMethod Called dynamically by
	 * {@see OCA\ShiftsNext\Service\ConfigService::setConfigValue()}
	 */
	public function setSyncToPersonalCalendar(bool $value): static {
		$this->appConfig->setValueBool(
			Application::APP_ID,
			AppConfigKey::SyncToPersonalCalendar->value,
			$value,
		);
		return $this;
	}

	public function getSyncToPersonalCalendar(): bool {
		return $this->appConfig->getValueBool(
			Application::APP_ID,
			AppConfigKey::SyncToPersonalCalendar->value,
			true,
		);
	}

	/**
	 * @psalm-suppress PossiblyUnusedMethod Called dynamically by
	 * {@see OCA\ShiftsNext\Service\ConfigService::setConfigValue()}
	 */
	public function setIgnoreAbsenceForByWeekShifts(bool $value): static {
		$this->appConfig->setValueBool(
			Application::APP_ID,
			AppConfigKey::IgnoreAbsenceForByWeekShifts->value,
			$value,
		);
		return $this;
	}

	public function getIgnoreAbsenceForByWeekShifts(): bool {
		return $this->appConfig->getValueBool(
			Application::APP_ID,
			AppConfigKey::IgnoreAbsenceForByWeekShifts->value,
			true,
		);
	}

	/**
	 * @psalm-suppress PossiblyUnusedMethod Called dynamically by
	 * {@see OCA\ShiftsNext\Service\ConfigService::setConfigValue()}
	 */
	public function setExchangeApprovalType(ExchangeApprovalType $type): static {
		$this->appConfig->setValueString(
			Application::APP_ID,
			AppConfigKey::ExchangeApprovalType->value,
			$type->value,
		);
		return $this;
	}

	public function getExchangeApprovalType(): ExchangeApprovalType {
		$type = $this->appConfig->getValueString(
			Application::APP_ID,
			AppConfigKey::ExchangeApprovalType->value,
			'all',
		);
		return ExchangeApprovalType::from($type);
	}

	/**
	 * Sets group IDs for the logged-in user, which are used as the default
	 * group filter values on the shifts view
	 *
	 * @param string[] $groupIds
	 *
	 * @return static
	 */
	public function setDefaultGroupIds(array $groupIds): static {
		$this->config->setUserValue(
			$this->userId,
			Application::APP_ID,
			UserConfigKey::DefaultGroupIds->value,
			json_encode($groupIds),
		);
		return $this;
	}

	/**
	 * Gets group IDs for the logged-in user, which are used as the default
	 * group filter values on the shifts view
	 *
	 * @return string[]
	 */
	public function getDefaultGroupIds(): array {
		/** @var string[] */
		return json_decode(
			$this->config->getUserValue(
				$this->userId,
				Application::APP_ID,
				UserConfigKey::DefaultGroupIds->value,
				'[]',
			),
		);
	}

	/**
	 * Sets a configuration value
	 *
	 * @param AppConfigKey|UserConfigKey $configKey
	 * @param value-of<AppConfig|UserConfig> $value
	 *
	 * @throws InvalidArgumentException If `$configKey` is `AppConfigKey::ExchangeApprovalType` and `$value` is not a string
	 */
	public function setConfigValue(
		AppConfigKey|UserConfigKey $configKey,
		mixed $value,
	): static {
		if ($configKey === AppConfigKey::ExchangeApprovalType) {
			if (!is_string($value)) {
				throw new InvalidArgumentException(
					'The given value is not a valid exchange approval type',
				);
			}
			$value = ExchangeApprovalType::from($value);
		}
		$convert = new Convert($configKey->value);
		$methodName = 'set' . $convert->toPascal();
		$this->$methodName($value);
		return $this;
	}

	/**
	 * Gets the time zone, e.g. "Europe/Berlin" of `$userId`.
	 *
	 * The time zone is only set if the user did log in at least once,
	 * otherwise "UTC" is returned.
	 *
	 * @param null|string $userId If `null`, the logged-in user is used
	 *
	 * @return non-empty-string
	 */
	public function getTimeZone(?string $userId = null): string {
		return $this->config->getUserValue(
			$userId ?? $this->userId,
			'core',
			'timezone',
		) ?: 'UTC';
	}
}
