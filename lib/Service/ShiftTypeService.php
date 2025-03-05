<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use Exception;
use JsonSerializable;
use OCA\ShiftsNext\Db\ShiftType;
use OCA\ShiftsNext\Db\ShiftTypeMapper;
use OCA\ShiftsNext\Exception\GroupNotFoundException;
use OCA\ShiftsNext\Exception\HttpException;
use OCA\ShiftsNext\Exception\ShiftTypeNotFoundException;
use OCA\ShiftsNext\Psalm\ShiftTypeAlias;
use OCP\AppFramework\Http;
use OCP\IGroup;

use function array_map;

class ShiftTypeService {
	public function __construct(
		private ShiftTypeMapper $shiftTypeMapper,
		private GroupShiftAdminRelationService $groupShiftAdminRelationService,
		private GroupService $groupService,
	) {
	}

	/**
	 * @throws ShiftTypeNotFoundException {@see OCA\ShiftsNext\Db\ShiftTypeMapper::findById()}
	 * @throws GroupNotFoundException {@see OCA\ShiftsNext\Service\GroupService::get()}
	 */
	public function getExtended(
		int|ShiftType|ShiftTypeExtended $shiftType,
		null|string|IGroup $group = null,
	): ShiftTypeExtended {
		if ($shiftType instanceof ShiftTypeExtended) {
			return $shiftType;
		}
		$shiftType = $this->shiftTypeMapper->findById($shiftType);

		$group ??= $shiftType->getGroupId();
		$group = $this->groupService->get($group);

		return new ShiftTypeExtended($shiftType, $group);
	}

	/**
	 * @throws ShiftTypeNotFoundException {@see OCA\ShiftsNext\Db\ShiftTypeMapper::findById()}
	 * @throws HttpException if the logged-in user is not a group shift admin of the
	 *                       ShiftType's group
	 */
	public function getRestricted(int|ShiftType $shiftType): ShiftType {
		$shiftType = $this->shiftTypeMapper->findById($shiftType);
		if (!$this->groupShiftAdminRelationService->isShiftAdmin($shiftType->getGroupId())) {
			throw new HttpException(
				Http::STATUS_FORBIDDEN,
				"You are not a group shift admin for the shift type's group",
			);
		}
		return $shiftType;
	}

	/**
	 * @param null|string[] $groupIds Adds `WHERE group_id IN($groupIds)`
	 *
	 * @return ShiftTypeExtended[]
	 *
	 * @throws Exception {@see OCA\ShiftsNext\Service\ShiftTypeService::getExtended()}
	 */
	public function getAllExtended(?array $groupIds = null): array {
		$shiftTypes = $this->shiftTypeMapper->findAll($groupIds);
		return array_map($this->getExtended(...), $shiftTypes);
	}
}

/**
 * This class is used to create objects representing a shift type
 * with all its foreign keys resolved to full objects
 *
 * @psalm-import-type Repetition from ShiftTypeAlias
 * @psalm-import-type Caldav from ShiftTypeAlias
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

	public function __construct(
		ShiftType $shiftType,
		public IGroup $group,
	) {
		$this->id = $shiftType->getId();
		$this->name = $shiftType->getName();
		$this->description = $shiftType->getDescription();
		$this->color = $shiftType->getColor();
		$this->active = $shiftType->getActive();
		$this->repetition = $shiftType->getRepetition();
		$this->caldav = $shiftType->getCaldav();
	}

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
		];
	}
}
