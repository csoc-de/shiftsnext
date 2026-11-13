import { loadState } from '@nextcloud/initial-state'
import { getCanonicalLocale } from '@nextcloud/l10n'
import { Intl as IntlExtended, Temporal } from 'temporal-polyfill'

/** The locale set in the personal settings of the logged-in user */
export const userLocale = getCanonicalLocale()

/**
 * Returns the current system time zone by calling {@link Temporal.Now.timeZoneId}
 */
export function getSystemTimeZone() {
	return Temporal.Now.timeZoneId()
}

/** The time zone set in the personal settings of the logged-in user */
export const userTimeZone = loadState<{ core?: { user?: { timezone?: string } } }>(
	'core',
	'capabilities',
	{},
).core?.user?.timezone ?? getSystemTimeZone()

/**
 * ISO calendar date, e.g. `"2024-01-01"`
 *
 * @see {@link https://en.wikipedia.org/wiki/ISO_8601#Calendar_dates}
 */
export type IsoCalendarDate = `${string}-${string}-${string}`

/**
 * ISO week date without day, e.g. `"2024-W01"`
 *
 * @see {@link https://en.wikipedia.org/wiki/ISO_week_date}
 */
export type IsoWeekDateWithoutDay = `${string}-W${string}`

/**
 * ISO week date with weekday, e.g. `"2024-W01-1"`
 *
 * @see {@link https://en.wikipedia.org/wiki/ISO_week_date}
 */
export type IsoWeekDateWithDay = `${string}-W${string}-${number}`

/**
 * ISO week date with or without weekday, e.g. `"2024-W01-1"` or `"2024-W01"`
 *
 * @see {@link https://en.wikipedia.org/wiki/ISO_week_date}
 */
export type IsoWeekDate = IsoWeekDateWithoutDay | IsoWeekDateWithDay

export type TemporalPlainDateCompatibleObject = Temporal.ZonedDateTime | Temporal.PlainDateTime | Temporal.PlainDate

export type FormatTemporalObjectValue = Intl.FormattableTemporalObject | Temporal.ZonedDateTime

export type NumberOfWeeks = 52 | 53

/**
 * Gets the number of weeks in `year`, using the ISO 8601 calendar
 *
 * @param year The year to get the number of weeks for
 */
export function getNumberOfWeeks(year: number): NumberOfWeeks {
	return Temporal.ZonedDateTime.from({
		timeZone: userTimeZone,
		year,
		month: 12,
		day: 28,
	}).weekOfYear as NumberOfWeeks
}

/**
 * Returns a new instance of `ZonedDateTime` with the day part moved to the
 * specified `dayOfWeek` within the same week. Every other component of `base`
 * will keep its original value.
 *
 * @param dayOfWeek The target day of week (1 = monday, 7 = sunday)
 * @param base The instance used as the base for the operation. Defaults to now.
 */
export function getZonedDateTimeForDayOfWeek(
	dayOfWeek: number,
	base: Temporal.ZonedDateTime = Temporal.Now.zonedDateTimeISO(userTimeZone),
): Temporal.ZonedDateTime {
	const referenceDayOfWeek = base.dayOfWeek
	const distanceOfDays = dayOfWeek - referenceDayOfWeek
	const duration = Temporal.Duration.from({ days: distanceOfDays })
	return base.add(duration)
}

/**
 * Returns a {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/DateTimeFormat | Intl.DateTimeFormat}
 * instance with the specified `options` using {@link userLocale}
 *
 * @param options The options to use when creating the formatter. If no time zone
 * is specified, the {@link userTimeZone} is used.
 */
export function getDateTimeFormatter(options: Intl.DateTimeFormatOptions): IntlExtended.DateTimeFormat {
	options.timeZone ??= userTimeZone
	return new IntlExtended.DateTimeFormat(userLocale, options)
}

/**
 * Formats `value` using {@link userLocale} and the specified `options`
 *
 * @param value The value to format
 * @param options The options to use when formatting `value`. If specified, the
 * {@link Intl.DateTimeFormatOptions.timeZone | timeZone} option takes
 * precendence over the {@link Temporal.ZonedDateTime.timeZoneId | timeZoneId}
 * of `value` in case `value` is a {@link Temporal.ZonedDateTime} instance
 */
export function formatTemporalObject(
	value: FormatTemporalObjectValue,
	options: Intl.DateTimeFormatOptions = {},
): string {
	if ('timeZoneId' in value) {
		options.timeZone ??= value.timeZoneId
		value = value.toInstant()
	}
	return getDateTimeFormatter(options).format(value)
}

/**
 * Formats `start` and `end` as range using the specified `options`
 *
 * @param start The start date
 * @param end The end date
 * @param options The options to use when formatting the range. If specified, the
 * {@link Intl.DateTimeFormatOptions.timeZone | timeZone} option takes
 * precendence over the {@link Temporal.ZonedDateTime.timeZoneId | timeZoneId}
 * of `start` in case `start` is a {@link Temporal.ZonedDateTime} instance
 */
