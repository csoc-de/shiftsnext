<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Util;

use DateTimeImmutable;
use DateTimeZone;
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
	 * the returned value will be a {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date#date_time_string_format Date Time String}
	 * with the `Z` timezone suffix.
	 * 2. If `$value` is a {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Temporal/ZonedDateTime Temporal.ZonedDateTime}
	 * compatible string (without calendar), the returned value will be a {@link https://www.rfc-editor.org/rfc/rfc9557.html RFC 9557}
	 * string (without calendar), with `+00:00[UTC]` as its timezone offset + name.
	 * 3. If `$value` is a {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Temporal/PlainDate Temporal.PlainDate}
	 * compatible string, it is simply returned as is.
	 *
	 * @param string $value
	 * @param DateTimeImmutable &$dateTime If specified, this variable will
	 *                                     be filled with the parsed
	 *                                     DateTimeImmutable object in UTC
	 *
	 * @return string
	 *
	 * @throws EcmaMalformedStringException {@see OCA\ShiftsNext\Util\Util::parseEcma()}
	 *
	 * @psalm-assert !null $dateTime
	 */
	public static function unlocalizeEcma(
		string $value,
		?DateTimeImmutable &$dateTime = null,
	): string {
		$dateTime = null;
		$dateTime = self::parseEcma($value, true, $type)
			->setTimezone(new DateTimeZone('UTC'));
		/** @var key-of<self::DATE_ECMA_FORMAT_TO_TYPE_MAP> */
		$format = array_search($type, self::DATE_ECMA_FORMAT_TO_TYPE_MAP);
		/**
		 * @disregard P1006 Intelephense fails to infer
		 * key-of<self::DATE_ECMA_FORMAT_TO_TYPE_MAP>, maybe due to
		 * self::DATE_ECMA_FORMAT_TO_TYPE_MAP referencing other class constants
		 */
		return $dateTime->format($format);
	}

	/**
	 * Parses a {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date#date_time_string_format Date Time String},
	 * a {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Temporal/ZonedDateTime Temporal.ZonedDateTime} compatible string,
	 * or a {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Temporal/PlainDate Temporal.PlainDate} compatible string
	 * into a DateTimeImmutable object
	 *
	 * @param string $value
	 * @param bool $toUtc The returned DateTimeImmutable will always be in UTC if `$value` is a PlainDate.
	 *                    If `$value` is not a PlainDate and `$toUtc` is set to `false`,
	 *                    the returned DateTimeImmutable will be in whatever time zone is present in `$value`.
	 *                    Defaults to `true`.
	 *
	 * @param EcmaType &$type If specified, this variable will be filled with
	 *                        the {@see OCA\ShiftsNext\Util\Util::EcmaType}
	 *
	 * @return DateTimeImmutable
	 *
	 * @throws EcmaMalformedStringException if `$value` is not a valid Temporal
	 *                                      string
	 *
	 * @psalm-assert !null $type
	 */
	public static function parseEcma(
		string $value,
		bool $toUtc = true,
		?string &$type = null,
	): DateTimeImmutable {
		foreach (self::DATE_ECMA_FORMAT_TO_TYPE_MAP as $format => $type) {
			$dateTime = DateTimeImmutable::createFromFormat($format, $value);
			if (!$dateTime) {
				continue;
			}
			if ($format === DateTimeInterface::PLAIN_DATE) {
				$toUtc = true;
			}
			return $toUtc
				? $dateTime->setTimezone(new DateTimeZone('UTC'))
				: $dateTime;
		}
		$type = null;
		throw new EcmaMalformedStringException(
			"Format of value `'$value'` is not supported"
		);
	}
}
