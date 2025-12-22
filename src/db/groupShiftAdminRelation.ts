import type { ErrorResponsePayload } from '../models/error.ts'
import type {
	GroupShiftAdminRelationsByGroup,
	GroupShiftAdminRelationsByGroupPutPayload,
} from '../models/groupShiftAdminRelation.ts'

import axios, { type AxiosError } from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { handleError } from '../utils/error.ts'
import { GROUP_SHIFT_ADMIN_RELATIONS_PATH } from '../utils/url.ts'

/**
 * Get the group shift admin relations grouped by group
 */
export async function getGroupShiftAdminRelationsGroupedByGroup(): Promise<
	GroupShiftAdminRelationsByGroup[]
> {
	try {
		return (
			await axios.get<GroupShiftAdminRelationsByGroup[]>(generateUrl(`${GROUP_SHIFT_ADMIN_RELATIONS_PATH}/grouped-by-group`))
		).data
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponsePayload>,
			'fetch',
			'group shift admin relations grouped by group',
		)
		throw error
	}
}

/**
 * Put the group shift admin relations grouped by group
 *
 * @param payload The request payload
 */
export async function putGroupShiftAdminRelationsGroupedByGroup(payload: GroupShiftAdminRelationsByGroupPutPayload): Promise<GroupShiftAdminRelationsByGroup> {
	try {
		return (
			await axios.put<GroupShiftAdminRelationsByGroup>(
				generateUrl(`${GROUP_SHIFT_ADMIN_RELATIONS_PATH}/grouped-by-group`),
				payload,
			)
		).data
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponsePayload>,
			'create',
			'group shift admin relation grouped by group',
		)
		throw error
	}
}
