<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Extended;

use JsonSerializable;
use OCA\ShiftsNext\Db\Shift;
use OCA\ShiftsNext\Serializable\SerializableUser;
use OCP\IUser;

/**
 * This class is used to create objects representing a shift
 * with all its foreign keys resolved to full objects
 */
final class ShiftExtended implements JsonSerializable {
	public int $id;
	public string $start;
	public string $end;

	public function __construct(
		Shift $shift,
		public IUser $user,
		public ShiftTypeExtended $shiftType,
	) {
		$this->id = $shift->getId();
		$this->start = $shift->getStart();
		$this->end = $shift->getEnd();
	}

	#[\Override]
	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'user' => new SerializableUser($this->user),
			'shift_type' => $this->shiftType,
			'start' => $this->start,
			'end' => $this->end,
		];
	}
}
