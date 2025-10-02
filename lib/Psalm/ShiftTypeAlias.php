<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Psalm;

/**
 * @psalm-type RepetitionFrequency = 'weekly'
 *
 * @psalm-type RepetitionBase = array{
 *     frequency: RepetitionFrequency,
 *     interval: int,
 * }
 *
 * @psalm-type RepetitionWeeklyType = 'by_day' | 'by_week'
 *
 * @psalm-type ShortDayToAmountMap = array{
 *     MO: int,
 *     TU: int,
 *     WE: int,
 *     TH: int,
 *     FR: int,
 *     SA: int,
 *     SU: int,
 * }
 *
 * @psalm-type RepetitionWeeklyByDayConfig = array{
 *     reference: string,
 *     short_day_to_amount_map: ShortDayToAmountMap,
 *     duration: string,
 * }
 *
 * @psalm-type RepetitionWeeklyByWeekConfig = array{
 *     reference: string,
 *     amount: int,
 * }
 *
 * @psalm-type RepetitionWeeklyConfig = RepetitionWeeklyByDayConfig | RepetitionWeeklyByWeekConfig
 *
 * @psalm-type RepetitionWeeklyBase = RepetitionBase & array{
 *     frequency: 'weekly',
 *     weekly_type: RepetitionWeeklyType,
 *     config: RepetitionWeeklyConfig,
 * }
 *
 * @psalm-type RepetitionWeeklyByDay = RepetitionWeeklyBase & array{
 *     weekly_type: 'by_day',
 *     config: RepetitionWeeklyByDayConfig,
 * }
 *
 * @psalm-type RepetitionWeeklyByWeek = RepetitionWeeklyBase & array{
 *     weekly_type: 'by_week',
 *     config: RepetitionWeeklyByWeekConfig,
 * }
 *
 * @psalm-type RepetitionWeekly = RepetitionWeeklyByDay | RepetitionWeeklyByWeek
 *
 * @psalm-type Repetition = RepetitionWeekly
 *
 * @psalm-type Caldav = array{
 *     categories: string,
 * }
 */
final class ShiftTypeAlias {
}
