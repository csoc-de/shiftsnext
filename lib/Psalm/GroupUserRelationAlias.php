<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Psalm;

use OCA\ShiftsNext\Serializable\SerializableGroup;
use OCA\ShiftsNext\Serializable\SerializableUser;

/**
 * @psalm-type GroupUserRow = array{gid: string, uid: string}
 *
 * @psalm-type GroupUserRelationsByGroup = array{
 *     group: SerializableGroup,
 *     users: list<SerializableUser>,
 * }
 */
final class GroupUserRelationAlias {
}
