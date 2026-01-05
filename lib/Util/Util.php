<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Util;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use OCA\ShiftsNext\Exception\EcmaMalformedStringException;
use OCA\ShiftsNext\Psalm\EcmaAlias;

use function array_search;

/**
 * @psalm-import-type EcmaType from EcmaAlias
 */
final class Util {
	/**
	 * {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date#date_time_string_format ECMAScript Date Time String}
	 * (example: 1970-01-01T00:00:00.000Z)
	 */
	public const string ECMA_DATE_TIME = 'Y-m-d\TH:i:s.vp';

	/**
	 * {@link https://www.rfc-editor.org/rfc/rfc9557.html RFC 9557}
	 * without calendar (example: 1970-01-01T00:00:00.000+00:00[UTC])
	 */
	public const string RFC9557_NC = 'Y-m-d\TH:i:s.vP\[e\]';

	/**
	 * Same as {@see OCA\ShiftsNext\Util\DateTimeInterface::RFC9557_NC} but
	 * without fractional second
	 */
	public const string RFC9557_NC_NF = 'Y-m-d\TH:i:sP\[e\]';

	/**
	 * A plain date (example: 1970-01-01)
	 */
	public const string PLAIN_DATE = 'Y-m-d';

	/**
	 * iCal plain date (example: 19700101)
	 */
	public const string I_CAL_PLAIN_DATE = 'Ymd';

	/** It's important to keep the order of elements inside this array */
	final public const array DATE_ECMA_FORMAT_TO_TYPE_MAP = [
		self::ECMA_DATE_TIME => 'Date',
		self::RFC9557_NC_NF => 'ZonedDateTime',
		self::RFC9557_NC => 'ZonedDateTime',
		self::PLAIN_DATE => 'PlainDate',
	];

	/**
	 * Removes any localized information from `$value` by converting it to UTC
	 *
	 * This method tries to unlocalize `$value` in the following order:
	 *
	 * 1. If `$value` is a full {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date#date_time_string_format Date Time String},
	 * the returned value will contain a {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date#date_time_string_format Date Time String}
	 * with the `Z` timezone suffix.
	 * 2. If `$value` is a {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Temporal/ZonedDateTime Temporal.ZonedDateTime}
	 * compatible string (without calendar), the returned value will contain a {@link https://www.rfc-editor.org/rfc/rfc9557.html RFC 9557}
	 * string (without calendar), with `+00:00[UTC]` as its timezone offset + name.
	 * 3. If `$value` is a {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Temporal/PlainDate Temporal.PlainDate}
	 * compatible string, the returned value will contain the original string as is.
	 *
	 * @param string $value
	 *
	 * @return list{string,DateTimeImmutable} A tuple containing the unlocalized string and
	 *                                        the corresponding DateTimeImmutable object in UTC
	 *
	 * @throws EcmaMalformedStringException {@see OCA\ShiftsNext\Util\Util::parseEcma()}
	 */
	public static function unlocalizeEcma(string $value): array {
		[$dateTime, $type] = self::parseEcma($value, 'UTC');
		$unlocalizedDateTime = $dateTime->setTimezone(new DateTimeZone('UTC'));
		$format = array_search($type, self::DATE_ECMA_FORMAT_TO_TYPE_MAP);
		// This is to satisfy psalm
		if (!$format) {
			throw new Exception(
				"Failed to find type `'$type'` in DATE_ECMA_FORMAT_TO_TYPE_MAP"
			);
		}
		return [$unlocalizedDateTime->format($format), $unlocalizedDateTime];
	}

	/**
	 * Parses a {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date#date_time_string_format Date Time String},
	 * a {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Temporal/ZonedDateTime Temporal.ZonedDateTime} compatible string,
	 * or a {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Temporal/PlainDate Temporal.PlainDate} compatible string
	 * into a DateTimeImmutable object.
	 *
	 * Resets all fields not present in `$value` to zero-like values.
	 *
	 * @param string $value
	 * @param non-empty-string $timeZone The time zone used for parsing. Only relevant if
	 *                                   `$value` is a PlainDate.
	 *
	 * @return list{DateTimeImmutable,EcmaType} A tuple containing the resulting DateTimeImmutable and
	 *                                          the detected {@see OCA\ShiftsNext\Util\Util::EcmaType} of `$value`
	 *
	 * @throws EcmaMalformedStringException if `$value` is not a valid ECMA string
	 */
	public static function parseEcma(string $value, string $timeZone = 'UTC'): array {
		$dateTimeZone = new DateTimeZone($timeZone);
		foreach (self::DATE_ECMA_FORMAT_TO_TYPE_MAP as $format => $type) {
			$dateTime = DateTimeImmutable::createFromFormat(
				"$format|",
				$value,
				$dateTimeZone
			);
			if (!$dateTime) {
				continue;
			}
			return [$dateTime, $type];
		}
		throw new EcmaMalformedStringException(
			"Format of value `'$value'` is not supported"
		);
	}
}
