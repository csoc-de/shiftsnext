<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use OC\DB\Exceptions\DbalException;
use OCA\ShiftsNext\Db\GroupShiftAdminRelationMapper;
use OCA\ShiftsNext\Exception\GroupShiftAdminRelationNotFoundException;
use OCA\ShiftsNext\Exception\HttpException;
use OCA\ShiftsNext\Service\GroupShiftAdminRelationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Throwable;

class GroupShiftAdminRelationController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private GroupShiftAdminRelationMapper $groupShiftAdminRelationMapper,
		private GroupShiftAdminRelationService $groupShiftAdminRelationService,
	) {
		parent::__construct($appName, $request);
	}

	#[FrontpageRoute(verb: 'GET', url: '/api/group-shift-admin-relations')]
	public function index(): DataResponse {
		try {
			$relationsExtended = $this->groupShiftAdminRelationService->getAllExtended();
			return new DataResponse($relationsExtended);
		} catch (Throwable $th) {
			return new DataResponse(['error' => $th->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	#[FrontpageRoute(verb: 'GET', url: '/api/group-shift-admin-relations/grouped-by-group')]
	public function indexGroupedByGroup(): DataResponse {
		try {
			$groupedRelations = $this->groupShiftAdminRelationService->getAllGroupedByGroup();
			return new DataResponse($groupedRelations);
		} catch (Throwable $th) {
			$responseCode = $th instanceof HttpException ? $th->getStatusCode() : Http::STATUS_INTERNAL_SERVER_ERROR;
			return new DataResponse(['error' => $th->getMessage()], $responseCode);
		}
	}

	#[FrontpageRoute(verb: 'GET', url: '/api/group-shift-admin-relations/{id}')]
	public function show(int $id): DataResponse {
		try {
			try {
				$relationExtended = $this->groupShiftAdminRelationService->getExtended($id);
			} catch (GroupShiftAdminRelationNotFoundException $e) {
				throw new HttpException(Http::STATUS_NOT_FOUND, null, $e);
			}
			return new DataResponse($relationExtended);
		} catch (Throwable $th) {
			$responseCode = $th instanceof HttpException ? $th->getStatusCode() : Http::STATUS_INTERNAL_SERVER_ERROR;
			return new DataResponse(['error' => $th->getMessage()], $responseCode);
		}
	}

	#[FrontpageRoute(verb: 'POST', url: '/api/group-shift-admin-relations')]
	public function create(
		string $group_id,
		string $user_id,
	): DataResponse {
		try {
			try {
				$relation = $this->groupShiftAdminRelationMapper->create(
					$group_id,
					$user_id,
				);
			} catch (DbalException $e) {
				if ($e->getPrevious() instanceof UniqueConstraintViolationException) {
					throw new HttpException(
						Http::STATUS_UNPROCESSABLE_ENTITY,
						'This relation already exists',
					);
				}
				throw $e;
			}
			$relationExtended = $this->groupShiftAdminRelationService->getExtended($relation);
			return new DataResponse($relationExtended);
		} catch (Throwable $th) {
			$responseCode = $th instanceof HttpException ? $th->getStatusCode() : Http::STATUS_INTERNAL_SERVER_ERROR;
			return new DataResponse(['error' => $th->getMessage()], $responseCode);
		}
	}

	/**
	 * @param string $group_id
	 * @param string[] $user_ids
	 *
	 * @return DataResponse
	 */
	#[FrontpageRoute(verb: 'PUT', url: '/api/group-shift-admin-relations/grouped-by-group')]
	public function updateAllOfGroup(
		string $group_id,
		array $user_ids,
	): DataResponse {
		try {
			$groupedRelation = $this->groupShiftAdminRelationService->updateAllOfGroup($group_id, $user_ids);
			return new DataResponse($groupedRelation);
		} catch (Throwable $th) {
			$responseCode = $th instanceof HttpException ? $th->getStatusCode() : Http::STATUS_INTERNAL_SERVER_ERROR;
			return new DataResponse(['error' => $th->getMessage()], $responseCode);
		}
	}

	#[FrontpageRoute(verb: 'DELETE', url: '/api/group-shift-admin-relations/{id}')]
	public function destroy(int $id): DataResponse {
		try {
			try {
				$relation = $this->groupShiftAdminRelationMapper->deleteById($id);
			} catch (GroupShiftAdminRelationNotFoundException $e) {
				throw new HttpException(Http::STATUS_NOT_FOUND, null, $e);
			}
			$relationExtended = $this->groupShiftAdminRelationService->getExtended($relation);
			return new DataResponse($relationExtended);
		} catch (Throwable $th) {
			$responseCode = $th instanceof HttpException ? $th->getStatusCode() : Http::STATUS_INTERNAL_SERVER_ERROR;
			return new DataResponse(['error' => $th->getMessage()], $responseCode);
		}
	}
}
