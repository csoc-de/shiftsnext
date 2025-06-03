import axios, { type AxiosError } from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { handleError } from '../error'
import type { ErrorResponse } from '../models/error'
import type {
	GroupShiftAdminRelationsByGroup,
	GroupShiftAdminRelationsByGroupRequest,
} from '../models/groupShiftAdminRelation'
import { GROUP_SHIFT_ADMIN_RELATIONS_PATH } from '../url'

/**
 * Get the group shift admin relations grouped by group
 */
export async function getGroupShiftAdminRelationsGroupedByGroup(): Promise<
  GroupShiftAdminRelationsByGroup[]
  > {
	try {
		return (
			await axios.get<GroupShiftAdminRelationsByGroup[]>(
				generateUrl(
					`${GROUP_SHIFT_ADMIN_RELATIONS_PATH}/grouped-by-group`,
				),
			)
		).data
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponse>,
			'fetch',
			'group shift admin relations grouped by group',
		)
		throw error
	}
}

/**
 * Put the group shift admin relations grouped by group
 * @param payload The group shift admin relations grouped by group
 */
export async function putGroupShiftAdminRelationsGroupedByGroup(
	payload: GroupShiftAdminRelationsByGroupRequest,
): Promise<GroupShiftAdminRelationsByGroup> {
	try {
		return (
			await axios.put<GroupShiftAdminRelationsByGroup>(
				generateUrl(
					`${GROUP_SHIFT_ADMIN_RELATIONS_PATH}/grouped-by-group`,
				),
				payload,
			)
		).data
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponse>,
			'create',
			'group shift admin relation grouped by group',
		)
		throw error
	}
}
