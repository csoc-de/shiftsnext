<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Controller;

use OCA\ShiftsNext\Exception\HttpException;
use OCA\ShiftsNext\Service\GroupUserRelationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Throwable;

final class GroupUserRelationController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private GroupUserRelationService $groupUserRelationService,
	) {
		parent::__construct($appName, $request);
	}

	#[FrontpageRoute(verb: 'GET', url: '/api/group-user-relations')]
	public function index(): DataResponse {
		try {
			$relationsExtended = $this->groupUserRelationService->getAllExtended();
			return new DataResponse($relationsExtended);
		} catch (Throwable $th) {
			return new DataResponse(['error' => $th->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	#[FrontpageRoute(verb: 'GET', url: '/api/group-user-relations/grouped-by-group')]
	public function indexGroupedByGroup(): DataResponse {
		try {
			$groupedRelations = $this->groupUserRelationService->getAllGroupedByGroup();
			return new DataResponse($groupedRelations);
		} catch (Throwable $th) {
			$responseCode = $th instanceof HttpException ? $th->getStatusCode() : Http::STATUS_INTERNAL_SERVER_ERROR;
			return new DataResponse(['error' => $th->getMessage()], $responseCode);
		}
	}
}
