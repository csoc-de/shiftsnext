import { defineStore } from 'pinia'
import { computed, ref, watch } from 'vue'
import { putDefaultGroups } from '../db/config.ts'
import { getInitialDefaultGroups } from '../initialState.ts'

export const useUserSettings = defineStore('user-settings', () => {
	const selectedGroups = ref(getInitialDefaultGroups())

	const selectedGroupIds = computed(() => selectedGroups.value.map(({ id }) => id))

	watch(selectedGroupIds, async (groupIds) => {
		await putDefaultGroups({ group_ids: groupIds })
	})

	return { selectedGroups, selectedGroupIds }
})
