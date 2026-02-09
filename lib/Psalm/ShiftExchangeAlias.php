<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Psalm;

/**
 * @psalm-import-type SerializableUserSerialized from UserAlias
 * @psalm-import-type ShiftExtendedSerialized from ShiftAlias
 * @psalm-import-type ShiftExchangeApprovalExtendedSerialized from ShiftExchangeApprovalAlias
 *
 * @psalm-type ShiftExchangeExtendedSerializedBase = array{
 *     id: int,
 *     shift_a: ShiftExtendedSerialized,
 *     comment: string,
 *     done: bool,
 *     approved: null|bool,
 *     user_a_approval: ShiftExchangeApprovalExtendedSerialized,
 *     user_b_approval: ShiftExchangeApprovalExtendedSerialized,
 *     admin_approval: ShiftExchangeApprovalExtendedSerialized,
 * }
 *
 * @psalm-type RegularShiftExchangeExtendedSerialized = ShiftExchangeExtendedSerializedBase & array{
 *     shift_b: ShiftExtendedSerialized,
 * }
 *
 * @psalm-type TransferShiftExchangeExtendedSerialized = ShiftExchangeExtendedSerializedBase & array{
 *     transfer_to_user: SerializableUserSerialized,
 * }
 *
 * @psalm-type ShiftExchangeExtendedSerialized = RegularShiftExchangeExtendedSerialized | TransferShiftExchangeExtendedSerialized
 */
final class ShiftExchangeAlias {
}