export function formatTemporalObjectRange(
	start: Temporal.PlainDate,
	end: Temporal.PlainDate,
	options?: Intl.DateTimeFormatOptions,
): string
export function formatTemporalObjectRange(
	start: Temporal.PlainYearMonth,
	end: Temporal.PlainYearMonth,
	options?: Intl.DateTimeFormatOptions,
): string
export function formatTemporalObjectRange(
	start: Temporal.PlainMonthDay,
	end: Temporal.PlainMonthDay,
	options?: Intl.DateTimeFormatOptions,
): string
export function formatTemporalObjectRange(
	start: Temporal.PlainTime,
	end: Temporal.PlainTime,
	options?: Intl.DateTimeFormatOptions,
): string
export function formatTemporalObjectRange(
	start: Temporal.PlainDateTime,
	end: Temporal.PlainDateTime,
	options?: Intl.DateTimeFormatOptions,
): string
export function formatTemporalObjectRange(
	start: Temporal.Instant,
	end: Temporal.Instant,
	options?: Intl.DateTimeFormatOptions,
): string
export function formatTemporalObjectRange(
	start: Temporal.ZonedDateTime,
	end: Temporal.ZonedDateTime,
	options?: Intl.DateTimeFormatOptions,
): string
export function formatTemporalObjectRange(
	start: FormatTemporalObjectValue,
	end: FormatTemporalObjectValue,
	options: Intl.DateTimeFormatOptions = {},
): string {
	if ('timeZoneId' in start) {
		options.timeZone ??= start.timeZoneId
		start = start.toInstant()
	}
	if ('timeZoneId' in end) {
		end = end.toInstant()
	}
	return getDateTimeFormatter(options).formatRange(start, end)
}

/**
 * Formats a {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Temporal/Duration | Temporal.Duration} using
 * {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/DurationFormat | Intl.DurationFormat}
 * and {@link userLocale}
 *
 * If Intl.DurationFormat is not supported, the plain
 * {@link https://en.wikipedia.org/wiki/ISO_8601#Durations | ISO 8061 Duration}
 * string is returned
 *
 * @param duration The duration to format
 * @param style The formatting style
 */
export function formatDuration(
	duration: Temporal.DurationLikeObject,
	style?: Intl.DurationFormatStyle,
) {
	try {
		return new Intl.DurationFormat(userLocale, { style }).format(duration)
	} catch {
		return duration.toString()
	}
}

/**
 * Returns the ISO calendar date for `value`
 *
 * @param value Defaults to now
 */
export function getIsoCalendarDate(value: TemporalPlainDateCompatibleObject = Temporal.Now.zonedDateTimeISO(userTimeZone)): IsoCalendarDate {
	if ('toPlainDate' in value) {
		value = value.toPlainDate()
	}
	return value.toString() as IsoCalendarDate
}

/**
 * Builds an ISO week date
 *
 * @param year The ISO week-numbering year
 * @param week The ISO week number
 * @param day The weekday number (1 = Monday, 7 = Sunday)
 */
export function buildIsoWeekDate<D extends number | undefined = undefined>(
	year: number,
	week: number,
	day?: D,
): D extends number ? IsoWeekDateWithDay : IsoWeekDateWithoutDay {
	const paddedYear = String(year).padStart(4, '0')
	const paddedWeek = String(week).padStart(2, '0')
	let isoWeekDate: IsoWeekDate = `${paddedYear}-W${paddedWeek}`
	if (day !== undefined) {
		isoWeekDate = `${isoWeekDate}-${day}`
	}
	return isoWeekDate as D extends number
		? IsoWeekDateWithDay
		: IsoWeekDateWithoutDay
}

/**
 * Returns the ISO week date for `value`
 *
 * @param value Defaults to now
 * @param withDay If false, the returned string does not include the weekday
 */
export function getIsoWeekDate<T extends boolean>(
	value: TemporalPlainDateCompatibleObject = Temporal.Now.zonedDateTimeISO(userTimeZone),
	withDay: T,
): T extends true ? IsoWeekDateWithDay : IsoWeekDateWithoutDay {
	return buildIsoWeekDate(
		value.yearOfWeek!,
		value.weekOfYear!,
		withDay ? value.dayOfWeek : undefined,
	)
}

/**
 * Parses `isoWeekDate` into a `ZonedDateTime`
 *
 * @param isoWeekDate The ISO week date to parse
 */
export function parseIsoWeekDate(isoWeekDate: IsoWeekDateWithDay): Temporal.ZonedDateTime {
	const error = new Error(`The value "${isoWeekDate}" is not a valid ISO week date`)
	const match = isoWeekDate.match(/^(\d{4})-W(\d{2})-(\d)$/)
	if (!match) {
		throw error
	}

	const [, year = 0, week = 0, day = 0] = match.map(Number)
	if (
		week < 1
		|| week > 53
		|| (week === 53 && getNumberOfWeeks(year) === 52)
		|| day < 1
		|| day > 7
	) {
		throw error
	}

	/** January 4th is always in the first ISO week */
	const january4th = Temporal.ZonedDateTime.from({
		year,
		month: 1,
		day: 4,
		timeZone: userTimeZone,
	})

	const firstDayOfWeek = january4th.subtract({
		days: january4th.dayOfWeek - 1,
	})

	return firstDayOfWeek.add({ weeks: week - 1, days: day - 1 })
}

/**
 * Revives key value pairs
 *
 * @param key The key
 * @param value The value
 */
export function reviver(key: string, value: unknown): unknown {
	if (typeof value === 'string') {
		if (['start', 'end'].includes(key)) {
			try {
				return Temporal.Instant.from(value).toZonedDateTimeISO(userTimeZone)
			} catch {
				// Failing is expected
			}
			try {
				return Temporal.PlainDate.from(value)
			} catch {
				// Failing is expected
			}
		}
		if (key === 'reference') {
			try {
				return Temporal.ZonedDateTime.from(value)
			} catch {
				// Failing is expected
			}
		}
		if (key === 'duration') {
			return Temporal.Duration.from(value)
		}
	}
	return value
}
