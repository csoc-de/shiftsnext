<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Controller;

use OCA\ShiftsNext\Response\ErrorResponse;
use OCA\ShiftsNext\Service\GroupUserRelationService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use Throwable;

final class GroupUserRelationController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private GroupUserRelationService $groupUserRelationService,
	) {
		parent::__construct($appName, $request);
	}

	#[FrontpageRoute(verb: 'GET', url: '/api/group-user-relations')]
	public function index(): JSONResponse {
		try {
			$relationsExtended = $this->groupUserRelationService->getAllExtended();
			return new JSONResponse($relationsExtended);
		} catch (Throwable $th) {
			return new ErrorResponse($th);
		}
	}

	#[FrontpageRoute(verb: 'GET', url: '/api/group-user-relations/grouped-by-group')]
	public function indexGroupedByGroup(): JSONResponse {
		try {
			$groupedRelations = $this->groupUserRelationService->getAllGroupedByGroup();
			return new JSONResponse($groupedRelations);
		} catch (Throwable $th) {
			return new ErrorResponse($th);
		}
	}
}
