<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Controller;

use DateTimeImmutable;
use DateTimeZone;
use OCA\ShiftsNext\Exception\HttpException;
use OCA\ShiftsNext\Response\ErrorResponse;
use OCA\ShiftsNext\Service\CalendarService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IL10N;
use OCP\IRequest;
use Throwable;
use function preg_match;

final class CalendarController extends ApiController {
	public function __construct(
		private IL10N $l,
		string $appName,
		IRequest $request,
		private CalendarService $calendarService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Returns the absence calendar events blocking the users in `$user_ids`
	 * during the ISO week identified by `$week_date`
	 *
	 * @param string $week_date An ISO week date without day, e.g. `2026-W23`
	 * @param list<string> $user_ids The users to return blockers for
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'GET', url: '/api/calendars/absence-blockers')]
	public function absenceBlockers(
		string $week_date,
		array $user_ids,
	): JSONResponse {
		try {
			$matches = [];
			if (!preg_match('/^(\d{4})-W(\d{2})$/', $week_date, $matches)) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					'Filter week_date is not an ISO week date',
					null,
					$this->l->t('The requested week is invalid.'),
				);
			}
			$year = (int)($matches[1] ?? 0);
			$week = (int)($matches[2] ?? 0);
			if ($year < 1970 || $week < 1 || $week > 53) {
				throw new HttpException(
					Http::STATUS_UNPROCESSABLE_ENTITY,
					'Filter week_date is out of range',
					null,
					$this->l->t('The requested week is invalid.'),
				);
			}
			$weekStart = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
				->setISODate($year, $week, 1)
				->setTime(0, 0, 0);
			$weekEnd = $weekStart->modify('+7 day');
			$blockers = $this->calendarService->getAbsenceBlockers(
				$weekStart,
				$weekEnd,
				$user_ids,
			);
			return new JSONResponse($blockers);
		} catch (Throwable $th) {
			return new ErrorResponse($th);
		}
	}
}
