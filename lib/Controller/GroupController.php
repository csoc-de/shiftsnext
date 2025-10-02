<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Controller;

use OCA\ShiftsNext\Service\GroupService;
use OCA\ShiftsNext\Service\GroupShiftAdminRelationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Throwable;

final class GroupController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private GroupService $groupService,
		private GroupShiftAdminRelationService $groupShiftAdminRelationService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @param bool $restricted
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'GET', url: '/api/groups')]
	public function index(bool $restricted = false): DataResponse {
		try {
			$groupIds = $restricted
				? $this->groupShiftAdminRelationService->getShiftAdminGroupIds()
				: null;
			$groups = $this->groupService->getAllSerializable($groupIds);
			return new DataResponse($groups);
		} catch (Throwable $th) {
			return new DataResponse(['error' => $th->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}
}
