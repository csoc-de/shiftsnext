import type { Group } from '../models/group.ts'

import { loadState } from '@nextcloud/initial-state'
import { defineStore } from 'pinia'
import { computed, ref, watch } from 'vue'
import { APP_ID } from '../appId.ts'
import { putDefaultGroups } from '../db/config.ts'

export const useUserSettings = defineStore('user-settings', () => {
	const selectedGroups = ref(loadState<Group[]>(APP_ID, 'default_groups', []))

	const selectedGroupIds = computed(() => selectedGroups.value.map(({ id }) => id))

	watch(selectedGroups, async (groups) => {
		await putDefaultGroups({ group_ids: groups.map(({ id }) => id) })
	})

	return { selectedGroups, selectedGroupIds }
})
