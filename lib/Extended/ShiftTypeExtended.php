<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Extended;

use JsonSerializable;
use OCA\ShiftsNext\Db\ShiftType;
use OCA\ShiftsNext\Psalm\CalendarAlias;
use OCA\ShiftsNext\Psalm\ShiftTypeAlias;
use OCA\ShiftsNext\Serializable\SerializableGroup;
use OCP\IGroup;
use Override;

/**
 * This class is used to create objects representing a shift type
 * with all its foreign keys resolved to full objects
 *
 * @psalm-import-type Repetition from ShiftTypeAlias
 * @psalm-import-type Caldav from ShiftTypeAlias
 * @psalm-import-type SanitizedCalendar from CalendarAlias
 */
final class ShiftTypeExtended implements JsonSerializable {
	public int $id;
	public string $name;
	public string $description;
	public string $color;
	public bool $active;
	/** @var Repetition */
	public array $repetition;
	/** @var Caldav */
	public array $caldav;
	public bool $syncToCalendar;

	/**
	 * @param null|SanitizedCalendar $calendar
	 */
	public function __construct(
		ShiftType $shiftType,
		public IGroup $group,
		public ?array $calendar,
	) {
		$this->id = $shiftType->getId();
		$this->name = $shiftType->getName();
		$this->description = $shiftType->getDescription();
		$this->color = $shiftType->getColor();
		$this->active = $shiftType->getActive();
		$this->repetition = $shiftType->getRepetition();
		$this->caldav = $shiftType->getCaldav();
		$this->syncToCalendar = $shiftType->getSyncToCalendar();
	}

	#[Override]
	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'group' => new SerializableGroup($this->group),
			'name' => $this->name,
			'description' => $this->description,
			'color' => $this->color,
			'active' => $this->active,
			'repetition' => (object)$this->repetition,
			'caldav' => (object)$this->caldav,
			'sync_to_calendar' => $this->syncToCalendar,
			'calendar' => $this->calendar,
		];
	}
}
