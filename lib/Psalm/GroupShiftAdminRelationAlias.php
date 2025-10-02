<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Psalm;

use OCA\ShiftsNext\Serializable\SerializableGroup;
use OCA\ShiftsNext\Serializable\SerializableUser;

/**
 * @psalm-type GroupShiftAdminRelationsByGroup = array{
 *     group: SerializableGroup,
 *     users: SerializableUser[],
 * }
 */
class GroupShiftAdminRelationAlias {
}
