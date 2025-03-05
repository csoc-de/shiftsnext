import { loadState } from '@nextcloud/initial-state'
import { defineStore } from 'pinia'
import { computed, ref, watch } from 'vue'
import { APP_ID } from '../appId'
import { putDefaultGroups } from '../db/config'
import type { Group } from '../models/group'

export const useSelectedGroups = defineStore('selectedGroups', () => {
	const selectedGroups = ref(loadState<Group[]>(APP_ID, 'default_groups', []))

	const selectedGroupIds = computed(() =>
		selectedGroups.value.map(({ id }) => id),
	)

	watch(selectedGroups, async (groups) => {
		await putDefaultGroups({ group_ids: groups.map(({ id }) => id) })
	})

	return { selectedGroups, selectedGroupIds }
})
