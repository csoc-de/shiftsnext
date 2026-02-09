<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Psalm;

/**
 * @psalm-import-type SerializableUserSerialized from UserAlias
 * @psalm-import-type ShiftTypeExtendedSerialized from ShiftTypeAlias
 *
 * @psalm-type TemporalColumnQueryOptions = array{
 *     calendarDates: list{0: string, 1?: string},
 *     timeZone: non-empty-string,
 * }
 *
 * @psalm-type ShiftExtendedSerialized = array{
 *     id: int,
 *     user: SerializableUserSerialized,
 *     shift_type: ShiftTypeExtendedSerialized,
 *     start: string,
 *     end: string,
 * }
 */
final class ShiftAlias {
}
