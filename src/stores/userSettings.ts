import { defineStore } from 'pinia'
import { Temporal } from 'temporal-polyfill'
import { computed, ref, watch } from 'vue'
import { putDefaultGroups, putHiddenUsers } from '../db/config.ts'
import { getIsoWeekDate, userTimeZone } from '../utils/date.ts'
import { getInitialDefaultGroups, getInitialHiddenUserIds } from '../utils/initialState.ts'
import { logger } from '../utils/logger.ts'

export const useUserSettingsStore = defineStore('user-settings', () => {
	const selectedGroups = ref(getInitialDefaultGroups())

	const selectedGroupIds = computed(() => selectedGroups.value.map(({ id }) => id))
	const hiddenUserIds = ref(getInitialHiddenUserIds())
	const showWeeklyShifts = ref(true)

	watch(selectedGroupIds, async (groupIds) => {
		try {
			await putDefaultGroups({ group_ids: groupIds })
		} catch (error) {
			logger.warn(error instanceof Error ? error : String(error))
		}
	})

	watch(hiddenUserIds, async (userIds) => {
		try {
			await putHiddenUsers({ user_ids: userIds })
		} catch (error) {
			logger.warn(error instanceof Error ? error : String(error))
		}
	}, { deep: true })

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
		hiddenUserIds,
		showWeeklyShifts,
		now,
		updateNow,
		currentIsoWeekDateWithDay,
		currentIsoWeekDateWithoutDay,
		isoWeekDate,
		resetIsoWeekDate,
	}
})
