import { getCanonicalLocale } from '@nextcloud/l10n'
import { Temporal } from 'temporal-polyfill'

export const locale = getCanonicalLocale()

export const userTimeZone = Temporal.Now.timeZoneId()

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

export type DateLike = Date | Temporal.ZonedDateTime | Temporal.PlainDate

export type DateLikeRange = [DateLike, DateLike]

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
 * Returns the ISO week date for `date`
 *
 * @example
 * ```JavaScript
 * const date = new Date("2024-10-07T12:00:00.000Z")
 * getIsoWeekDate(date, true)
 * // returns "2024-W41-1", because Monday October 07 2024 is the first day of week 41 in 2024
 * ```
 *
 * @param date Defaults to the current date
 * @param withDay If false, the returned string does not include the weekday
 */
export function getIsoWeekDate<T extends boolean>(
	date: DateLike = new Date(),
	withDay: T,
): T extends true ? IsoWeekDateWithDay : IsoWeekDateWithoutDay {
	if ('toISOString' in date) {
		date = Temporal.Instant.fromEpochMilliseconds(date.valueOf()).toZonedDateTimeISO(userTimeZone)
	}
	return buildIsoWeekDate(
		date.yearOfWeek!,
		date.weekOfYear!,
		withDay ? date.dayOfWeek : undefined,
	)
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
 * Formats `date` using the specified `options`
 *
 * @param date The date to format
 * @param options The options to use when formatting `date`
 */
export function formatDate(
	date: DateLike,
	options: Intl.DateTimeFormatOptions = {},
): string {
	if ('toZonedDateTime' in date) {
		date = date.toZonedDateTime(userTimeZone)
	}
	if ('calendarId' in date) {
		date = new Date(date.epochMilliseconds)
	}
	return getDateTimeFormatter(options).format(date)
}

/**
 * Formats `dates` as range using the specified `options`
 *
 * @param dates The dates to format as range
 * @param options The options to use when formatting `dates`
 */
export function formatRange(
	dates: DateLikeRange,
	options: Intl.DateTimeFormatOptions = {},
) {
	let [start, end] = dates
	if ('toZonedDateTime' in start) {
		start = start.toZonedDateTime(userTimeZone)
	}
	if ('calendarId' in start) {
		start = new Date(start.epochMilliseconds)
	}
	if ('toZonedDateTime' in end) {
		end = end.toZonedDateTime(userTimeZone)
	}
	if ('calendarId' in end) {
		end = new Date(end.epochMilliseconds)
	}
	return getDateTimeFormatter(options).formatRange(start, end)
}

/**
 * Returns a {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/DateTimeFormat | Intl.DateTimeFormat}
 * instance with the specified `options`
 *
 * @param options The options to use when creating the formatter
 */
export function getDateTimeFormatter(options: Intl.DateTimeFormatOptions): Intl.DateTimeFormat {
	options.timeZone ??= userTimeZone
	return new Intl.DateTimeFormat(locale, options)
}

/**
 * Returns the ISO calendar date for `date`
 *
 * The time zone of the returned string depends on the following:
 *
 * - If `date` is an instance of `Date`, the runtime's time zone will be used
 * - If `date` is an instance of `ZonedDateTime`, `date`'s time zone will be used
 *
 * @param date Defaults to the current date
 */
export function getIsoCalendarDate(date: Date | Temporal.ZonedDateTime = new Date()): IsoCalendarDate {
	if ('toISOString' in date) {
		date = Temporal.Instant.fromEpochMilliseconds(date.valueOf()).toZonedDateTimeISO(userTimeZone)
	}
	const year = String(date.year).padStart(4, '0')
	const month = String(date.month).padStart(2, '0')
	const day = String(date.day).padStart(2, '0')
	return `${year}-${month}-${day}`
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

/**
 * Formats a {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Temporal/Duration | Temporal.Duration} using
 * {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/DurationFormat | Intl.DurationFormat}
 *
 * If Intl.DurationFormat is not supported, the plain
 * {@link https://en.wikipedia.org/wiki/ISO_8601#Durations | ISO 8061 Duration}
 * string is returned
 *
 * @param duration The duration to format
 * @param style The formatting style
 */
export function formatDuration(
	duration: Temporal.DurationLike,
	style?: 'long' | 'short' | 'narrow' | 'digital',
) {
	try {
		// @ts-expect-error part of ES2025
		return new Intl.DurationFormat(locale, { style }).format(duration)
	} catch {
		return duration.toString()
	}
}
