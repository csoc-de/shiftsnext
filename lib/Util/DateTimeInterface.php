<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Util;

interface DateTimeInterface extends \DateTimeInterface {
	/**
	 * {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date#date_time_string_format ECMAScript Date Time String}
	 * (example: 1970-01-01T00:00:00.000Z)
	 */
	final public const string ECMA_DATE_TIME = 'Y-m-d\TH:i:s.vp';

	/**
	 * {@link https://www.rfc-editor.org/rfc/rfc9557.html RFC 9557}
	 * without calendar (example: 1970-01-01T00:00:00.000+00:00[UTC])
	 */
	final public const string RFC9557_NC = 'Y-m-d\TH:i:s.vP\[e\]';

	/**
	 * Same as {@see OCA\ShiftsNext\Util\DateTimeInterface::RFC9557_NC} but
	 * without fractional second
	 */
	final public const string RFC9557_NC_NF = 'Y-m-d\TH:i:sP\[e\]';

	/**
	 * A plain date (example: 1970-01-01)
	 */

	final public const string PLAIN_DATE = 'Y-m-d';
	/**
	 * iCal plain date (example: 19700101)
	 */
	final public const string I_CAL_PLAIN_DATE = 'Ymd';
}
