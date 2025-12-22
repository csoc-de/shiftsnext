<template>
	<NcSettingsSection :name="t(APP_ID, 'Shift admins')">
		<div class="overflow-auto">
			<table class="w-full table-auto border-collapse border border-solid border-nc-maxcontrast text-left">
				<thead>
					<tr class="bg-nc-primary-element text-nc-primary-element *:px-4 *:py-3">
						<th>
							{{ t(APP_ID, "Group") }}
						</th>
						<th>
							{{ t(APP_ID, "Admins") }}
						</th>
						<th class="text-center">
							{{ t(APP_ID, "Actions") }}
						</th>
					</tr>
				</thead>
				<tbody>
					<template
						v-for="({ group: { id, display_name }, users: relationUsers }, index) in relations"
						:key="id">
						<tr
							v-if="relationUsers.length || group?.display_name === display_name"
							class="border border-solid border-nc-maxcontrast  *:px-4 *:py-3">
							<td class="w-0">
								{{ display_name }}
							</td>
							<td>
								<template v-if="!editedList[index]">
									{{ relationUsers.map(({ display_name }) => display_name).join(', ') }}
								</template>
								<NcSelectUsers
									v-else
									v-model="selectedUserOptions2D[index]"
									class="min-w-56"
									multiple
									keep-open
									:options="userOptions" />
							</td>
							<td class="w-0">
								<div class="flex items-center justify-center">
									<NcButton
										v-if="!editedList[index]"
										@click="toggleRow(index, true)">
										{{ t(APP_ID, "Edit") }}
									</NcButton>
									<NcButton
										v-else
										:disabled="saving"
										@click="
											save(
												index,
												id,
												selectedUserOptions2D[index]!.map(({ id }) => id),
											)
										">
										{{ t(APP_ID, "Save") }}
									</NcButton>
								</div>
							</td>
						</tr>
					</template>
				</tbody>
			</table>
		</div>
		<InputGroup class="mt-3 items-start">
			<label for="selected-group">{{ t(APP_ID, "Add group") }}</label>
			<NcSelect
				v-model="group"
				input-id="selected-group"
				:options="groups"
				label="display_name"
				label-outside
				class="min-w-44" />
		</InputGroup>
	</NcSettingsSection>
</template>

<script setup lang="ts">
import type { Group } from '../models/group.ts'
import type {
	GroupShiftAdminRelationsByGroupPutPayload,
} from '../models/groupShiftAdminRelation.ts'
import type { NcSelectUsersOption } from '../models/nextcloudVue.ts'

import { t } from '@nextcloud/l10n'
import { computed, ref } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import NcSelectUsers from '@nextcloud/vue/components/NcSelectUsers'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'
import InputGroup from './InputGroup.vue'
import { putGroupShiftAdminRelationsGroupedByGroup } from '../db/groupShiftAdminRelation.ts'
import { APP_ID } from '../utils/appId.ts'
import { getInitialGroupShiftAdminRelationsByGroup, getInitialUsers } from '../utils/initialState.ts'
import { getNcSelectUsersOption } from '../utils/nextcloudVue.ts'
import { showSavedToast } from '../utils/toast.ts'

const saving = ref(false)

const relations = ref(getInitialGroupShiftAdminRelationsByGroup())

const users = getInitialUsers()
const userOptions = computed<NcSelectUsersOption[]>(() => users.map(getNcSelectUsersOption))

const selectedUserOptions2D = ref<NcSelectUsersOption[][]>(relations.value.map(({ users }) => users.map(getNcSelectUsersOption)))

const length = relations.value.length
const editedList = ref<boolean[]>(Array(length).fill(false))

/**
 * Groups for the "add group" NcSelect, filtered to groups who currently have no
 * assigned admins
 */
const groups = computed(() => {
	const groups: Group[] = []
	for (const { group, users } of relations.value) {
		if (!users.length) {
			groups.push(group)
		}
	}
	return groups
})

const group = ref<Group>()

/**
 * Toggles the row between edit and save mode
 *
 * @param index The index of the row
 * @param enable Whether to enable edit mode
 */
function toggleRow(index: number, enable: boolean): void {
	editedList.value[index] = enable
}

/**
 * Saves the selected users for the group
 *
 * @param index The index of the row
 * @param groupId The group ID
 * @param userIds The user IDs
 */
async function save(
	index: number,
	groupId: string,
	userIds: string[],
): Promise<void> {
	try {
		saving.value = true
		await update({ group_id: groupId, user_ids: userIds })
	} finally {
		saving.value = false
	}
	group.value = undefined
	toggleRow(index, false)
}

/**
 * Updates the group shift admin relations
 *
 * @param payload The request payload
 */
async function update(payload: GroupShiftAdminRelationsByGroupPutPayload): Promise<void> {
	const updatedRelation
		= await putGroupShiftAdminRelationsGroupedByGroup(payload)

	const index = relations.value.findIndex(({ group }) => group.id === updatedRelation.group.id)
	if (relations.value[index]) {
		relations.value[index].users = updatedRelation.users
	}
	showSavedToast()
}
</script>
