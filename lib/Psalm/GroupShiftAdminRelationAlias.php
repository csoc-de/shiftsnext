<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Psalm;

use OCA\ShiftsNext\Service\SerializableGroup;
use OCA\ShiftsNext\Service\SerializableUser;

/**
 * @psalm-type GroupShiftAdminRelationsByGroup = array{
 *     group: SerializableGroup,
 *     users: SerializableUser[],
 * }
 */
class GroupShiftAdminRelationAlias {
}
