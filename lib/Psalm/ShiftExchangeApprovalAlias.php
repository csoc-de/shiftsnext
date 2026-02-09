<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Psalm;

/**
 * @psalm-import-type SerializableUserSerialized from UserAlias
 *
 * @psalm-type ShiftExchangeApprovalExtendedSerialized = array{
 *     id: int,
 *     user: null|SerializableUserSerialized,
 *     approved: null|bool,
 * }
 */
final class ShiftExchangeApprovalAlias {
}
