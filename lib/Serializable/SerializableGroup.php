<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Serializable;

use JsonSerializable;
use OCP\IGroup;

final class SerializableGroup implements JsonSerializable {
	public string $id;
	public string $displayName;

	public function __construct(IGroup $group) {
		$this->id = $group->getGID();
		$this->displayName = $group->getDisplayName();
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'display_name' => $this->displayName,
		];
	}
}
