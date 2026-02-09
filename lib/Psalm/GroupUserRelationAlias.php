<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Psalm;

use OCA\ShiftsNext\Serializable\SerializableGroup;
use OCA\ShiftsNext\Serializable\SerializableUser;

/**
 * @psalm-import-type SerializableGroupSerialized from GroupAlias
 * @psalm-import-type SerializableUserSerialized from UserAlias
 *
 * @psalm-type GroupUserRow = array{gid: string, uid: string}
 *
 * @psalm-type GroupUserRelationsByGroup = array{
 *     group: SerializableGroup,
 *     users: list<SerializableUser>,
 * }
 *
 * @psalm-type GroupUserRelationExtendedSerialized = array{
 *     group: SerializableGroupSerialized,
 *     user: SerializableUserSerialized,
 * }
 */
final class GroupUserRelationAlias {
}
