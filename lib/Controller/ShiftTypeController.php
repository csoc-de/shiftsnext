<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Controller;

use OCA\ShiftsNext\Db\ShiftTypeMapper;
use OCA\ShiftsNext\Exception\HttpException;
use OCA\ShiftsNext\Exception\ShiftTypeNotFoundException;
use OCA\ShiftsNext\Psalm\ShiftTypeAlias;
use OCA\ShiftsNext\Service\GroupService;
use OCA\ShiftsNext\Service\GroupShiftAdminRelationService;
use OCA\ShiftsNext\Service\ShiftTypeService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Throwable;

use function array_intersect;

/**
 * @psalm-import-type Repetition from ShiftTypeAlias
 * @psalm-import-type Caldav from ShiftTypeAlias
 */
class ShiftTypeController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private ShiftTypeMapper $shiftTypeMapper,
		private ShiftTypeService $shiftTypeService,
		private GroupShiftAdminRelationService $groupShiftAdminRelationService,
		private GroupService $groupService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @param null|string[] $group_ids
	 * @param bool $restricted
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'GET', url: '/api/shift-types')]
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
			$shiftTypesExtended = $this->shiftTypeService->getAllExtended(
				$group_ids,
			);
			return new DataResponse($shiftTypesExtended);
		} catch (Throwable $th) {
			return new DataResponse(['error' => $th->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'GET', url: '/api/shift-types/{id}')]
	public function show(int $id): DataResponse {
		try {
			try {
				$shiftTypeExtended = $this->shiftTypeService->getExtended($id);
			} catch (ShiftTypeNotFoundException $e) {
				throw new HttpException(Http::STATUS_NOT_FOUND, null, $e);
			}
			return new DataResponse($shiftTypeExtended);
		} catch (Throwable $th) {
			$responseCode = $th instanceof HttpException ? $th->getStatusCode() : Http::STATUS_INTERNAL_SERVER_ERROR;
			return new DataResponse(['error' => $th->getMessage()], $responseCode);
		}
	}

	/**
	 * @param Repetition $repetition
	 * @param Caldav $caldav
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'POST', url: '/api/shift-types')]
	public function create(
		string $group_id,
		string $name,
		string $description,
		string $color,
		bool $active,
		array $repetition,
		array $caldav,
	): DataResponse {
		try {
			if (!$this->groupShiftAdminRelationService->isShiftAdmin($group_id)) {
				throw new HttpException(
					Http::STATUS_FORBIDDEN,
					"You are not a group shift admin for group_id $group_id",
				);
			}
			$shiftType = $this->shiftTypeMapper->create(
				$group_id,
				$name,
				$description,
				$color,
				$active,
				$repetition,
				$caldav,
			);
			$shiftTypeExtended = $this->shiftTypeService->getExtended($shiftType);
			return new DataResponse($shiftTypeExtended);
		} catch (Throwable $th) {
			$responseCode = $th instanceof HttpException ? $th->getStatusCode() : Http::STATUS_INTERNAL_SERVER_ERROR;
			return new DataResponse(['error' => $th->getMessage()], $responseCode);
		}
	}

	/**
	 * @param Caldav $caldav
	 */
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'PUT', url: '/api/shift-types/{id}')]
	public function update(
		int $id,
		string $name,
		string $description,
		string $color,
		bool $active,
		array $caldav,
	): DataResponse {
		try {
			try {
				$shiftType = $this->shiftTypeService->getRestricted($id);
			} catch (ShiftTypeNotFoundException $e) {
				throw new HttpException(Http::STATUS_NOT_FOUND, null, $e);
			}
			$shiftType = $this->shiftTypeMapper->updateById(
				$shiftType,
				null,
				$name,
				$description,
				$color,
				$active,
				null,
				$caldav,
			);
			$shiftTypeExtended = $this->shiftTypeService->getExtended($shiftType);
			return new DataResponse($shiftTypeExtended);
		} catch (Throwable $th) {
			$responseCode = $th instanceof HttpException ? $th->getStatusCode() : Http::STATUS_INTERNAL_SERVER_ERROR;
			return new DataResponse(['error' => $th->getMessage()], $responseCode);
		}
	}

	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'DELETE', url: '/api/shift-types/{id}')]
	public function destroy(int $id): DataResponse {
		try {
			try {
				$shiftType = $this->shiftTypeService->getRestricted($id);
			} catch (ShiftTypeNotFoundException $e) {
				throw new HttpException(Http::STATUS_NOT_FOUND, null, $e);
			}
			$shiftType = $this->shiftTypeMapper->deleteById($shiftType);
			$shiftTypeExtended = $this->shiftTypeService->getExtended($shiftType);
			return new DataResponse($shiftTypeExtended);
		} catch (Throwable $th) {
			$responseCode = $th instanceof HttpException ? $th->getStatusCode() : Http::STATUS_INTERNAL_SERVER_ERROR;
			return new DataResponse(['error' => $th->getMessage()], $responseCode);
		}
	}
}
