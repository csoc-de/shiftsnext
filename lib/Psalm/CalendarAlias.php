<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Psalm;

use DateTimeImmutable;

/**
 * @psalm-type Calendar = array{
 *     id: int,
 *     uri: string,
 *     principaluri: string,
 *     '{DAV:}displayname': string,
 *     '{http://nextcloud.com/ns}owner-displayname': string,
 * }
 *
 * @psalm-type CalendarObject = array{
 *     id: int,
 *     uri: string,
 *     uid: string,
 *     lastmodified: int,
 *     etag: string,
 *     calendarid: int,
 *     size: int,
 *     calendardata: string,
 *     component: string,
 *     classification: int,
 *     '{http://nextcloud.com/ns}deleted-at': null|string,
 * }
 *
 * @psalm-type SanitizedCalendar = array{
 *     id: int,
 *     uri: string,
 *     principalUri: string,
 *     displayName: string,
 *     ownerDisplayName: string,
 * }
 *
 * @psalm-type SearchResultObject = array{
 *     CREATED: list{DateTimeImmutable, array},
 *     DTSTAMP: list{DateTimeImmutable, array},
 *     'LAST-MODIFIED': list{DateTimeImmutable, array},
 *     SEQUENCE: list{int, array},
 *     UID: list{string, array},
 *     DTSTART: list{DateTimeImmutable, array},
 *     DTEND: list{DateTimeImmutable, array},
 *     STATUS: list{string, array},
 *     SUMMARY: list{string, array},
 * }
 *
 * @psalm-type SearchResult = array{
 *     id: int,
 *     type: string,
 *     uid: string,
 *     uri: string,
 *     objects: SearchResultObject[],
 *     timezones: array,
 * }
 */
final class CalendarAlias {
}
