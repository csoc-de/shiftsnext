<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Serializable;

use JsonSerializable;
use OCP\IUser;

final class SerializableUser implements JsonSerializable {
	public string $id;
	public string $displayName;

	public function __construct(IUser $user) {
		$this->id = $user->getUID();
		$this->displayName = $user->getDisplayName();
	}

	#[\Override]
	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'display_name' => $this->displayName,
		];
	}
}
