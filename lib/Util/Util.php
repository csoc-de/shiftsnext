<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Util;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use OCA\ShiftsNext\Exception\EcmaMalformedStringException;

use function array_search;

/**
 * Needs to be redeclared, because Psalm cannot infer the type of a variable
 * which is passed by reference into a function when that function accepts a by
 * reference parameter and this parameter uses an imported Psalm type alias
 * @psalm-type EcmaType = 'Date'|'ZonedDateTime'|'PlainDate'
 */
final class Util {
	/** It's important to keep the order of elements inside this array */
	final public const array DATE_ECMA_FORMAT_TO_TYPE_MAP = [
		DateTimeInterface::ECMA_DATE_TIME => 'Date',
		DateTimeInterface::RFC9557_NC_NF => 'ZonedDateTime',
		DateTimeInterface::RFC9557_NC => 'ZonedDateTime',
		DateTimeInterface::PLAIN_DATE => 'PlainDate',
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
		[$dateTime, $type] = self::parseEcma($value, true);
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
	 * into a DateTimeImmutable object
	 *
	 * @param string $value
	 * @param bool $toUtc The DateTimeImmutable in the returned tuple will always be in UTC if `$value` is a PlainDate.
	 *                    If `$value` is not a PlainDate and `$toUtc` is set to `false`,
	 *                    the DateTimeImmutable will be in whatever time zone is present in `$value`.
	 *                    Defaults to `true`.
	 *
	 * @return list{DateTimeImmutable,EcmaType} A tuple containing the resulting DateTimeImmutable and
	 *                                          the detected {@see OCA\ShiftsNext\Util\Util::EcmaType} of `$value`
	 *
	 * @throws EcmaMalformedStringException if `$value` is not a valid Temporal
	 *                                      string
	 */
	public static function parseEcma(string $value, bool $toUtc = true): array {
		foreach (self::DATE_ECMA_FORMAT_TO_TYPE_MAP as $format => $type) {
			$dateTime = DateTimeImmutable::createFromFormat($format, $value);
			if (!$dateTime) {
				continue;
			}
			if ($format === DateTimeInterface::PLAIN_DATE) {
				$toUtc = true;
			}
			if ($toUtc) {
				$dateTime = $dateTime->setTimezone(new DateTimeZone('UTC'));
			}
			return [$dateTime, $type];
		}
		throw new EcmaMalformedStringException(
			"Format of value `'$value'` is not supported"
		);
	}
}
