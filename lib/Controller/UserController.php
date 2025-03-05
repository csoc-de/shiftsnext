<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Controller;

use OCA\ShiftsNext\Service\GroupShiftAdminRelationService;
use OCA\ShiftsNext\Service\UserService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Throwable;

use function array_intersect;

class UserController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private UserService $userService,
		private GroupShiftAdminRelationService $groupShiftAdminRelationService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @param null|string[] $group_ids
	 * @param bool $restricted
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'GET', url: '/api/users')]
	public function index(
		?array $group_ids = null,
		bool $restricted = false,
	): DataResponse {
		try {
			if ($restricted) {
				$shiftAdminGroupIds = $this->groupShiftAdminRelationService->getShiftAdminGroupIds();
				if ($group_ids !== null) {
					$group_ids = array_intersect($shiftAdminGroupIds, $group_ids);
				} else {
					$group_ids = $shiftAdminGroupIds;
				}
			}
			$users = $this->userService->getAllSerializable(groupIds: $group_ids);
			return new DataResponse($users);
		} catch (Throwable $th) {
			return new DataResponse(['error' => $th->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}
}
