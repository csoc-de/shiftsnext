<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Psalm;

use OCA\ShiftsNext\Serializable\SerializableGroup;
use OCA\ShiftsNext\Serializable\SerializableUser;

/**
 * @psalm-import-type SerializableGroupSerialized from GroupAlias
 * @psalm-import-type SerializableUserSerialized from UserAlias
 *
 * @psalm-type GroupShiftAdminRelationsByGroup = array{
 *     group: SerializableGroup,
 *     users: list<SerializableUser>,
 * }
 *
 * @psalm-type GroupShiftAdminRelationExtendedSerialized = array{
 *     id: int,
 *     group: SerializableGroupSerialized,
 *     user: SerializableUserSerialized,
 * }
 */
final class GroupShiftAdminRelationAlias {
}
