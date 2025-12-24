import { defineStore } from 'pinia'
import { Temporal } from 'temporal-polyfill'
import { computed, ref, watch } from 'vue'
import { putDefaultGroups } from '../db/config.ts'
import { getIsoWeekDate, userTimeZone } from '../utils/date.ts'
import { getInitialDefaultGroups } from '../utils/initialState.ts'

export const useUserSettingsStore = defineStore('user-settings', () => {
	const selectedGroups = ref(getInitialDefaultGroups())

	const selectedGroupIds = computed(() => selectedGroups.value.map(({ id }) => id))

	watch(selectedGroupIds, async (groupIds) => {
		await putDefaultGroups({ group_ids: groupIds })
	})

	const now = ref(Temporal.Now.zonedDateTimeISO(userTimeZone))
	const currentIsoWeekDateWithDay = computed(() => getIsoWeekDate(now.value, true))
	const currentIsoWeekDateWithoutDay = computed(() => getIsoWeekDate(now.value, false))

	const isoWeekDate = ref(currentIsoWeekDateWithoutDay.value)

	/**
	 * Updates the `now` variable to the current date + time
	 */
	function updateNow() {
		now.value = Temporal.Now.zonedDateTimeISO(userTimeZone)
	}

	/**
	 * Resets the `isoWeekDate` variable back to the current ISO week after
	 * calling {@link updateNow} first
	 */
	function resetIsoWeekDate() {
		updateNow()
		isoWeekDate.value = currentIsoWeekDateWithoutDay.value
	}

	return {
		selectedGroups,
		selectedGroupIds,
		now,
		updateNow,
		currentIsoWeekDateWithDay,
		currentIsoWeekDateWithoutDay,
		isoWeekDate,
		resetIsoWeekDate,
	}
})
